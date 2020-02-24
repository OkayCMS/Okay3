<?php


namespace Okay\Modules\OkayCMS\NovaposhtaCost\Backend\Controllers;


use Okay\Admin\Controllers\IndexAdmin;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\PaymentsEntity;

class NovaposhtaCostAdmin extends IndexAdmin
{
    public function fetch(CurrenciesEntity $currenciesEntity, PaymentsEntity $paymentsEntity)
    {

        if ($this->request->method('POST')) {
            $this->settings->set('newpost_key', $this->request->post('newpost_key'));
            $this->settings->set('newpost_currency_id', $this->request->post('currency_id'));
            $this->settings->set('newpost_city', $this->request->post('newpost_city'));
            $this->settings->set('newpost_weight', str_replace(',', '.', $this->request->post('newpost_weight')));
            $this->settings->set('newpost_volume', str_replace(',', '.', $this->request->post('newpost_volume')));
            $this->settings->set('newpost_use_volume', $this->request->post('newpost_use_volume'));
            $this->settings->set('newpost_use_assessed_value', $this->request->post('newpost_use_assessed_value'));
            $this->design->assign('message_success', 'saved');
        }

        $this->design->assign('all_currencies', $currenciesEntity->find());
        $this->design->assign('newpost_cities', $this->getCities());

        $paymentMethods = $paymentsEntity->find();
        $this->design->assign('payment_methods', $paymentMethods);

        $this->response->setContent($this->design->fetch('novaposhta_cost.tpl'));
    }

    private function getCities()
    {
        $request = [
            "apiKey" => $this->settings->get('newpost_key'),
            "modelName" => "Address",
            "calledMethod" => "getCities",
            "methodProperties" => [
                "Page" => 1
            ],
        ];
        $request = json_encode($request);

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
        $response = json_decode($response);

        $citiesOptions = '';
        foreach ($response->data as $i=>$city) {
            $citiesOptions .= '<option value="'.$city->Ref.'" '.($city->Ref == $this->settings->get('newpost_city') ? 'selected' : '').'>'.$city->DescriptionRu.'</option>';
        }
        return $citiesOptions;
    }
    
}