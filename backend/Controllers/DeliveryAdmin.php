<?php


namespace Okay\Admin\Controllers;


use Okay\Core\Image;
use Okay\Entities\DeliveriesEntity;
use Okay\Entities\PaymentsEntity;

class DeliveryAdmin extends IndexAdmin
{
    
    public function fetch(
        DeliveriesEntity $deliveriesEntity,
        PaymentsEntity $paymentsEntity,
        Image $imageCore
    ) {
        $delivery = new \stdClass;
        /*Принимаем данные о способе доставки*/
        if ($this->request->method('post')) {
            $delivery->id               = $this->request->post('id', 'intgeger');
            $delivery->enabled          = $this->request->post('enabled', 'boolean');
            $delivery->name             = $this->request->post('name');
            $delivery->description      = $this->request->post('description');
            $delivery->price            = $this->request->post('price');
            $delivery->free_from        = $this->request->post('free_from');
            $delivery->separate_payment = $this->request->post('separate_payment','boolean');

            if (!$deliveryPayments = $this->request->post('delivery_payments')) {
                $deliveryPayments = [];
            }
            
            if (empty($delivery->name)) {
                $this->design->assign('message_error', 'empty_name');
            } else {
                /*Добавление/Обновление способа доставки*/
                if (empty($delivery->id)) {
                    $delivery->id = $deliveriesEntity->add($delivery);
                    $this->design->assign('message_success', 'added');
                } else {
                    $deliveriesEntity->update($delivery->id, $delivery);
                    $this->design->assign('message_success', 'updated');
                }

                // Удаление изображения
                if ($this->request->post('delete_image')) {
                    $imageCore->deleteImage(
                        $delivery->id,
                        'image',
                        DeliveriesEntity::class,
                        $this->config->original_deliveries_dir,
                        $this->config->resized_deliveries_dir
                    );
                }
                // Загрузка изображения
                $image = $this->request->files('image');
                if (!empty($image['name']) && ($filename = $imageCore->uploadImage($image['tmp_name'], $image['name'], $this->config->original_deliveries_dir))) {
                    $imageCore->deleteImage(
                        $delivery->id,
                        'image',
                        DeliveriesEntity::class,
                        $this->config->original_deliveries_dir,
                        $this->config->resized_deliveries_dir
                    );
                    $deliveriesEntity->update($delivery->id, ['image'=>$filename]);
                }
                $deliveriesEntity->updateDeliveryPayments($delivery->id, $deliveryPayments);
                $delivery = $deliveriesEntity->get($delivery->id);
            }
            
        } else {
            $delivery->id = $this->request->get('id', 'integer');
            if (!empty($delivery->id)) {
                $delivery = $deliveriesEntity->get($delivery->id);
            }
            $deliveryPayments = $deliveriesEntity->getDeliveryPayments($delivery->id);
        }
        $this->design->assign('delivery_payments', $deliveryPayments);
    
        // Все способы оплаты
        $paymentMethods = $paymentsEntity->find();
        $this->design->assign('payment_methods', $paymentMethods);
    
        $this->design->assign('delivery', $delivery);

        $this->response->setContent($this->design->fetch('delivery.tpl'));
    }
    
}
