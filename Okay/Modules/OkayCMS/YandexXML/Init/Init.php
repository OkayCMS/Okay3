<?php


namespace Okay\Modules\OkayCMS\YandexXML\Init;


use Okay\Core\Modules\AbstractInit;
use Okay\Core\Modules\EntityField;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;

class Init extends AbstractInit
{
    const TO_FEED_FIELD     = 'to__okaycms__yandex_xml';
    const NOT_TO_FEED_FIELD = 'not_to__okaycms__yandex_xml';
    const FEED_UPLOAD_FIELD = 'upload__okaycms__yandex_xml';

    public function install()
    {
        $this->setModuleType(MODULE_TYPE_XML);
        $this->setBackendMainController('YandexXmlAdmin');

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
        
        $this->registerBackendController('YandexXmlAdmin');
        $this->addBackendControllerPermission('YandexXmlAdmin', self::FEED_UPLOAD_FIELD);
        
        $this->registerEntityFilter(
            ProductsEntity::class,
            'okaycms__yandex_xml__only',
            \Okay\Modules\OkayCMS\YandexXML\ExtendsEntities\ProductsEntity::class,
            'okaycms__yandex_xml__only'
        );

        $this->addBackendBlock('products_icon', 'svg_feed.tpl');
    }
    
}