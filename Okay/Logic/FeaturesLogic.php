<?php


namespace Okay\Logic;


use Okay\Core\Import;
use Okay\Core\Database;
use Okay\Core\Translit;
use Okay\Core\QueryFactory;
use Okay\Core\EntityFactory;
use Okay\Entities\FeaturesEntity;
use Okay\Entities\FeaturesValuesEntity;

class FeaturesLogic
{
    /**
     * @var Database
     */
    private $db;

    /**
     * @var Import
     */
    private $import;

    /**
     * @var Translit
     */
    private $translit;

    /**
     * @var EntityFactory
     */
    private $entityFactory;

    /**
     * @var FeaturesEntity
     */
    private $featuresEntity;

    /**
     * @var FeaturesValuesEntity
     */
    private $featuresValuesEntity;

    /**
     * @var QueryFactory
     */
    private $queryFactory;


    public function __construct(
        Database      $db,
        Import        $import,
        Translit      $translit,
        EntityFactory $entityFactory,
        QueryFactory  $queryFactory
    ){
        $this->db                   = $db;
        $this->import               = $import;
        $this->translit             = $translit;
        $this->queryFactory         = $queryFactory;
        $this->entityFactory        = $entityFactory;
        $this->featuresEntity       = $entityFactory->get(FeaturesEntity::class);
        $this->featuresValuesEntity = $entityFactory->get(FeaturesValuesEntity::class);
    }

    // todo нужно будет перепиливать, написано слишком топорно, нельзя переиспользовать :(
    public function addFeatures($features, $productId, $categoryId)
    {
        $featuresNames   = [];
        $featuresValues  = [];
        $valuesTranslits = [];
        $valuesIds       = [];

        foreach ($features as $featureName => $featureValue) {
            if ($featureValue === '' || empty($categoryId)) {
                continue;
            }

            $sql = $this->queryFactory->newSqlQuery();
            $sql->setStatement("SELECT f.id FROM __features f WHERE f.name=:feature_name LIMIT 1");
            $sql->bindValue('feature_name', $featureName);
            $this->db->query($sql);
            $featureId = $this->db->result('id');
            
            if (empty($featureId)) {
                $featureId = $this->featuresEntity->add(['name' => $featureName]);
            }

            $featuresNames[$featureId]  = $featureName;
            $featuresValues[$featureId] = explode($this->import->getValuesDelimiter(), $featureValue);

            foreach ($featuresValues[$featureId] as $value) {
                $valuesTranslits[] = $this->translit->translitAlpha($value);
            }
        }

        if (empty($featuresNames)) {
            return;
        }

        foreach ($this->featuresValuesEntity->find(['feature_id' => array_keys($featuresNames), 'translit' => $valuesTranslits]) as $value) {
            $valuesIds[$value->feature_id][$value->translit] = $value->id;
        }

        $this->featuresValuesEntity->deleteProductValue($productId, null, array_keys($featuresNames));

        $valuesTransaction = "INSERT IGNORE INTO `__products_features_values` (`product_id`, `value_id`) VALUES ";
        $sqlValues = array();

        foreach ($featuresNames as $featureId => $featureName) {
            $this->featuresEntity->addFeatureCategory($featureId, $categoryId);

            $values = $featuresValues[$featureId];

            foreach ($values as $value) {
                $valueId = null;
                $translit = $this->translit->translitAlpha($value);

                // Ищем значение с таким транслитом
                if (isset($valuesIds[$featureId][$translit])) {
                    $valueId = $valuesIds[$featureId][$translit];
                }

                // Если нет, тогда добавим значение
                if (empty($valueId)) {
                    $featureValue = new \stdClass();
                    $featureValue->value = trim($value);
                    $featureValue->feature_id = $featureId;
                    $featureValue->translit = $this->translit->translitAlpha($value);

                    $valueId = $this->featuresValuesEntity->add($featureValue);
                }

                if (!empty($valueId)) {
                    $sqlValues[] = "('$productId', '$valueId')";
                }
            }
        }

        if (empty($sqlValues)) {
            return;
        }

        $valuesTransaction .= implode(", ", $sqlValues);

        $sql = $this->queryFactory->newSqlQuery();
        $sql->setStatement($valuesTransaction);
        $this->db->query($sql);
    }
}