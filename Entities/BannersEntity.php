<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;

class BannersEntity extends Entity
{
    
    protected static $fields = [
        'id',
        'group_id',
        'name',
        'position',
        'visible',
        'show_all_pages',
        'categories',
        'pages',
        'brands',
    ];

    protected static $defaultOrderFields = [
        'position',
    ];

    protected static $table = '__banners';
    protected static $tableAlias = 'b';
    protected static $alternativeIdField = 'group_id';

    public function delete($ids)
    {
        if (empty($ids)) {
            return false;
        }

        $ids = (array)$ids;

        /** @var BannersImagesEntity $bannersImagesEntity */
        $bannersImagesEntity = $this->entity->get(BannersImagesEntity::class);
        $bannersImagesIds = $bannersImagesEntity->cols(['id'])->find(['banner_id'=>$ids]);
        $bannersImagesEntity->delete($bannersImagesIds);

        return parent::delete($ids);
    }
    
}
