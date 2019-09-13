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
        $field = new EntityField(CategoriesEntity::class, 'to_rozetka');
        $field->setTypeTinyInt(1);
        $this->registerEntityField($field);
        
        $field = new EntityField(BrandsEntity::class, 'to_rozetka');
        $field->setTypeTinyInt(1);
        $this->registerEntityField($field);
        
        $field = new EntityField(ProductsEntity::class, 'to_rozetka');
        $field->setTypeTinyInt(1);
        $this->registerEntityField($field);
        
        $field = new EntityField(ProductsEntity::class, 'not_to_rozetka');
        $field->setTypeTinyInt(1);
        $this->registerEntityField($field);
        
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