<?php


namespace Okay\Core\Entity;


trait entityInfo
{

    /**
     * Метод возвращает все поля сущности, за исключением переданных
     * 
     * @var array $excludedFields поля, которые нужно исключить
     * @return array
     */
    final public static function getDifferentFields($excludedFields)
    {
        $fields = static::getFields();
        $langFields = static::getLangFields();
        $additionalFields = static::getAdditionalFields();

        $allFields = array_merge($fields, $langFields, $additionalFields);
        foreach ($excludedFields as $field) {
            if (($fieldKey = array_search($field, $allFields)) !== false && isset($allFields[$fieldKey])) {
                unset($allFields[$fieldKey]);
            }
        }
        
        return (array)$allFields;
    }
    
    /**
     * @var array $fields
     */
    final public function setSelectFields(array $fields)
    {
        $this->selectFields = $fields;
    }
    
    /**
     * @return array
     */
    final public static function getFields()
    {
        return (array)static::$fields;
    }
    
    /**
     * @return array
     */
    final public static function getAdditionalFields()
    {
        return (array)static::$additionalFields;
    }

    /**
     * @return array
     */
    final public static function getSearchFields()
    {
        return (array)static::$searchFields;
    }

    /**
     * @return array
     */
    final public static function getDefaultOrderFields()
    {
        return (array)static::$defaultOrderFields;
    }

    /**
     * @return string
     */
    final public static function getLangObject()
    {
        return (string)static::$langObject;
    }

    /**
     * @return array
     */
    final public static function getLangFields()
    {
        return (array)static::$langFields;
    }

    /**
     * @return string|null
     */
    final public static function getLangTable()
    {
        $table = (string)static::$langTable;
        if (empty($table)) {
            return null;
        }
        return '__lang_' . preg_replace('~(__lang_)?(.+)~', '$2', $table);
    }

    /**
     * @return string
     */
    final public static function getTable()
    {
        $table = (string)static::$table;
        return '__' . preg_replace('~(__)?(.+)~', '$2', $table);
    }

    /**
     * @return string
     */
    final public static function getTableAlias()
    {
        if (empty(static::$tableAlias)) {
            static::$tableAlias = substr(preg_replace('~(__)?(.+)~', '$2', self::getTable()), 0, 1);
        }
        return (string)static::$tableAlias;
    }

    /**
     * @return string
     */
    final public static function getAlternativeIdField()
    {
        return (string)static::$alternativeIdField;
    }

    final public static function addField($name)
    {
        if (!in_array($name, static::getFields())) {
            static::$fields[] = $name;
        }
    }

    final public static function addLangField($name)
    {
        if (!in_array($name, static::getLangFields())) {
            static::$langFields[] = $name;
        }
    }
}