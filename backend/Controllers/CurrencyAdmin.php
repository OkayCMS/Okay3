<?php


namespace Okay\Admin\Controllers;


use Okay\Core\QueryFactory;
use Okay\Entities\OrdersEntity;
use Okay\Entities\CouponsEntity;
use Okay\Entities\VariantsEntity;
use Okay\Entities\PurchasesEntity;
use Okay\Entities\DeliveriesEntity;
use Okay\Entities\CurrenciesEntity;

class CurrencyAdmin extends IndexAdmin
{
    
    public function fetch(CurrenciesEntity $currenciesEntity, QueryFactory $queryFactory)
    {
        // Обработка действий
        if ($this->request->method('post')) {
            $currencies = [];
            /*Формирование данных с валютами*/
            foreach ($this->request->post('currency') as $n=>$va) {
                foreach ($va as $i=>$v) {
                    if(empty($currencies[$i])) {
                        $currencies[$i] = new \stdClass;
                    }
                    $currencies[$i]->$n = $v;
                }
            }
            $wrongIso = [];
            $currenciesIds = [];

            /*Добавление/Удаление валюты*/
            foreach ($currencies as $currency) {
                if (!preg_match('(^[a-zA-Z]{1,3}$)',$currency->code)) {
                    $wrongIso[] = $currency->name;
                }
                if ($currency->id) {
                    $currenciesEntity->update($currency->id, $currency);
                } else {
                    unset($currency->id);
                    $currency->id = $currenciesEntity->add($currency);
                }
                $currenciesIds[] = $currency->id;
            }
            
            if (count($wrongIso) > 0) {
                $this->design->assign('message_error', 'wrong_iso');
                $this->design->assign('wrong_iso', $wrongIso);
            }
            
            // Удалить непереданные валюты
            $currenciesIdsToDelete = $currenciesEntity->find(['not_in_ids' => $currenciesIds]);
            $currenciesEntity->delete($currenciesIdsToDelete);

            // Пересчитать курсы
            $oldCurrency = $currenciesEntity->getMainCurrency();
            $newCurrency = reset($currencies);
            if (!empty($oldCurrency) && $oldCurrency->id != $newCurrency->id) {
                $coef = $newCurrency->rate_from/$newCurrency->rate_to;
                /*Пересчет цен по курсу валюты*/
                if ($this->request->post('recalculate') == 1) {
                    $sql = $queryFactory->newSqlQuery();
                    $sql->setStatement("UPDATE ".VariantsEntity::getTable()." SET price=price*{$coef}, compare_price=compare_price*{$coef} where currency_id=0");
                    $this->db->query($sql);

                    $sql = $queryFactory->newSqlQuery();
                    $sql->setStatement("UPDATE ".DeliveriesEntity::getTable()." SET price=price*{$coef}, free_from=free_from*{$coef}");
                    $this->db->query($sql);

                    $sql = $queryFactory->newSqlQuery();
                    $sql->setStatement("UPDATE ".OrdersEntity::getTable()." SET delivery_price=delivery_price*{$coef}");
                    $this->db->query($sql);

                    $sql = $queryFactory->newSqlQuery();
                    $sql->setStatement("UPDATE ".OrdersEntity::getTable()." SET total_price=total_price*{$coef}");
                    $this->db->query($sql);

                    $sql = $queryFactory->newSqlQuery();
                    $sql->setStatement("UPDATE ".PurchasesEntity::getTable()." SET price=price*{$coef}");
                    $this->db->query($sql);

                    $sql = $queryFactory->newSqlQuery();
                    $sql->setStatement("UPDATE ".CouponsEntity::getTable()." SET value=value*{$coef} WHERE type='absolute'");
                    $this->db->query($sql);

                    $sql = $queryFactory->newSqlQuery();
                    $sql->setStatement("UPDATE ".CouponsEntity::getTable()." SET min_order_price=min_order_price*{$coef}");
                    $this->db->query($sql);

                    $sql = $queryFactory->newSqlQuery();
                    $sql->setStatement("UPDATE ".OrdersEntity::getTable()." SET coupon_discount=coupon_discount*{$coef}");
                    $this->db->query($sql);
                }

                $sql = $queryFactory->newSqlQuery();
                $sql->setStatement("UPDATE ".CurrenciesEntity::getTable()." SET rate_from=1.0*rate_from*$newCurrency->rate_to/$oldCurrency->rate_to");
                $this->db->query($sql);

                $sql = $queryFactory->newSqlQuery();
                $sql->setStatement("UPDATE ".CurrenciesEntity::getTable()." SET rate_to=1.0*rate_to*$newCurrency->rate_from/$oldCurrency->rate_from");
                $this->db->query($sql);

                $sql = $queryFactory->newSqlQuery();
                $sql->setStatement("UPDATE ".CurrenciesEntity::getTable()." SET rate_to = rate_from WHERE id={$newCurrency->id}");
                $this->db->query($sql);

                $sql = $queryFactory->newSqlQuery();
                $sql->setStatement("UPDATE ".CurrenciesEntity::getTable()." SET rate_to = 1, rate_from = 1 WHERE (rate_to=0 OR rate_from=0) AND id={$newCurrency->id}");
                $this->db->query($sql);
            }
            
            // Отсортировать валюты
            asort($currenciesIds);
            $i = 0;
            foreach ($currenciesIds as $currencyId) {
                $currenciesEntity->update($currenciesIds[$i], ['position'=>$currencyId]);
                $i++;
            }
            
            // Действия с выбранными
            $action = $this->request->post('action');
            $id = $this->request->post('action_id');
            
            if (!empty($action) && !empty($id)) {
                switch ($action) {
                    case 'disable': {
                        /*Выключить валюту*/
                        $currenciesEntity->update($id, ['enabled'=>0]);
                        break;
                    }
                    case 'enable': {
                        /*Включить валюту*/
                        $currenciesEntity->update($id, ['enabled'=>1]);
                        break;
                    }
                    case 'show_cents': {
                        /*Показывать копейки*/
                        $currenciesEntity->update($id, ['cents'=>2]);
                        break;
                    }
                    case 'hide_cents': {
                        /*Не показывать копейки*/
                        $currenciesEntity->update($id, ['cents'=>0]);
                        break;
                    }
                    case 'delete': {
                        /*Удалить валюту*/
                        $currenciesEntity->delete($id);
                        break;
                    }
                }
            }
        }
        
        // Отображение
        $currencies = $currenciesEntity->find();
        $currency = $currenciesEntity->getMainCurrency();
        $this->design->assign('currency', $currency);
        $this->design->assign('currencies', $currencies);
        
        $this->response->setContent($this->design->fetch('currency.tpl'));
    }
    
}
