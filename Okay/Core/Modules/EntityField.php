<?php


namespace Okay\Core\Modules;


use \Exception;
use Okay\Core\Database;
use Okay\Core\Entity\Entity;
use Okay\Core\QueryFactory;
use Okay\Core\ServiceLocator;

class EntityField
{
    
    const TYPE_VARCHAR = 'varchar';
    const TYPE_INT     = 'int';
    const TYPE_TINYINT = 'tinyint';
    const TYPE_FLOAT   = 'float';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_TEXT    = 'text';
    
    const CHANGE_ADD    = 1; // Нужно добавить колонку в базу
    const CHANGE_UPDATE = 2; // Нужно изменить тип колонки в базе
    
    private $type = self::TYPE_VARCHAR;
    private $length = 255;
    private $default = null;
    private $isNull = false;
    private $isLangField = false;
    private $fieldName;
    
    /** @var Database */
    private $db;
    
    /** @var QueryFactory */
    private $queryFactory;

    /**
     * @var Entity Хранится имя класса, можно обращаться только к статическим методам и свойствам
     */
    private $entityClassName;

    /**
     * EntityField constructor.
     * @param $entityClassName 
     * @param $fieldName
     * @throws Exception
     */
    public function __construct($entityClassName, $fieldName)
    {
        if (!is_string($entityClassName) || !is_subclass_of($entityClassName, Entity::class)) {
            throw new Exception("\"$entityClassName\" must be class name subclass of \"" . Entity::class . "\"");
        }
        $this->fieldName = preg_replace('~[\W]~', '', $fieldName);
        $this->entityClassName = $entityClassName;

        if ($this->isFieldExists()) {
            throw new Exception("Field \"{$this->fieldName}\" already exists in \"$entityClassName\"");
        }
        
        $SL = new ServiceLocator();
        $this->db = $SL->getService(Database::class);
        $this->queryFactory = $SL->getService(QueryFactory::class);
    }

    public function setIsLang()
    {
        $this->isLangField = true;
        return $this;
    }

    public function unsetIsLang()
    {
        $this->isLangField = false;
        return $this;
    }
    
    public function setTypeVarchar($length, $isNull = false, $default = null)
    {
        if (!is_int($length)) {
            throw new Exception("Length must be integer");
        }

        $this->resetAll();
        
        $this->type = self::TYPE_VARCHAR;
        $this->isNull = $isNull;
        $this->default = $default;
        $this->length = $length;
        return $this;
    }
    
    public function setTypeInt($length, $isNull = true, $default = 0)
    {
        if (!is_int($length)) {
            throw new Exception("Length must be integer");
        }

        $this->resetAll();
        
        $this->type = self::TYPE_INT;
        $this->isNull = $isNull;
        $this->default = $default;
        $this->length = $length;
        return $this;
    }
    
    public function setTypeTinyInt($length, $isNull = true, $default = 0)
    {
        if (!is_int($length)) {
            throw new Exception("Length must be integer");
        }

        $this->resetAll();
        
        $this->type = self::TYPE_TINYINT;
        $this->isNull = $isNull;
        $this->default = $default;
        $this->length = $length;
        return $this;
    }
    
    public function setTypeFloat($length, $isNull = true, $default = 0.00)
    {
        $this->resetAll();
        
        $this->type = self::TYPE_FLOAT;
        $this->isNull = $isNull;
        $this->default = $default;
        $this->length = $length;
        return $this;
    }
    
    public function setTypeDecimal($length, $isNull = true, $default = 0.00)
    {
        $this->resetAll();
        
        $this->type = self::TYPE_DECIMAL;
        $this->isNull = $isNull;
        $this->default = $default;
        $this->length = $length;
        return $this;
    }
    
    public function setTypeText($length = null)
    {
        if ($length !== null && !is_int($length)) {
            throw new Exception("Length must be integer or null");
        }
        
        $this->resetAll();
        
        $this->type = self::TYPE_TEXT;
        $this->length = $length;
        return $this;
    }

