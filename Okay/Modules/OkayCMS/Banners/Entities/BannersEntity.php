<?php


namespace Okay\Modules\OkayCMS\Banners\Entities;


use Okay\Core\Entity\Entity;

class BannersEntity extends Entity
{
    
    protected static $fields = [
        'id',
        'name',
        'group_name',
        'position',
        'visible',
        'show_all_pages',
        'categories',
        'pages',
        'brands',
        'individual_shortcode',
        'settings',
        'show_all_products',
    ];

    protected static $defaultOrderFields = [
        'position',
    ];

    protected static $table = 'okaycms__banners';
    protected static $tableAlias = 'b';
    protected static $alternativeIdField = 'individual_shortcode';

    public function delete($ids)
    {
        if (empty($ids)) {
            return parent::delete($ids);
        }

        $ids = (array)$ids;

        /** @var BannersImagesEntity $bannersImagesEntity */
        $bannersImagesEntity = $this->entity->get(BannersImagesEntity::class);
        $bannersImagesIds = $bannersImagesEntity->cols(['id'])->find(['banner_id'=>$ids]);
        $bannersImagesEntity->delete($bannersImagesIds);

        return parent::delete($ids);
    }
    
    protected function filter__show_on($showOnEntitiesIds)
    {
        foreach($showOnEntitiesIds as $k=>$values) {
            if(empty($values)) {
                unset($showOnEntitiesIds[$k]);
                continue;
            }
            $showFilterArray[$k] = $k." regexp '[[:<:]]({$showOnEntitiesIds[$k]})[[:>:]]'";
        }
        $showFilterArray[] = "show_all_pages=1";
        $this->select->where('(' . implode(' OR ',$showFilterArray) . ')');
    }
    
}
