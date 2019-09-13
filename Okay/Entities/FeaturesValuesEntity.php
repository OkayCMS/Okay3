<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;
use Okay\Core\Money;
use Okay\Core\Translit;

class FeaturesValuesEntity extends Entity
{

    protected static $fields = [
        'id',
        'feature_id',
        'position',
        'to_index',
    ];

    protected static $langFields = [
        'value',
        'translit',
    ];

    protected static $defaultOrderFields = [
        'position ASC',
        'value ASC',
    ];
    
    protected static $searchFields = [
        'value',
    ];

    protected static $table = '__features_values';
    protected static $langObject = 'feature_value';
    protected static $langTable = 'features_values';
    protected static $tableAlias = 'fv';

    /**
     * @var Translit
     */
    private $translit;

    public function __construct()
    {
        parent::__construct();
        $this->translit = $this->serviceLocator->getService(Translit::class);
    }

    /*добавление значения свойства*/
    public function add($featureValue) {

        $featureValue = (object)$featureValue;

        if (empty($featureValue->value) || empty($featureValue->feature_id)) {
            return false;
        }

        $featureValue->value = trim($featureValue->value);

        if (empty($featureValue->translit)) {
            $featureValue->translit = $this->translit->translitAlpha($featureValue->value);
        }
        $featureValue->translit = $this->translit->translitAlpha($featureValue->translit);

        return parent::add($featureValue);
    }

    /*Обновление значения свойства*/
    public function update($id, $featureValue)
    {
        $featureValue = (object)$featureValue;

        if (!empty($featureValue->value)) {
            $featureValue->value = trim($featureValue->value);
        }

        if (empty($featureValue->translit) && !empty($featureValue->value)) {
            $featureValue->translit = $this->translit->translitAlpha($featureValue->value);
        }

        if (!empty($featureValue->translit)) {
            $featureValue->translit = $this->translit->translitAlpha($featureValue->translit);
        }

        return parent::update($id, $featureValue);
    }

    public function find(array $filter = [])
    {
        $this->select->groupBy([$this->getTableAlias().'.id']);

        $this->select->join('LEFT', '__products_features_values AS pf', 'pf.value_id=fv.id');
        
        // Нужно фильтр по свойствам применить здесь, чтобы он отработал до всех джоинов
        if (isset($filter['features'])) {
            $this->filter__features($filter['features']);
            unset($filter['features']);
        }
        
        $this->select->join('LEFT', '__features AS f', 'f.id=fv.feature_id');
        $this->select->groupBy(['l.value']);
        $this->select->groupBy(['l.translit']);

        if (isset($filter['visible']) || isset($filter['price'])) {
            $this->select->join('LEFT', '__products AS p', 'p.id=pf.product_id');
        }
        
        return parent::find($filter);
    }

    /*Удаление значения свойства*/
    public function delete($valuesIds = null)
    {

        // TODO удалять с алиасов
        $this->deleteProductValue(null, $valuesIds);

        return parent::delete($valuesIds);
    }

    public function countProductsByValueId(array $valuesIds)
    {
        $select = $this->queryFactory->newSelect();
        $select->cols([
            'COUNT(product_id) AS count',
            'value_id',
        ])
            ->from('__products_features_values')
            ->where('value_id IN (?)', $valuesIds)
            ->groupBy(['value_id']);
        
        $this->db->query($select);
        return $this->db->results(null, 'value_id');
    }

    protected function filter__product_id($productsIds)
    {
        $this->select->where('pf.product_id IN (:products_ids)')
            ->bindValue('products_ids', (array)$productsIds);
    }
    
    protected function filter__brand_id($brandsIds)
    {
        $this->select->where('pf.product_id IN (SELECT id FROM __products WHERE brand_id IN (:brands_ids))')
            ->bindValue('brands_ids', (array)$brandsIds);
    }
    
    protected function filter__features($features)
    {
        foreach ($features as $featureId=>$value) {

            $subQuery = $this->queryFactory->newSelect();
            $subQuery->from('__products_features_values AS pf')
                ->cols(['DISTINCT(pf.product_id)'])
                ->join('LEFT', '__features_values AS fv', 'fv.id=pf.value_id');

            // Алиас для таблицы без языков
            $optionsPx = 'fv';
            
            if (!empty($this->lang->getLangId())) {
                $subQuery->where('lfv.lang_id=' . (int)$this->lang->getLangId())
                    ->join('LEFT', '__lang_features_values AS lfv', 'fv.id=lfv.feature_value_id');
                // Алиас для таблицы с языками
                $optionsPx = 'lfv';
            }
            
            $subQuery->where("({$optionsPx}.translit IN (:translit_features_subquery_{$featureId}) AND fv.feature_id=:feature_id_features_subquery_{$featureId})");

            $subQuery->bindValues([
                "translit_features_subquery_{$featureId}" => (array)$value,
                "feature_id_features_subquery_{$featureId}" => $featureId,
            ]);
            
            $this->select->where("(fv.feature_id =:feature_id_{$featureId} OR p.id IN (?))", $subQuery);
            $this->select->bindValue("feature_id_{$featureId}", $featureId);
        }
    }

