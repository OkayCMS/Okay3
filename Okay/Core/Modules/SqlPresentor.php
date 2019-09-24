<?php


namespace Okay\Core\Modules;


class SqlPresentor
{
    public function addColumnQuery($tableName, EntityField $field)
    {
        return "ALTER TABLE `{$tableName}` ADD COLUMN {$this->fieldSql($field)}; ";
    }

    public function changeColumnQuery($tableName, EntityField $field)
    {
        return "ALTER TABLE `{$tableName}` CHANGE `{$field->getName()}` {$this->fieldSql($field)}; ";
    }

    public function createTableQuery($tableName, $entityFields)
    {
        $primaryKeyExists = false;

        $sql  = "CREATE TABLE {$tableName} (";

            foreach($entityFields as $entityField) {
                $sql .= $this->fieldSql($entityField) . ",";

                if ($entityField->isPrimaryKey() && $primaryKeyExists === false) {
                    $sql .= "PRIMARY KEY (`{$entityField->getName()}`),";
                    $primaryKeyExists = true;
                } elseif($entityField->isPrimaryKey()) {
                    throw new \Exception("Table can use only one primary key");
                }
            }

            $sql = substr($sql, 0, -1);
        $sql .= ");";

        return $sql;
    }

    private function fieldSql(EntityField $field)
    {
        $nullSql          = $field->isNullable() ? ' NULL' : ' NOT NULL';
        $defaultSql       = $field->getDefault() !== null ? " DEFAULT '{$field->getDefault()}'" : "";
        $autoIncrementSql = $field->isAutoIncrement() ? " AUTO_INCREMENT " : "";

        return "`{$field->getName()}` {$field->getType()} {$nullSql} {$defaultSql} {$autoIncrementSql}";
    }
}