<?php


namespace Okay\Modules\OkayCMS\Rozetka\Init;


use Okay\Core\Modules\AbstractInit;
use Okay\Core\Modules\EntityField;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;

class Init extends AbstractInit
{

    const TO_FEED_FIELD     = 'to_rozetka';
    const NOT_TO_FEED_FIELD = 'not_to_rozetka';

    public function install()
    {
        $this->setModuleType(MODULE_TYPE_XML);
        $this->setBackendMainController('RozetkaXmlAdmin');

        $field = new EntityField(self::TO_FEED_FIELD);
        $field->setTypeTinyInt(1);
        $this->migrateEntityField(CategoriesEntity::class, $field);

        $field = new EntityField(self::TO_FEED_FIELD);
        $field->setTypeTinyInt(1);
        $this->migrateEntityField(BrandsEntity::class, $field);

        $field = new EntityField(self::TO_FEED_FIELD);
        $field->setTypeTinyInt(1);
        $this->migrateEntityField(ProductsEntity::class, $field);

        $field = new EntityField(self::NOT_TO_FEED_FIELD);
        $field->setTypeTinyInt(1);
        $this->migrateEntityField(ProductsEntity::class, $field);
    }
    
    public function init()
    {
        $this->registerEntityField(CategoriesEntity::class, self::TO_FEED_FIELD);
        $this->registerEntityField(BrandsEntity::class, self::TO_FEED_FIELD);
        $this->registerEntityField(ProductsEntity::class, self::TO_FEED_FIELD);
        $this->registerEntityField(ProductsEntity::class, self::NOT_TO_FEED_FIELD);
        
        $this->registerBackendController('RozetkaXmlAdmin');
        $this->addBackendControllerPermission('RozetkaXmlAdmin', 'rozetka_upload');
        
        $this->registerEntityFilter(
            ProductsEntity::class,
            'rozetka_only',
            \Okay\Modules\OkayCMS\Rozetka\ExtendsEntities\ProductsEntity::class,
            'filter__rozetka_only'
        );
        
    }
    
}