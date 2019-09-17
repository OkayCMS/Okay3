<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;

class OrdersEntity extends Entity
{
    protected static $fields = [
        'id',
        'delivery_id',
        'delivery_price',
        'payment_method_id',
        'separate_delivery',
        'paid',
        'payment_date',
        'closed',
        'discount',
        'coupon_code',
        'coupon_discount',
        'date',
        'user_id',
        'name',
        'address',
        'phone',
        'email',
        'comment',
        'status_id',
        'url',
        'total_price',
        'note',
        'ip',
        'lang_id',
    ];
    
    protected static $defaultOrderFields = [
        'id DESC',
    ];

    protected static $table = '__orders';
    protected static $tableAlias = 'o';
    protected static $alternativeIdField = 'url';
    protected static $additionalFields = [
        'os.color as status_color',
    ];
    
    public function get($id)
    {
        if (empty($id)) {
            return null;
        }
        
        $this->select->join('LEFT', '__orders_status AS os', 'o.status_id=os.id');
        return parent::get($id);
    }

    public function find(array $filter = [])
    {
        $this->select->join('LEFT', '__orders_labels AS ol', 'o.id=ol.order_id');
        $this->select->join('LEFT', '__orders_status AS os', 'o.status_id=os.id');
        $this->select->groupBy(['id']);
        return parent::find($filter);
    }
    
    public function count(array $filter = [])
    {
        $this->select->join('LEFT', '__orders_labels AS ol', 'o.id=ol.order_id');
        return parent::count($filter);
    }

    public function update($id, $order) {
        parent::update($id, $order);
        return $id;
    }

    public function delete($ids)
    {
        $ids = (array)$ids;
        if (!empty($ids)) {

            $delete = $this->queryFactory->newDelete();
            $delete->from(PurchasesEntity::getTable())
                ->where('order_id IN (:order_ids)')
                ->bindValue('order_ids', $ids);
            $this->db->query($delete);

            $delete = $this->queryFactory->newDelete();
            $delete->from('__orders_labels')
                ->where('order_id IN (:order_ids)')
                ->bindValue('order_ids', $ids);
            $this->db->query($delete);
        }
        return parent::delete($ids);
    }

    public function add($order)
    {
        /** @var OrderStatusEntity $orderStatusEntity */
        $orderStatusEntity = $this->entity->get(OrderStatusEntity::class);
        
        $order = (object)$order;
        $order->url = md5(uniqid($this->config->salt, true));
        if (empty($order->date)) {
            $order->date = 'now()';
        }

        $allStatuses = $orderStatusEntity->mappedBy('id')->find();
        if (empty($order->status_id)) {
            $order->status_id = reset($allStatuses)->id;
        }
        
        $id = parent::add($order);
        if ($allStatuses[$order->status_id]->is_close == 1){
            $this->close(intval($id));
        } else {
            $this->open(intval($id));
        }

        return $id;
    }

    /*Закрытие заказа(списание количества)*/
    public function close($orderId)
    {
        /** @var VariantsEntity $variantsEntity */
        $variantsEntity = $this->entity->get(VariantsEntity::class);
        
        /** @var PurchasesEntity $purchasesEntity */
        $purchasesEntity = $this->entity->get(PurchasesEntity::class);
        
        $order = $this->get(intval($orderId));
        if (empty($order)) {
            return false;
        }

        if (!$order->closed) {
            $variantsAmounts = [];
            $purchases = $purchasesEntity->find(['order_id'=>$order->id]);
            foreach ($purchases as $purchase) {
                if (isset($variantsAmounts[$purchase->variant_id])) {
                    $variantsAmounts[$purchase->variant_id] += $purchase->amount;
                } else {
                    $variantsAmounts[$purchase->variant_id] = $purchase->amount;
                }
            }

            foreach ($variantsAmounts as $id=>$amount) {
                $variant = $variantsEntity->get($id);
                if (empty($variant) || ($variant->stock<$amount)) {
                    return false;
                }
            }
            foreach ($purchases as $purchase) {
                $variant = $variantsEntity->get($purchase->variant_id);
                if (!$variant->infinity) {
                    $newStock = $variant->stock-$purchase->amount;
                    $variantsEntity->update($variant->id, ['stock'=>$newStock]);
                }
            }
            $this->update($order->id, ['closed'=>1]);
        }
        return $order->id;
    }

    /*Открытие заказа (возвращение количества)*/
    public function open($orderId)
    {
        /** @var VariantsEntity $variantsEntity */
        $variantsEntity = $this->entity->get(VariantsEntity::class);

        /** @var PurchasesEntity $purchasesEntity */
        $purchasesEntity = $this->entity->get(PurchasesEntity::class);
        
        $order = $this->get(intval($orderId));
        if (empty($order)) {
            return false;
        }

        if ($order->closed) {
            $purchases = $purchasesEntity->find(['order_id'=>$order->id]);
            foreach ($purchases as $purchase) {
                $variant = $variantsEntity->get($purchase->variant_id);
                if ($variant && !$variant->infinity) {
                    $newStock = $variant->stock+$purchase->amount;
                    $variantsEntity->update($variant->id, ['stock'=>$newStock]);
                }
            }
            $this->update($order->id, ['closed'=>0]);
        }
        return $order->id;
    }

