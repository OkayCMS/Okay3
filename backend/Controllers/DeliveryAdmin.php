<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendDeliveriesHelper;
use Okay\Admin\Helpers\BackendValidateHelper;
use Okay\Admin\Requests\BackendDeliveriesRequest;
use Okay\Core\Modules\Modules;
use Okay\Entities\PaymentsEntity;

class DeliveryAdmin extends IndexAdmin
{
    
    public function fetch(
        PaymentsEntity $paymentsEntity,
        BackendDeliveriesHelper $backendDeliveriesHelper,
        BackendDeliveriesRequest $backendDeliveriesRequest,
        BackendValidateHelper $backendValidateHelper,
        Modules $modules
    ) {
        /*Принимаем данные о способе доставки*/
        if ($this->request->method('post')) {

            $delivery = $backendDeliveriesRequest->postDelivery();

            $deliverySettings = $backendDeliveriesRequest->postSettings();
            $deliveryPayments = $backendDeliveriesRequest->postDeliveryPayments();

            if ($error = $backendValidateHelper->getDeliveriesValidateError($delivery)) {
                $this->design->assign('message_error', $error);
            } else {
                /*Добавление/Обновление способа доставки*/
                if (empty($delivery->id)) {
                    $preparedDelivery = $backendDeliveriesHelper->prepareAdd($delivery);
                    $delivery->id     = $backendDeliveriesHelper->add($preparedDelivery);
                    $this->design->assign('message_success', 'added');
                } else {
                    $preparedDelivery = $backendDeliveriesHelper->prepareUpdate($delivery);
                    $backendDeliveriesHelper->update($preparedDelivery->id, $delivery);
                    $this->design->assign('message_success', 'updated');
                }

                if ($backendDeliveriesRequest->postDeleteImage()) {
                    $backendDeliveriesHelper->deleteImage($delivery);
                }

                if ($image = $backendDeliveriesRequest->fileImage()) {
                    $backendDeliveriesHelper->uploadImage($image, $delivery);
                }

                $backendDeliveriesHelper->updateSettings($delivery->id, $deliverySettings);
                $backendDeliveriesHelper->updateDeliveryPayments($delivery->id, $deliveryPayments);
            }
        }

        if (!empty($delivery)) {
            $deliveryId = $delivery->id;
        } else {
            $deliveryId = $this->request->get('id', 'integer');
        }

        $delivery = $backendDeliveriesHelper->getDelivery($deliveryId);
        
        // Все способы оплаты
        $paymentMethods = $paymentsEntity->find();
        $this->design->assign('payment_methods', $paymentMethods);
        $this->design->assign('delivery', $delivery);

        $deliveryModules = $modules->getDeliveryModules($this->manager->lang);
        $this->design->assign('delivery_modules', $deliveryModules);

        $this->response->setContent($this->design->fetch('delivery.tpl'));
    }
    
}
