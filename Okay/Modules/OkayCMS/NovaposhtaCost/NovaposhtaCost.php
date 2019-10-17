<?php


namespace Okay\Modules\OkayCMS\NovaposhtaCost;


use Okay\Core\EntityFactory;
use Okay\Core\Money;
use Okay\Core\Settings;
use Okay\Entities\CurrenciesEntity;

class NovaposhtaCost
{
    
    private $npApiKey;
    
    /** @var Settings */
    private $settings;
    
    /** @var EntityFactory */
    private $entityFactory;
    
    /** @var Money */
    private $money;
    
    public function __construct(Settings $settings, EntityFactory $entityFactory, Money $money)
    {
        $this->entityFactory = $entityFactory;
        $this->npApiKey = $settings->get('newpost_key');
        $this->settings = $settings;
        $this->money = $money;
    }

    /**
     * Выборка отделений Новой Почты
     * @param string $cityRef id города Новой Почты
     * @param string $warehouseRef id отделения Новой Почты
     * @return bool|mixed
     */
    public function getWarehouses($cityRef, $warehouseRef = '')
    {
        if (empty($cityRef)) {
            return false;
        }

        $request = array(
            "apiKey" => $this->settings->newpost_key,
            "modelName" => "Address",
            "calledMethod" => "getWarehouses",
            "methodProperties" => array(
                "CityRef" => $cityRef,
                "Page" => 1
            )
        );

        $response = $this->npRequest(json_encode($request));

        if ($response->success){
            $result['success'] = $response->success;
            $result['warehouses_data'] = $response->data;
            $result['warehouses'] = '<option selected disabled value="">Выберите отделение доставки</option>';
            foreach ($response->data as $i=>$warehouse) {
                $result['warehouses'] .= '<option value="'.htmlspecialchars($warehouse->DescriptionRu).'" data-warehouse_ref="'.$warehouse->Ref.'"'.(!empty($warehouseRef) && $warehouseRef == $warehouse->Ref ? 'selected' : '').'>'.htmlspecialchars($warehouse->DescriptionRu).'</option>';
            }
            return $result;
        } else {
            return false;
        }
    }


    /**
     * Выборка городов Новой Почты
     * @param string $selectedCity id города Новой Почты
     * @return bool|mixed
     */
    public function getCities($selectedCity = '')
    {
        $request = [
            "apiKey" => $this->npApiKey,
            "modelName" => "Address",
            "calledMethod" => "getCities",
            "methodProperties" => [
                "Page" => 1,
            ],
        ];

        $response = $this->npRequest(json_encode($request));
        if ($response->success){
            $result['success'] = $response->success;
            $result['cities_data'] = $response->data;
            $result['cities'] = '<option value=""></option>';
            foreach ($response->data as $i=>$city) {
                $result['cities'] .= '<option value="'.htmlspecialchars($city->DescriptionRu).'" data-city_ref="'.$city->Ref.'" '.(!empty($selectedCity) && $selectedCity == $city->Ref ? 'selected' : '').'>'.htmlspecialchars($city->DescriptionRu).'</option>';
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Калькулятор стоимости доставки Новой Почты
     * @param string $cityRef id города Новой Почты
     * @param bool $redelivery наложенный платеж
     * @param object $data - данные о заказе
     * @param string $serviceType - тип доставки (до двери, до склада...)
     * @return bool|mixed
     * @throws \Exception
     */
    public function calcPrice($cityRef, $redelivery, $data, $serviceType)
    {
        if (empty($cityRef) && empty($data)) {
            return false;
        }

        /** @var CurrenciesEntity $currenciesEntity */
        $currenciesEntity = $this->entityFactory->get(CurrenciesEntity::class);

        if ($this->settings->get('newpost_currency_id')) {
            $npCurrency = $currenciesEntity->get((int)$this->settings->get('newpost_currency_id'));
        } else {
            $npCurrency = $currenciesEntity->getMainCurrency();
        }

        $totalWeight = 0;
        $totalVolume = 0;
        foreach ($data->purchases as $purchase) {
            $totalWeight += (!empty($purchase->variant->weight) && $purchase->variant->weight>0 ? $purchase->variant->weight : $this->settings->get('newpost_weight'))*$purchase->amount;

            if ($this->settings->get('newpost_use_volume')){
                $totalVolume += (!empty($purchase->variant->volume) && $purchase->variant->volume>0 ? $purchase->variant->volume : $this->settings->get('newpost_volume'))*$purchase->amount;
            }
        }

        $methodProperties = [
            "CitySender" => $this->settings->get('newpost_city'),
            "CityRecipient" => $cityRef,
            "Weight" => $totalWeight,
            "ServiceType" => $serviceType,
        ];

        if ($this->settings->get('newpost_use_volume')){
            $methodProperties = array_merge($methodProperties, array("VolumeGeneral" => $totalVolume));
        }

        /* Если в настройках выбрано "оценочная стоимость" */
        if ($this->settings->get('newpost_use_assessed_value')){

            $cost = $this->money->convert($data->total_price, $npCurrency->id, false);
            $methodProperties = array_merge($methodProperties, array("Cost" => max(1, round($cost))));
        }

        /* Если выбрали наложенный платеж */
        if ($redelivery){
            $redeliveryAmount = $this->money->convert($data->total_price, $npCurrency->id, false);

            $methodProperties = array_merge($methodProperties, array("RedeliveryCalculate" => array(
                'CargoType'=>'Money',
                'Amount'=>round($redeliveryAmount),
            )));
        }

        $request = array(
            "apiKey" => $this->settings->get('newpost_key'),
            "modelName" => "InternetDocument",
            "calledMethod" => "getDocumentPrice",
            "methodProperties" => $methodProperties
        );
        
        return $this->npRequest(json_encode($request));
    }

    /**
     * Калькулятор срока доставки
     * @param string $cityRef id города Новой Почты
     * @param string $serviceType - тип доставки (до двери, до склада...)
     * @return bool|mixed
     */
    public function calcTerm($cityRef, $serviceType)
    {

        if (empty($cityRef)) {
            return false;
        }

        $request = array(
            "apiKey" => $this->settings->get('newpost_key'),
            "modelName" => "InternetDocument",
            "calledMethod" => "getDocumentDeliveryDate",
            "methodProperties" => array(
                "CitySender" => $this->settings->get('newpost_city'),
                "CityRecipient" => $cityRef,
                "ServiceType" => $serviceType,
            )
        );

        return $this->npRequest(json_encode($request));

    }

    /**
     * @param string $request json параметры запроса
     * @return bool|mixed
     */
    private function npRequest($request)
    {
        if (empty($request)) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.novaposhta.ua/v2.0/json/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: text/xml"]);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }
    
}