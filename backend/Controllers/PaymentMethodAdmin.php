<?php


namespace Okay\Admin\Controllers;


use Okay\Core\Image;
use Okay\Core\Modules\Modules;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\DeliveriesEntity;
use Okay\Entities\PaymentsEntity;

class PaymentMethodAdmin extends IndexAdmin
{
    
    public function fetch(
        PaymentsEntity $paymentsEntity,
        DeliveriesEntity $deliveriesEntity,
        CurrenciesEntity $currenciesEntity,
        Image $imageCore,
        Modules $modules
    ) {
        $paymentMethod = new \stdClass;
        /*Приме информации о способе оплаты*/
        if ($this->request->method('post')) {
            $paymentMethod->id              = $this->request->post('id', 'intgeger');
            $paymentMethod->enabled         = $this->request->post('enabled', 'boolean');
            $paymentMethod->name            = $this->request->post('name');
            $paymentMethod->currency_id     = $this->request->post('currency_id');
            $paymentMethod->description     = $this->request->post('description');
            $paymentMethod->module          = $this->request->post('module');
            
            $paymentSettings = $this->request->post('payment_settings', null, []);
            
            if (!$paymentDeliveries = $this->request->post('payment_deliveries')) {
                $paymentDeliveries = [];
            }
            
            if (empty($paymentMethod->name)) {
                $this->design->assign('message_error', 'empty_name');
            } else {
                /*Добавление/Обновление способа оплаты*/
                if (empty($paymentMethod->id)) {
                    if (empty($paymentMethod->settings)) {
                        $paymentMethod->settings = '';
                    }

                    $paymentMethod->id = $paymentsEntity->add($paymentMethod);
                    $this->design->assign('message_success', 'added');
                } else {
                    $paymentsEntity->update($paymentMethod->id, $paymentMethod);
                    $this->design->assign('message_success', 'updated');
                }
                
                if ($this->request->post('delete_image')) {
                    $imageCore->deleteImage(
                        $paymentMethod->id,
                        'image',
                        PaymentsEntity::class,
                        $this->config->original_payments_dir,
                        $this->config->resized_payments_dir
                    );
                }
                // Загрузка изображения
                $image = $this->request->files('image');
                if (!empty($image['name']) && ($filename = $imageCore->uploadImage($image['tmp_name'], $image['name'], $this->config->original_payments_dir))) {
                    $imageCore->deleteImage(
                        $paymentMethod->id,
                        'image',
                        PaymentsEntity::class,
                        $this->config->original_payments_dir,
                        $this->config->resized_payments_dir
                    );
                    $paymentsEntity->update($paymentMethod->id, ['image'=>$filename]);
                }
                
                if ($paymentMethod->id) {
                    $paymentsEntity->updatePaymentSettings($paymentMethod->id, $paymentSettings);
                    $paymentsEntity->updatePaymentDeliveries($paymentMethod->id, $paymentDeliveries);
                }
                $paymentMethod = $paymentsEntity->get($paymentMethod->id);
            }
        } else {
            $paymentMethod->id = $this->request->get('id', 'integer');
            if (!empty($paymentMethod->id)) {
                $paymentMethod = $paymentsEntity->get($paymentMethod->id);
                $paymentSettings =  $paymentsEntity->getPaymentSettings($paymentMethod->id);
            } else {
                $paymentSettings = [];
            }
            $paymentDeliveries = $paymentsEntity->getPaymentDeliveries($paymentMethod->id);
        }
        $this->design->assign('payment_deliveries', $paymentDeliveries);
        
        // Связанные способы доставки
        $deliveries = $deliveriesEntity->find();
        $this->design->assign('deliveries', $deliveries);
        
        $this->design->assign('payment_method', $paymentMethod);
        $this->design->assign('payment_settings', $paymentSettings);
        
        $paymentModules = $modules->getPaymentModules();
        $this->design->assign('payment_modules', $paymentModules);
        
        $currencies = $currenciesEntity->find();
        $this->design->assign('currencies', $currencies);
        
        $this->response->setContent($this->design->fetch('payment_method.tpl'));
    }
    
}
