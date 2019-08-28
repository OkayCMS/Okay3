<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;

class VariantsEntity extends Entity
{

    protected static $fields = [
        'id',
        'product_id',
        'sku',
        'weight',
        'price',
        'compare_price',
        'stock',
        'position',
        'attachment',
        'external_id',
        'currency_id',
        'feed',
    ];
    
    protected static $additionalFields = [
        '(v.stock IS NULL) as infinity',
        'c.rate_from',
        'c.rate_to',
    ];
    
    protected static $langFields = [
        'name',
        'units',
    ];
    
    protected static $defaultOrderFields = [
        'position',
        'id',
    ];

    protected static $table = '__variants';
    protected static $langObject = 'variant';
    protected static $langTable = 'variants';
    protected static $tableAlias = 'v';

    public function get($id)
    {
        if (empty($id)) {
            return null;
        }
        $this->select->join('left', '__currencies AS c', 'c.id=v.currency_id');

        $variant = parent::get($id);
        
        $variant = $this->resetInfo($variant);
        return $variant;
    }
    
    public function find(array $filter = [])
    {
        $this->select->join('left', '__currencies AS c', 'c.id=v.currency_id');
        $variants = parent::find($filter);

        foreach ($variants as &$variant) {
            $variant = $this->resetInfo($variant);
        }
        
        return $variants;
    }

    protected function resetInfo($variant)
    {
        if (property_exists($variant, 'compare_price') && $variant->compare_price == 0) {
            $variant->compare_price = null;
        }

        if (property_exists($variant, 'stock') && $variant->stock === null) {
            $variant->stock = $this->settings->max_order_amount;
        }
        return $variant;
    }
    
    public function deleteAttachment($ids)
    {
        $ids = (array)$ids;

        $results = $this->cols([
            'id',
            'attachment',
        ])->find([
            'id' => $ids,
            'has_attachment' => true
        ]);
        
        foreach ($results as $result) {
            $exists = $this->cols(['1'])->find([
                'attachment' => $result->attachment,
                'not_id' => $result->id,
            ]);
            if (empty($exists)) {
                @unlink($this->config->root_dir . '/' . $this->config->downloads_dir . $result->attachment);
            }
            $this->update($result->id, ['attachment' => null]);
        }
    }
    
    protected function filter__in_stock()
    {
        $this->select->where('(v.stock > 0 OR v.stock IS NULL)');
    }
    
    protected function filter__has_attachment()
    {
        $this->select->where("attachment !=''");
    }
    
    protected function filter__not_id($id)
    {
        $this->select->where("id!=:not_id");
        $this->select->bindValue('not_id', (int)$id);
    }
    
    protected function filter__has_price()
    {
        $this->select->where("price > 0");
    }
}