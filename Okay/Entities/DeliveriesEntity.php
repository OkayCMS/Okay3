<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;
use Okay\Core\Image;

class DeliveriesEntity extends Entity
{

    protected static $fields = [
        'id',
        'free_from',
        'price',
        'enabled',
        'position',
        'separate_payment',
        'image',
    ];

    protected static $langFields = [
        'name',
        'description'
    ];

    protected static $defaultOrderFields = [
        'position'
    ];

    protected static $table = '__deliveries';
    protected static $langObject = 'delivery';
    protected static $langTable = 'deliveries';
    protected static $tableAlias = 'd';

    public function add($delivery)
    {
        if (empty($delivery->price)) {
            $delivery->price = 0.00;
        }

        if (empty($delivery->free_from)) {
            $delivery->free_from = 0.00;
        }

        return parent::add($delivery);
    }

    public function delete($ids)
    {
        /** @var Image $imageCore */
        $imageCore = $this->serviceLocator->getService(Image::class);
        
        $ids = (array)$ids;
        
        // Удаляем связь доставки с методоми оплаты
        $delete = $this->queryFactory->newDelete();
        $delete->from('__delivery_payment')
            ->where('delivery_id IN (:delivery_id)')
            ->bindValue('delivery_id', $ids);
        
        $this->db->query($delete);

        foreach ($ids as $id) {
            $imageCore->deleteImage(
                $id,
                'image',
                self::class,
                $this->config->original_deliveries_dir,
                $this->config->resized_deliveries_dir
            );
        }
        
        return parent::delete($ids);
    }

    /*Выборка доступных способов оплаты для данного способа доставки*/
    public function getDeliveryPayments($deliveryId)
    {
        $select = $this->queryFactory->newSelect();
        $select->from('__delivery_payment')
            ->cols(['payment_method_id'])
            ->where('delivery_id = :delivery_id')
            ->bindValue('delivery_id', $deliveryId);
        
        $this->db->query($select);
        return $this->db->results('payment_method_id');
    }

    /*Обновление способов оплаты у данного способа доставки*/
    public function updateDeliveryPayments($deliveryId, array $paymentMethodsIds)
    {
        $delete = $this->queryFactory->newDelete();
        $delete->from('__delivery_payment')
            ->where('delivery_id = :delivery_id')
            ->bindValue('delivery_id', $deliveryId);
        
        $this->db->query($delete);
        
        if (is_array($paymentMethodsIds)) {
            foreach($paymentMethodsIds as $pId) {
                $insert = $this->queryFactory->newInsert();
                $insert->into('__delivery_payment')
                    ->cols([
                        'delivery_id' => $deliveryId,
                        'payment_method_id' => $pId,
                    ]);
                $this->db->query($insert);
            }
        }
    }
    
}
