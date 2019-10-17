<?php


namespace Okay\Admin\Helpers;


use Okay\Core\Translit;
use Okay\Core\Database;
use Okay\Core\QueryFactory;
use Okay\Core\EntityFactory;
use Okay\Entities\FeaturesEntity;
use Okay\Entities\FeaturesValuesEntity;
use Okay\Core\Modules\Extender\ExtenderFacade;

class BackendFeaturesHelper
{
    private $featuresValuesEntity;
    private $featuresEntity;
    private $queryFactory;
    private $translit;
    private $db;

    public function __construct(
        EntityFactory $entityFactory,
        QueryFactory $queryFactory,
        Translit $translit,
        Database $db
    ) {
        $this->featuresValuesEntity = $entityFactory->get(FeaturesValuesEntity::class);
        $this->featuresEntity = $entityFactory->get(FeaturesEntity::class);
        $this->queryFactory = $queryFactory;
        $this->translit = $translit;
        $this->db = $db;
    }

    public function updateProductFeatures($product, $featuresValues, $featuresValuesText, $newFeaturesNames, $newFeaturesValues)
    {
        // Удалим все значения свойств товара
        $this->featuresValuesEntity->deleteProductValue($product->id);
        if (!empty($featuresValues)) {
            foreach ($featuresValues as $featureId => $feature_values) {
                foreach ($feature_values as $k => $valueId) {

                    $value = trim($featuresValuesText[$featureId][$k]);
                    if (!empty($value)) {
                        if (!empty($valueId)) {
                            $this->featuresValuesEntity->update($valueId, ['value' => $value]);
                        } else {
                            /**
                             * Проверим может есть занчение с таким транслитом,
                             * дабы исключить дублирование значений "ТВ приставка" и "TV приставка" и подобных
                             */
                            $valueTranslit = $this->translit->translitAlpha($value);

                            // Ищем значение по транслиту в основной таблице, если мы создаем значение не на основном языке
                            $select = $this->queryFactory->newSelect();
                            $select->from('__features_values')
                                ->cols(['id'])
                                ->where('feature_id=:feature_id')
                                ->where('translit=:translit')
                                ->limit(1)
                                ->bindValues([
                                    'feature_id' => $featureId,
                                    'translit' => $valueTranslit,
                                ]);
                            $this->db->query($select);
                            $valueId = $this->db->result('id');

                            if (empty($valueId) && ($fv = $this->featuresValuesEntity->find(['feature_id' => $featureId, 'translit' => $valueTranslit]))) {
                                $fv = reset($fv);
                                $valueId = $fv->id;
                            }

                            // Если такого значения еще нет, но его запостили тогда добавим
                            if (!$valueId) {
                                $toIndex = $this->featuresEntity->cols(['to_index_new_value'])->get((int)$featureId)->to_index_new_value;
                                $featureValue = new \stdClass();
                                $featureValue->value = $value;
                                $featureValue->feature_id = $featureId;
                                $featureValue->to_index = $toIndex;
                                $valueId = $this->featuresValuesEntity->add($featureValue);
                            }
                        }
                    }

                    if (!empty($valueId)) {
                        $this->featuresValuesEntity->addProductValue($product->id, $valueId);
                    }
                }
            }
        }

        // Новые характеристики
        if (is_array($newFeaturesNames) && is_array($newFeaturesValues)) {
            foreach ($newFeaturesNames as $i => $name) {
                $value = trim($newFeaturesValues[$i]);
                if (!empty($name) && !empty($value)) {
                    $featuresIds = $this->featuresEntity->cols(['id'])->find([
                        'name' => trim($name),
                        'limit' => 1,
                    ]);

                    $featureId = reset($featuresIds);

                    if (empty($featureId)) {
                        $featureId = $this->featuresEntity->add(['name' => trim($name)]);
                    }
                    $this->featuresEntity->addFeatureCategory($featureId, reset($productCategories)->id);

                    // Добавляем вариант значения свойства
                    $featureValue = new \stdClass();
                    $featureValue->feature_id = $featureId;
                    $featureValue->value = $value;
                    $valueId = $this->featuresValuesEntity->add($featureValue);

                    // Добавляем значения к товару
                    $this->featuresValuesEntity->addProductValue($product->id, $valueId);
                }
            }
        }

        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }

    public function findProductFeaturesValues($product)
    {
        $featuresValues = [];
        if (!empty($product->id)) {
            foreach ($this->featuresValuesEntity->find(['product_id' => $product->id]) as $fv) {
                $featuresValues[$fv->feature_id][] = $fv;
            }
        }

        return ExtenderFacade::execute(__METHOD__, $featuresValues, func_get_args());
    }

    public function findCategoryFeatures(array $productCategories, array $categoriesTree)
    {
        $features = [];

        $category = reset($productCategories);
        if (!is_object($category)) {
            $category = reset($categoriesTree);
        }

        if (is_object($category)) {
            $features = $this->featuresEntity->find(['category_id' => $category->id]);
        }

        return ExtenderFacade::execute(__METHOD__, $features, func_get_args());
    }
}