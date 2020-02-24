<?php


namespace Okay\Modules\OkayCMS\YandexXMLVendorModel\Init;


use Okay\Admin\Helpers\BackendExportHelper;
use Okay\Admin\Helpers\BackendImportHelper;
use Okay\Core\Modules\AbstractInit;
use Okay\Core\Modules\EntityField;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Modules\OkayCMS\YandexXMLVendorModel\Extenders\BackendExtender;

class Init extends AbstractInit
{
    const TO_FEED_FIELD     = 'to__okaycms__yandex_xml_vendor_model';
    const NOT_TO_FEED_FIELD = 'not_to__okaycms__yandex_xml_vendor_model';
    const FEED_UPLOAD_FIELD = 'upload__okaycms__yandex_xml_vendor_model';

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

        $this->addBackendBlock('import_fields_association', 'import_fields_association.tpl');

        $this->registerChainExtension(
            ['class' => BackendImportHelper::class, 'method' => 'parseProductData'],
            ['class' => BackendExtender::class, 'method' => 'parseProductData']
        );
        
        $this->registerChainExtension(
            ['class' => BackendExportHelper::class, 'method' => 'getColumnsNames'],
            ['class' => BackendExtender::class, 'method' => 'extendExportColumnsNames']
        );
        
        $this->registerEntityFilter(
            ProductsEntity::class,
            'okaycms__yandex_xml_vendor_model__only',
            \Okay\Modules\OkayCMS\YandexXMLVendorModel\ExtendsEntities\ProductsEntity::class,
            'okaycms__yandex_xml_vendor_model__only'
        );
        
    }
    
}