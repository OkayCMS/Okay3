<?php


namespace Okay\Logic;


use Okay\Core\EntityFactory;
use Okay\Entities\CurrenciesEntity;

class MoneyLogic
{
    private $entityFactory;
    private static $currencies;

    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    public function convertVariantsPriceToMainCurrency(array $variants = [])
    {
        if (empty($variants)) {
            return $variants;
        }
        
        foreach ($variants as &$variant) {
            $variant = $this->convertVariantPriceToMainCurrency($variant);
        }

        return $variants;
    }

    public function convertVariantPriceToMainCurrency($variant)
    {
        if (empty($variant)) {
            return $variant;
        }

        $currencies = $this->getCurrenciesList();
        if (!isset($currencies[$variant->currency_id])) {
            return $variant;
        }

        $variantCurrency = $currencies[$variant->currency_id];
        if (!empty($variant->currency_id) && $variantCurrency->rate_from != $variantCurrency->rate_to) {
            $variant->price = round($variant->price * $variantCurrency->rate_to / $variantCurrency->rate_from, 2);
            $variant->compare_price = round($variant->compare_price * $variantCurrency->rate_to / $variantCurrency->rate_from, 2);
        }

        return $variant;
    }
    
    private function getCurrenciesList()
    {
        if (empty(self::$currencies)) {
            /** @var CurrenciesEntity $currenciesEntity */
            $currenciesEntity = $this->entityFactory->get(CurrenciesEntity::class);
            self::$currencies = $currenciesEntity->mappedBy('id')->find();
        }
        
        return self::$currencies;
    }
}