    /**
     * Проверка, нужно ли изменять таблицу в базе
     * @return bool
     * @throws Exception
     */
    private function isNeedChangeDatabase()
    {
        $entityClassName = $this->entityClassName;
        if ($this->isLangField === true) {
            $table = "__lang_{$entityClassName::getLangTable()}";
        } else {
            $table = $entityClassName::getTable();
        }
        $sql = $this->queryFactory->newSqlQuery();
        $sql->setStatement("SHOW COLUMNS FROM `$table`;");
        $this->db->query($sql);
        $currentFields = [];
        foreach ($this->db->results() as $currentField) {
            $currentFields[$currentField->Field] = $currentField;
        }
        
        if (!isset($currentFields[$this->fieldName])) {
            return self::CHANGE_ADD;
        } else {
            $currentField = $currentFields[$this->fieldName];
            
            // Если не совпадает тип или длина поля, нужно изменить таблицу
            if(isset($currentField->Type)) {
                $fieldType = $this->type . (!empty($this->length) ? "({$this->length})" : "");
                if ($fieldType != $currentField->Type) {
                    return self::CHANGE_UPDATE;
                }
            }
            
            // Если не совпадает признак IS NULL, нужно изменить таблицу
            if (($this->isNull === true && $currentField->Null == 'NO')
                || ($this->isNull === false && $currentField->Null == 'YES')) {
                return self::CHANGE_UPDATE;
            }
            
            // Если не совпадает значение по умолчанию, нужно изменить таблицу
            if ($this->default != $currentField->Default) {
                return self::CHANGE_UPDATE;
            }
            
            return false;
        }
    }
    
    private function isFieldExists()
    {
        $entityClassName = $this->entityClassName;
        if (in_array($this->fieldName, $entityClassName::getFields()) || in_array($this->fieldName, $entityClassName::getLangFields())) {
            return true;
        }
        return false;
    }
    
    public function getEntityClass()
    {
        return $this->entityClassName;
    }
    
    public function getIsLang()
    {
        return $this->isLangField;
    }
    
    public function getName()
    {
        return $this->fieldName;
    }
    
    // todo доделать добавление индексов.
    public function index()
    {
        return $this;
    }
    
    public function changeDatabase()
    {
        if ($this->isNeedChangeDatabase() === false) {
            return null;
        } elseif ($this->isNeedChangeDatabase() === self::CHANGE_ADD) {
            $this->addFieldToDatabase();
        } elseif ($this->isNeedChangeDatabase() === self::CHANGE_UPDATE) {
            $this->changeFieldInDatabase();
        }
        
    }
    
    // todo доделать валидацию, защиту от дурака
    private function changeFieldInDatabase()
    {
        $entityClassName = $this->entityClassName;
        
        $typeSql = " {$this->type}" . ($this->length > 0 ? "({$this->length})" : "");
        $nullSql = $this->isNull === true ? ' NULL' : ' NOT NULL';
        $defaultSql = $this->default !== null ? " DEFAULT '{$this->default}'" : "";

        $sqlStatement = "ALTER TABLE `{$entityClassName::getTable()}` CHANGE `{$this->fieldName}` `{$this->fieldName}`{$typeSql}{$nullSql}{$defaultSql};";

        if ($this->isLangField === true) {
            $sqlStatement .= PHP_EOL;
            $sqlStatement .= "ALTER TABLE `__lang_{$entityClassName::getLangTable()}` CHANGE `{$this->fieldName}` `{$this->fieldName}`{$typeSql}{$nullSql}{$defaultSql};";
        }

        $sql = $this->queryFactory->newSqlQuery();
        $sql->setStatement($sqlStatement);
        $this->db->query($sql);
    }

    private function addFieldToDatabase()
    {
        $entityClassName = $this->entityClassName;
        
        $typeSql = " {$this->type}" . ($this->length > 0 ? "({$this->length})" : "");
        $nullSql = $this->isNull === true ? ' NULL' : ' NOT NULL';
        $defaultSql = $this->default !== null ? " DEFAULT '{$this->default}'" : "";

        $sqlStatement = "ALTER TABLE `{$entityClassName::getTable()}` ADD COLUMN `{$this->fieldName}`{$typeSql}{$nullSql}{$defaultSql};";

        if ($this->isLangField === true) {
            $sqlStatement .= PHP_EOL;
            $sqlStatement .= "ALTER TABLE `__lang_{$entityClassName::getLangTable()}` ADD COLUMN `{$this->fieldName}`{$typeSql}{$nullSql}{$defaultSql};";
        }

        $sql = $this->queryFactory->newSqlQuery();
        $sql->setStatement($sqlStatement);
        $this->db->query($sql);
    }
    
    private function resetAll()
    {
        $this->type = null;
        $this->length = null;
        $this->default = null;
        $this->isNull = false;
    }
    
}