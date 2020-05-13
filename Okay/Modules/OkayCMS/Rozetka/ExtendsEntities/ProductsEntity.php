<?php


namespace Okay\Modules\OkayCMS\Rozetka\ExtendsEntities;


use Okay\Core\Modules\AbstractModuleEntityFilter;
use Okay\Modules\OkayCMS\Rozetka\Init\Init;

class ProductsEntity extends AbstractModuleEntityFilter
{
    public function filter__rozetka_only($categoriesIds, $filter)
    {
        $categoryFilter = '';
        if (!empty($categoriesIds)) {
            $categoryFilter = "OR p.id IN (SELECT product_id FROM __products_categories WHERE category_id IN (:category_id))";
            $this->select->bindValue('category_id', (array)$categoriesIds);
        }

        $this->select->where("(p.".Init::NOT_TO_FEED_FIELD." != 1 OR p.".Init::NOT_TO_FEED_FIELD." IS NULL)");
        $this->select->where("(
            p.".Init::TO_FEED_FIELD."=1 
            OR p.brand_id IN (SELECT id FROM __brands WHERE ".Init::TO_FEED_FIELD." = 1)
            {$categoryFilter}
        )");
    }
}