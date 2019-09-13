<?php


namespace Okay\Modules\OkayCMS\Rozetka\ExtendsEntities;


use Okay\Core\Modules\AbstractModuleEntityFilter;

class ProductsEntity extends AbstractModuleEntityFilter
{
    public function rozetka_only($categoriesIds, $filter)
    {
        $categoryFilter = '';
        if (!empty($categoriesIds)) {
            $categoryFilter = "OR p.id IN (SELECT product_id FROM __products_categories WHERE category_id IN (:category_id))";
            $this->select->bindValue('category_id', (array)$categoriesIds);
        }

        $this->select->where('not_to_rozetka != 1');
        $this->select->where("(
            p.to_rozetka=1 
            OR p.brand_id IN (SELECT id FROM __brands WHERE to_rozetka = 1)
            {$categoryFilter}
        )");
    }
}