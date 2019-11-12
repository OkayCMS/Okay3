<?php


namespace Okay\Modules\OkayCMS\NovaposhtaCost\Init;


use Okay\Admin\Helpers\BackendOrdersHelper;
use Okay\Admin\Requests\BackendProductsRequest;
use Okay\Core\Modules\AbstractInit;
use Okay\Core\Modules\EntityField;
use Okay\Entities\VariantsEntity;
use Okay\Helpers\CartHelper;
use Okay\Helpers\DeliveriesHelper;
use Okay\Helpers\OrdersHelper;
use Okay\Modules\OkayCMS\NovaposhtaCost\Entities\NPCostDeliveryDataEntity;
use Okay\Modules\OkayCMS\NovaposhtaCost\Extenders\BackendExtender;
use Okay\Modules\OkayCMS\NovaposhtaCost\Extenders\FrontExtender;

class Init extends AbstractInit
{
    
    const VOLUME_FIELD = 'volume';
    
    public function install()
    {
        $this->setModuleType(MODULE_TYPE_DELIVERY);
        $this->setBackendMainController('NovaposhtaCostAdmin');
        $this->migrateEntityTable(NPCostDeliveryDataEntity::class, [
            (new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
            (new EntityField('order_id'))->setTypeInt(11)->setIndex(),
            (new EntityField('city_id'))->setTypeVarchar(255, true),
            (new EntityField('warehouse_id'))->setTypeVarchar(255, true),
            (new EntityField('delivery_term'))->setTypeVarchar(8, true),
            (new EntityField('redelivery'))->setTypeTinyInt(1, true),
        ]);

        $this->migrateEntityField(VariantsEntity::class, (new EntityField(self::VOLUME_FIELD))->setTypeDecimal('10,5'));
    }

    public function init()
    {

        $this->registerEntityField(VariantsEntity::class, self::VOLUME_FIELD);
        
        $this->addPermission('okaycms__novaposhta_cost');

        $this->addBackendBlock('product_variant', 'product_variant_block.tpl');
        $this->addBackendBlock('order_contact', 'order_contact_block.tpl');
        $this->addFrontBlock('front_cart_delivery', 'front_cart_delivery_block.tpl');
        
        $this->registerChainExtension(
            ['class' => DeliveriesHelper::class, 'method' => 'prepareDeliveryPriceInfo'],
            ['class' => FrontExtender::class, 'method' => 'setCartDeliveryPrice']
        );
        
        $this->registerChainExtension(
            ['class' => CartHelper::class, 'method' => 'getDefaultCartData'],
            ['class' => FrontExtender::class, 'method' => 'getDefaultCartData']
        );
        
        $this->registerChainExtension(
            ['class' => DeliveriesHelper::class, 'method' => 'getCartDeliveriesList'],
            ['class' => FrontExtender::class, 'method' => 'getCartDeliveriesList']
        );
        
        $this->registerQueueExtension(
            ['class' => OrdersHelper::class, 'method' => 'finalCreateOrderProcedure'],
            ['class' => FrontExtender::class, 'method' => 'setCartDeliveryDataProcedure']
        );

        $this->registerChainExtension(
            ['class' => BackendProductsRequest::class, 'method' => 'postVariants'],
            ['class' => BackendExtender::class, 'method' => 'correctVariantsVolume']
        );
        
        // В админке в заказе достаём данные по доставке
        $this->registerQueueExtension(
            ['class' => BackendOrdersHelper::class, 'method' => 'findOrderDelivery'],
            ['class' => BackendExtender::class, 'method' => 'getDeliveryDataProcedure']
        );

        // В админке в заказе обновляем данные по доставке
        $this->registerQueueExtension(
            ['class' => BackendOrdersHelper::class, 'method' => 'executeCustomPost'],
            ['class' => BackendExtender::class, 'method' => 'updateDeliveryDataProcedure']
        );
        
        $this->registerBackendController('NovaposhtaCostAdmin');
        $this->addBackendControllerPermission('NovaposhtaCostAdmin', 'okaycms__novaposhta_cost');
    }
}