    protected function filter__price(array $priceRange)
    {
        $coef = $this->serviceLocator->getService(Money::class)->getCoefMoney();

        if (isset($priceRange['min'])) {
            $this->select->where("floor(IF(pv.currency_id=0 OR c.id is null,pv.price, pv.price*c.rate_to/c.rate_from)*{$coef})>=:price_min")
                ->bindValue('price_min', trim($priceRange['min']));
        }
        if (isset($priceRange['max'])) {
            $this->select->where("floor(IF(pv.currency_id=0 OR c.id is null,pv.price, pv.price*c.rate_to/c.rate_from)*{$coef})<=:price_max")
                ->bindValue('price_max', trim($priceRange['max']));
        }

        $this->select->join('LEFT', '__variants AS pv', 'pv.product_id = p.id');
        $this->select->join('LEFT', '__currencies AS c', 'c.id=pv.currency_id');
    }
    
    protected function filter__category_id($categoriesIds)
    {
        $this->select->join('INNER', '__products_categories AS pc', 'pc.product_id=pf.product_id AND pc.category_id IN (:category_id)')
            ->bindValue('category_id', (array)$categoriesIds);
    }
    
    protected function filter__visible($visible)
    {
        $this->select->where('p.visible=:visible')
            ->bindValue('visible', (int)$visible);
    }
    
    protected function filter__other_filter($otherFilters)
    {
        $otherFilterArray = [];
        if (isset($otherFilters['featured'])) {
            $otherFilterArray[] = 'pf.product_id IN (SELECT id FROM __products WHERE featured=1)';
        }

        if (isset($otherFilters['discounted'])) {
            $otherFilterArray[] = '(SELECT 1 FROM __variants pv WHERE pv.product_id=pf.product_id AND pv.compare_price>0 LIMIT 1) = 1';
        }
        if (!empty($otherFilterArray)) {
            $this->select->where(implode(' OR ', $otherFilterArray));
        }
    }
    
    /**
     * @param array $features
     * example $features[feature_id] = [value1_id, value2_id ...]
     * Метод возвращает только мультиязычные поля значений свойств, используется для построения alternate на странице фильтра
     * @return array
     * result [
     *  lang_id => [
     *          feature1_id => [
     *              value1_id => $value1,
     *              value2_id => $value2
     *          ],
     *          feature2_id => [
     *              value3_id => $value3,
     *              value4_id => $value4
     *          ]
     *      ]
     *  ]
     */
    public function getFeaturesValuesAllLang($features = []) {
        
        if (empty($features)) {
            return [];
        }
        
        $select = $this->queryFactory->newSelect();
        $select->from('__lang_features_values AS lv')
            ->cols([
                'lv.lang_id',
                'lv.feature_value_id',
                'lv.value',
                'lv.translit',
                'fv.feature_id',
            ])
            ->join('left', '__features_values AS fv', 'fv.id = lv.feature_value_id');
        
        foreach ($features as $featureId=>$valuesIds) {
            if (!empty($valuesIds)) {
                $select->orWhere("(fv.feature_id=:feature_id_{$featureId} AND feature_value_id IN (:values_ids_{$featureId}))")
                    ->bindValues([
                        "feature_id_{$featureId}" => $featureId,
                        "values_ids_{$featureId}" => $valuesIds,
                    ]);
            }
        }
        
        $result = [];
        $this->db->query($select);
        foreach ($this->db->results() as $res) {
            $result[$res->lang_id][$res->feature_id][$res->feature_value_id] = $res;
        }
        return $result;
    }
    
    /*добавление значения свойства товара*/
    public function addProductValue($productId, $valueId)
    {

        if (empty($productId) || empty($valueId)) {
            return false;
        }

        $insert = $this->queryFactory->newInsert();
        $insert->into('__products_features_values')
            ->cols([
                'product_id',
                'value_id',
            ])
            ->bindValues([
                'product_id' => $productId,
                'value_id' => $valueId,
            ])
            ->ignore();
        if ($this->db->query($insert)) {
            return true;
        }
        return false;
    }

    /*Метод возвращает ID всех значений свойств товаров*/
    public function getProductValuesIds($productId)
    {

        if (empty($productId)) {
            return false;
        }
        
        $select = $this->queryFactory->newSelect();
        $select->from('__products_features_values')
            ->cols([
                'product_id',
                'value_id',
            ])
            ->where('product_id IN (:product_id)')
            ->bindValue('product_id', (array)$productId);
        
        if ($this->db->query($select)) {
            return $this->db->results();
        }
        return false;
    }

    /*удаление связки значения свойства и товара*/
    public function deleteProductValue($productsIds, $valuesIds = null, $featuresIds = null)
    {
        $productIdFilter  = '';
        $valueIdFilter    = '';
        $featureIdFilter  = '';
        $featureIdJoin    = '';

        /*Удаляем только если передали хотябы один аргумент*/
        if (empty($productsIds) && empty($valuesIds) && empty($featuresIds)) {
            return false;
        }

        if (!empty($productsIds)) {
            $productIdFilter = "AND `pf`.`product_id` in (" . implode(',', (array)$productsIds) . ")";
        }

        if (!empty($valuesIds)) {
            $valueIdFilter = "AND `pf`.`value_id` in (" . implode(',', (array)$valuesIds) . ")";
        }

        if (!empty($featuresIds)) {
            $featureIdFilter = "AND `fv`.`feature_id` in (" . implode(',', (array)$featuresIds) . ")";
            $featureIdJoin   = "INNER JOIN `__features_values` as `fv` ON `pf`.`value_id`=`fv`.`id`";
        }

        $sql = $this->queryFactory->newSqlQuery();
        $sql->setStatement("DELETE `pf`
                                FROM `__products_features_values` as `pf`
                                    $featureIdJoin 
                                WHERE 1 
                                    $productIdFilter
                                    $valueIdFilter
                                    $featureIdFilter
                                    ");
        $this->db->query($sql);

        return true;
    }

}
