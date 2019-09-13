<?php


namespace Okay\Core\Entity;


trait entityInfo
{
    
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
     * @return string
     */
    final public static function getLangTable()
    {
        return (string)static::$langTable;
    }

    /**
     * @return string
     */
    final public static function getTable()
    {
        return (string)static::$table;
    }

    /**
     * @return string
     */
    final public static function getTableAlias()
    {
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