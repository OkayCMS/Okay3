<?php


namespace Okay\Modules\OkayCMS\Rozetka\Init;


use Okay\Core\Modules\AbstractInit;
use Okay\Core\Modules\EntityField;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;

class Init extends AbstractInit
{
    public function install()
    {
        $this->setModuleType(MODULE_TYPE_XML);
        $this->setBackendMainController('RozetkaXmlAdmin');
    }
    
    public function init()
    {
        $field = new EntityField('to_rozetka');
        $field->setTypeTinyInt(1);
        $this->registerEntityField(CategoriesEntity::class, $field);
        
        $field = new EntityField('to_rozetka');
        $field->setTypeTinyInt(1);
        $this->registerEntityField(BrandsEntity::class, $field);
        
        $field = new EntityField('to_rozetka');
        $field->setTypeTinyInt(1);
        $this->registerEntityField(ProductsEntity::class, $field);
        
        $field = new EntityField('not_to_rozetka');
        $field->setTypeTinyInt(1);
        $this->registerEntityField(ProductsEntity::class, $field);
        
        $this->registerBackendController('RozetkaXmlAdmin');
        $this->addBackendControllerPermission('RozetkaXmlAdmin', 'rozetka_upload');
        
        $this->registerEntityFilter(
            ProductsEntity::class,
            'rozetka_only',
            \Okay\Modules\OkayCMS\Rozetka\ExtendsEntities\ProductsEntity::class,
            'rozetka_only'
        );
        
    }
    
}