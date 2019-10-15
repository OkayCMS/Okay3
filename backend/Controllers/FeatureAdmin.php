<?php


namespace Okay\Admin\Controllers;


use Okay\Core\QueryFactory;
use Okay\Core\Translit;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\FeaturesEntity;
use Okay\Entities\FeaturesValuesEntity;

class FeatureAdmin extends IndexAdmin
{

    private $forbiddenNames = [];
    
    public function fetch(
        FeaturesEntity $featuresEntity,
        FeaturesValuesEntity $featuresValuesEntity,
        CategoriesEntity $categoriesEntity,
        Translit $translit,
        QueryFactory $queryFactory
    ) {
        $feature = new \stdClass;
        if ($this->request->method('post')) {
            $feature->id = $this->request->post('id', 'integer');
            $feature->name = $this->request->post('name');
            $feature->in_filter = intval($this->request->post('in_filter'));
            $feature->auto_name_id = $this->request->post('auto_name_id');
            $feature->auto_value_id = $this->request->post('auto_value_id');
            $feature->url = $this->request->post('url', 'string');
            $feature->url_in_product = $this->request->post('url_in_product');
            $feature->to_index_new_value = $this->request->post('to_index_new_value');
            $feature->description = $this->request->post('description');

            $feature->url = preg_replace("/[\s]+/ui", '', $feature->url);
            $feature->url = strtolower(preg_replace("/[^0-9a-z]+/ui", '', $feature->url));
            if (empty($feature->url)) {
                $feature->url = $translit->translitAlpha($feature->name);
            }
            $featureCategories = $this->request->post('feature_categories', null, []);

            // Не допустить одинаковые URL свойств.
            if (($f = $featuresEntity->get($feature->url)) && $f->id!=$feature->id) {
                $this->design->assign('message_error', 'duplicate_url');
            } elseif(empty($feature->name)) {
                $this->design->assign('message_error', 'empty_name');
            } elseif (!$featuresEntity->checkAutoId($feature->id, $feature->auto_name_id)) {
                $this->design->assign('message_error', 'auto_name_id_exists');
            } elseif (!$featuresEntity->checkAutoId($feature->id, $feature->auto_value_id, "auto_value_id")) {
                $this->design->assign('message_error', 'auto_value_id_exists');
            } elseif ($this->isNameForbidden($feature->name)) {
                $this->design->assign('forbidden_names', $this->forbiddenNames);
                $this->design->assign('message_error', 'forbidden_name');
            } else {
                /*Добавление/Обновление свойства*/
                if (empty($feature->id)) {
                    $feature->id = $featuresEntity->add($feature);
                    $feature = $featuresEntity->get($feature->id);
                    $this->design->assign('message_success', 'added');
                } else {
                    $featuresEntity->update($feature->id, $feature);
                    $feature = $featuresEntity->get($feature->id);
                    $this->design->assign('message_success', 'updated');
                }
                
                $featuresEntity->updateFeatureCategories($feature->id, $featureCategories);
            }

            // Если отметили "Индексировать все значения"
            if (isset($_POST['to_index_all_values']) && $feature->id) {
                $toIndexAllValues = $this->request->post('to_index_all_values', 'integer');
                $update = $queryFactory->newUpdate();
                $update->table('__features_values')
                    ->col('to_index', $toIndexAllValues)
                    ->where('feature_id=:feature_id')
                    ->bindValue('feature_id', $feature->id);
                $this->db->query($update);
            }

            $featuresValues = [];
            if ($this->request->post('feature_values')) {
                foreach ($this->request->post('feature_values') as $n=>$fv) {
                    foreach ($fv as $i=>$v) {
                        if (empty($featuresValues[$i])) {
                            $featuresValues[$i] = new \stdClass;
                        }
                        $featuresValues[$i]->$n = $v;
                    }
                }
            }

            if ($valuesToDelete = $this->request->post('values_to_delete')) {
                foreach ($featuresValues  as $k=>$fv) {
                    if (in_array($fv->id, $valuesToDelete)) {
                        unset($featuresValues[$k]);
                        $featuresValuesEntity->delete($fv->id);
                    }
                }
            }

            $featureValuesIds = [];
            foreach($featuresValues as $fv) {
                if (!$fv->to_index) {
                    $fv->to_index = 0;
                }
                // TODO Обработка ошибок не уникального тринслита или генерить уникальный
                if ($fv->value) {
                    $fv->feature_id = $feature->id;
                    if (!empty($fv->id)) {
                        $featuresValuesEntity->update($fv->id, $fv);
                    } else {
                        unset($fv->id);
                        $fv->id = $featuresValuesEntity->add($fv);
                    }
                    $featureValuesIds[] = $fv->id;
                }
            }

            asort($featureValuesIds);
            $i = 0;
            foreach($featureValuesIds as $featureValueId) {
                $featuresValuesEntity->update($featureValuesIds[$i], ['position'=>$featureValueId]);
                $i++;
            }

            // Если прислали значения для объединения
            if (($unionMainValueId = $this->request->post('union_main_value_id', 'integer'))
                && ($unionSecondValueId = $this->request->post('union_second_value_id', 'integer'))) {

                $unionMainValue   = $featuresValuesEntity->get((int)$unionMainValueId);
                $unionSecondValue = $featuresValuesEntity->get((int)$unionSecondValueId);

                if ($unionMainValue && $unionSecondValue && $unionMainValue->id != $unionSecondValue->id) {
                    // Получим id товаров для которых уже есть занчение, которое мы объединяем
                    $select = $queryFactory->newSelect();
                    $select->from('__products_features_values')
                        ->cols(['product_id'])
                        ->where('value_id=:value_id')
                        ->bindValue('value_id', $unionMainValue->id);
                    $this->db->query($select);
                    $productsIds = $this->db->results('product_id');

                    // Добавляем значение с которым объединяли всем товарам у которых было старое значение
                    foreach ($productsIds as $productId) {
                        $sql = $queryFactory->newSqlQuery();
                        $sql->setStatement("REPLACE INTO `__products_features_values` SET `product_id`=:product_id, `value_id`=:value_id")
                            ->bindValue('product_id', $productId)
                            ->bindValue('value_id', $unionSecondValue->id);
                        $this->db->query($sql);
                    }

                    // Удаляем занчение которое мы объединяли
                    $featuresValuesEntity->delete($unionMainValue->id);
                }
            }

        } else {
            $feature->id = $this->request->get('id', 'integer');
            $feature = $featuresEntity->get($feature->id);
        }

        if (!empty($feature->id)) {

            $featuresValues = [];
            $featuresValuesFilter = ['feature_id'=>$feature->id];

            if ($featuresValuesFilter['limit'] = $this->request->get('limit', 'integer')) {
                $featuresValuesFilter['limit'] = max(5, $featuresValuesFilter['limit']);
                $featuresValuesFilter['limit'] = min(100, $featuresValuesFilter['limit']);
                $_SESSION['features_values_num_admin'] = $featuresValuesFilter['limit'];
            } elseif (!empty($_SESSION['features_values_num_admin'])) {
                $featuresValuesFilter['limit'] = $_SESSION['features_values_num_admin'];
            } else {
                $featuresValuesFilter['limit'] = 25;
            }
            $this->design->assign('current_limit', $featuresValuesFilter['limit']);

            $featuresValuesFilter['page'] = max(1, $this->request->get('page', 'integer'));

            $feature_values_count = $featuresValuesEntity->count($featuresValuesFilter);

            // Показать все страницы сразу
            if($this->request->get('page') == 'all') {
                $featuresValuesFilter['limit'] = $feature_values_count;
            }

            if($featuresValuesFilter['limit'] > 0) {
                $pages_count = ceil($feature_values_count/$featuresValuesFilter['limit']);
            } else {
                $pages_count = 0;
            }

            if ($this->request->post('action') == 'move_to_page' && $this->request->post('check')) {
                /*Переместить на страницу*/
                $target_page = $this->request->post('target_page', 'integer');

                // Сразу потом откроем эту страницу
                $featuresValuesFilter['page'] = $target_page;

                $check = $this->request->post('check');
                $select = $queryFactory->newSelect();
                $select->from('__features_values')
                    ->cols(['id'])
                    ->where('feature_id = :feature_id')
                    ->where('id NOT IN (:id)')
                    ->bindValues([
                        'feature_id' => $feature->id,
                        'id' => (array)$check,
                    ])
                    ->orderBy(['position ASC']);
                //$query = $this->db->placehold("SELECT id FROM __features_values WHERE feature_id=? AND id not in (?@) ORDER BY position ASC", $feature->id, (array)$check);
                $this->db->query($select);

                $ids = $this->db->results('id');

                // вычисляем после какого значения вставить то, которое меремещали
                $offset = $featuresValuesFilter['limit'] * ($target_page)-1;
                $featureValuesIds = array();
                
                // Собираем общий массив id значений, и в нужное место добавим значение которое перемещали
                // По сути иммитация если выбрали page=all и мереместили приблизительно в нужное место значение
                foreach ($ids as $k=>$id) {
                    if ($k == $offset) {
                        $featureValuesIds = array_merge($featureValuesIds, $check);
                        unset($check);
                    }
                    $featureValuesIds[] = $id;
                }
                
                if (!empty($check)) {
                    $featureValuesIds = array_merge($featureValuesIds, $check);
                }

                asort($featureValuesIds);
                $i = 0;
                
                foreach ($featureValuesIds as $featuresValueId) {
                    $featuresValuesEntity->update($featureValuesIds[$i], ['position'=>$featuresValueId]);
                    $i++;
                }
            }

            $featuresValuesFilter['page'] = min($featuresValuesFilter['page'], $pages_count);
            $this->design->assign('feature_values_count', $feature_values_count);
            $this->design->assign('pages_count', $pages_count);
            $this->design->assign('current_page', $featuresValuesFilter['page']);

            $featureValuesIds = [];
            foreach ($featuresValuesEntity->find($featuresValuesFilter) as $fv) {
                $featuresValues[$fv->translit] = $fv;
                $featureValuesIds[] = $fv->id;
            }

            $productsCounts = $featuresValuesEntity->countProductsByValueId($featureValuesIds);
            $this->design->assign('products_counts', $productsCounts);
            $this->design->assign('features_values', $featuresValues);
        }

        $featureCategories = [];
        if ($feature) {
            $featureCategories = $featuresEntity->getFeatureCategories($feature->id);
        } elseif ($category_id = $this->request->get('category_id')) {
            $featureCategories[] = $category_id;
        }

        $categories = $categoriesEntity->getCategoriesTree();
        $this->design->assign('categories', $categories);
        $this->design->assign('feature', $feature);
        $this->design->assign('feature_categories', $featureCategories);
        
        $this->response->setContent($this->design->fetch('feature.tpl'));
    }

    private function isNameForbidden($name) // todo доделать после импорта
    {
        $result = false;
        /*foreach($this->import->columns_names as $i=>$names) {
            $this->forbiddenNames = array_merge($this->forbiddenNames, $names);
            foreach($names as $n) {
                if(preg_match("~^".preg_quote($name)."$~ui", $n)) {
                    $result = true;
                }
            }
        }*/
        return $result;
    }
    
}
