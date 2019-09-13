<?php


namespace Okay\Core\Entity;


use Okay\Core\Modules\ModulesEntitiesFilters;
use Okay\Core\QueryFactory;
use Aura\SqlQuery\Common\Select;
use Okay\Core\Config;
use Okay\Core\Database;
use Okay\Core\Languages;
use Okay\Core\EntityFactory;
use Okay\Core\ServiceLocator;
use Okay\Core\Settings;

abstract class Entity implements EntityInterface, FilterPriorityInterface
{
    
    use CRUD, lang, order, filter, entityInfo, filterPriority;

    /**
     * @var array
     * Массив полей сущности
     */
    protected static $fields;

    /**
     * @var array
     * Массив мельтиязычных полей сущности
     */
    protected static $langFields;

    /**
     * @var array
     * Массив дополнительных полей сущности, с других таблиц или которые как подзапросы идут.
     * К ним префикс таблицы не добавляется
     */
    protected static $additionalFields;

    /**
     * @var array
     * Массив полей по которым происходит текстовый поиск
     */
    protected static $searchFields;

    /**
     * @var string
     * Название таблицы сущности, начиная с __
     */
    protected static $table;

    /**
     * @var string
     * Используется для связи с мультиязычными данными (в языковых таблицах blog_id, product_id)
     */
    protected static $langObject;

    /**
     * @var string
     * Название языковой таблицы без __lang_
     */
    protected static $langTable;

    /**
     * @var string
     * Алиас основной таблицы
     */
    protected static $tableAlias;

    /**
     * @var array
     * Массив полей по которым происходит сортировка по умолчанию
     */
    protected static $defaultOrderFields;

    /**
     * @var string
     * поле по которому может происходить get() если id передали строкой (url, code etc...)
     */
    protected static $alternativeIdField;

    /**
     * @var array
     * Массив названий фильтров, которые нужно выполнять в первую очередь (для использования индексов)
     */
    private $highPriorityFilters;

    /**
     * @var array
     * Массив названий фильтров, которые нужно выполнять в последнюю очередь (для НЕ использования индексов)
     */
    private $lowPriorityFilters;
    
    /**
     * @var array
     * Когда нужно доставать не все поля сущности, можно через setSelectFields() передать массив названий колонок
     */
    private $selectFields;
    
    /** 
     * @var ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @var QueryFactory
     */
    protected $queryFactory;
    
    /**
     * @var Database
     */
    protected $db;
    
    /**
     * @var Languages
     */
    protected $lang;
    
    /**
     * @var EntityFactory
     */
    protected $entity;
    
    /**
     * @var Config
     */
    protected $config;
    
    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var integer
     */
    protected $langId;
    
    /**
     * @var Select
     */
    protected $select;
    
    /**
     * @var ModulesEntitiesFilters
     */
    protected $modulesFilters;

    /**
     * @var string
     */
    protected $mappedBy = null;

    public function __construct()
    {
        $this->serviceLocator = new ServiceLocator();
        $this->queryFactory   = $this->serviceLocator->getService(QueryFactory::class);
        $this->db             = $this->serviceLocator->getService(Database::class);
        $this->lang           = $this->serviceLocator->getService(Languages::class);
        $this->entity         = $this->serviceLocator->getService(EntityFactory::class);
        $this->config         = $this->serviceLocator->getService(Config::class);
        $this->settings       = $this->serviceLocator->getService(Settings::class);
        $this->modulesFilters = $this->serviceLocator->getService(ModulesEntitiesFilters::class);
        $this->flush();
    }

    /**
     * @param string $order
     * @return array
     * Здесь это метод-заглушка, если нужно применить кастомную сортировку,
     * переопределяем этот метод в нужном Entity классе.
     * Там через switch case описываем кастомные сортировки
     */
    protected function customOrder($order = null)
    {
        $orderFields = [];

        // Пример, как реализовать кастомную сортировку.
        /*switch ($order) {
            case 'some_custom_order' :
                $orderFields = [
                    'visible',
                    'name'
                ];
                break;
        }*/

        return $orderFields;
    }

    public function flush()
    {
        $this->select = $this->queryFactory->newSelect();
        // Установим сортировку по умолчанию
        $this->order();
        $this->resetPriority();
        $this->mappedBy = null;
        $this->selectFields = [];
    }
    
    protected function filter__keyword($keywords)
    {
        $keywords = explode(' ', $keywords);

        $tableAlias = $this->getTableAlias();
        $langAlias = $this->lang->getLangAlias(
            $this->getTableAlias()
        );
        
        $fields = $this->getFields();
        $langFields = $this->getLangFields();
        
        $searchFields = $this->getSearchFields();
        foreach ($keywords as $keyNum=>$keyword) {
            $keywordFilter = [];
            foreach ($searchFields as $searchField) {
                $searchFieldWithAlias = $searchField;
                
                if (in_array($searchField, $fields)) {
                    $searchFieldWithAlias = $tableAlias . "." . $searchField;
                } elseif (in_array($searchField, $langFields)) {
                    $searchFieldWithAlias = $langAlias . "." . $searchField;
                }
                
                $keywordFilter[] = $searchFieldWithAlias . " LIKE :auto_keyword_{$searchField}_{$keyNum}";
                $this->select->bindValue("auto_keyword_{$searchField}_{$keyNum}", '%' . $keyword . '%');
            }
            $this->select->where('(' . implode(' OR ', $keywordFilter) . ')');
            
        }
    }

    final public function mappedBy($columnName)
    {
        if (!in_array($columnName, $this->getFields())) {
            throw new \Exception('Incorrect column name in mappedBy');
        }

        $this->mappedBy = $columnName;
        return $this;
    }
}