    public function getNeighborsOrders($filter)
    {
        if (empty($filter['id'])) {
            return false;
        }
        $prevSelect = $this->queryFactory->newSelect();
        $nextSelect = $this->queryFactory->newSelect();

        $nextSelect->from('__orders AS o')
            ->cols(['MIN(o.id) as id'])
            ->where("o.id>:id")
            ->bindValue('id', (int)$filter['id'])
            ->limit(1);

        $prevSelect->from('__orders AS o')
            ->cols(['MAX(o.id) as id'])
            ->where("o.id<:id")
            ->bindValue('id', (int)$filter['id'])
            ->limit(1);

        if (!empty($filter['status_id'])) {
            $nextSelect->where('status_id=:status_id')
                ->bindValue('status_id', (int)$filter['status_id']);

            $prevSelect->where('status_id=:status_id')
                ->bindValue('status_id', (int)$filter['status_id']);
        }

        if (!empty($filter['label'])) {
            $nextSelect->join('INNER', '__orders_labels AS ol', 'o.id=ol.order_id AND label_id=:label_id')
                ->bindValue('label_id', (int)$filter['label_id']);

            $prevSelect->join('INNER', '__orders_labels AS ol', 'o.id=ol.order_id AND label_id=:label_id')
                ->bindValue('label_id', (int)$filter['label_id']);
        }

        $ordersIds = [];
        $this->db->query($nextSelect);
        $id = $this->db->result('id');
        $ordersIds[$id] = 'next';

        $this->db->query($prevSelect);
        $id = $this->db->result('id');
        $ordersIds[$id] = 'prev';

        $result = ['next'=>null, 'prev'=>null];
        if (!empty($ordersIds)) {
            foreach ($this->find(['id'=>array_keys($ordersIds)]) as $o) {
                $result[$ordersIds[$o->id]] = $o;
            }
        }
        return $result;
    }

    public function updateTotalPrice($orderId)
    {
        $order = $this->get(intval($orderId));
        if (empty($order)) {
            return false;
        }

        $update = $this->queryFactory->newUpdate();
        $update->table('__orders AS o')
            ->set('o.total_price', 'IFNULL((SELECT SUM(p.price*p.amount)*(100-o.discount)/100 FROM __purchases p WHERE p.order_id=o.id), 0)+o.delivery_price*(1-IFNULL(o.separate_delivery, 0))-o.coupon_discount')
            ->where('o.id=:id')
            ->bindValue('id', $order->id);

        $this->db->query($update);
        return $order->id;
    }
    
    protected function filter__modified_since($modified)
    {
        $this->select->where('o.modified > :modified')
            ->bindValue('modified', $modified);
    }

    protected function filter__label($labelId)
    {
        $this->select->where('ol.label_id = :label_id')
            ->bindValue('label_id', $labelId);
    }

    protected function filter__from_date($fromDate)
    {
        $this->select->where('o.date >= :from_date')
            ->bindValue('from_date', date('Y-m-d', strtotime($fromDate)));
    }

    protected function filter__to_date($toDate)
    {
        $this->select->where('o.date <= :to_date')
            ->bindValue('to_date', date('Y-m-d', strtotime($toDate)));
    }
    
    protected function filter__keyword($keywords)
    {
        $keywords = explode(' ', $keywords);

        foreach ($keywords as $keyNum=>$keyword) {
            $this->select->where("(
                o.id LIKE :keyword_id_{$keyNum}
                OR o.name LIKE :keyword_name_{$keyNum}
                OR REPLACE(o.phone, '-', '') LIKE :keyword_phone_{$keyNum}
                OR o.address LIKE :keyword_address_{$keyNum}
                OR o.email LIKE :keyword_email_{$keyNum}
                OR o.id IN (SELECT order_id FROM __purchases WHERE product_name LIKE :keyword_product_name_{$keyNum} OR variant_name LIKE :keyword_product_name_{$keyNum})
            )");

            $this->select->bindValues([
                "keyword_id_{$keyNum}"           => '%' . $keyword . '%',
                "keyword_name_{$keyNum}"         => '%' . $keyword . '%',
                "keyword_phone_{$keyNum}"        => '%' . $keyword . '%',
                "keyword_address_{$keyNum}"      => '%' . $keyword . '%',
                "keyword_email_{$keyNum}"        => '%' . $keyword . '%',
                "keyword_product_name_{$keyNum}" => '%' . $keyword . '%',
                "keyword_product_name_{$keyNum}" => '%' . $keyword . '%',
            ]);
        }
    }

}
