<?php


namespace Okay\Core;


use Okay\Entities\CurrenciesEntity;

class Money
{

    /**
     * @var EntityFactory 
     */
    private $entityFactory;
    
    private $decimalsPoint;
    private $thousandsSeparator;
    
    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    public function getCoefMoney()
    {
        /** @var CurrenciesEntity $currenciesEntity */
        $currenciesEntity = $this->entityFactory->get(CurrenciesEntity::class);
        $mainCurrency = $currenciesEntity->getMainCurrency();
        
        $coef = 1;
        if (isset($_SESSION['currency_id']) && $mainCurrency->id != $_SESSION['currency_id']) {
            $currency = $this->entityFactory->get(CurrenciesEntity::class)->get(intval($_SESSION['currency_id']));

            if (empty($currency)) {
                $_SESSION['currency_id'] = $mainCurrency->id;
                return $coef;
            }

            $coef = $currency->rate_from / $currency->rate_to;
        }

        return $coef;
    }
    
    public function convert($price, $currencyId = null, $format = true, $revers = false)
    {
        /** @var CurrenciesEntity $currenciesEntity */
        $currenciesEntity = $this->entityFactory->get(CurrenciesEntity::class);
        $precision = 0;
        
        if (isset($currencyId)) {
            if (is_numeric($currencyId)) {
                $currency = $currenciesEntity->get((int)$currencyId);
            } else {
                $currency = $currenciesEntity->get((string)$currencyId);
            }
        } elseif (isset($_SESSION['currency_id'])) { // todo работа со storage
            $currency = $currenciesEntity->get((int)$_SESSION['currency_id']);
        } else {
            $currency = current($currenciesEntity->find(['enabled' => 1]));
        }
        
        $result = $price;
        if (!empty($currency)) {
            // Умножим на курс валюты
            if ($revers === true) {
                $result = $result*$currency->rate_to/$currency->rate_from;
            } else {
                $result = $result*$currency->rate_from/$currency->rate_to;
            }
            
            // Точность отображения, знаков после запятой
            $precision = isset($currency->cents)?$currency->cents:2;
        }
        
        // Форматирование цены
        if ($format) {
            $result = number_format($result, $precision, $this->decimalsPoint, $this->thousandsSeparator);
        } else {
            $result = round($result, $precision);
        }
        return $result;
    }
    
    public function configure($decimalsPoint, $thousandsSeparator)
    {
        $this->decimalsPoint = $decimalsPoint;
        $this->thousandsSeparator = $thousandsSeparator;
    }
    
}
