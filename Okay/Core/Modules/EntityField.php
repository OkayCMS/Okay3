<?php


namespace Okay\Core\Modules;


use \Exception;

class EntityField
{
    
    const TYPE_VARCHAR = 'varchar';
    const TYPE_INT     = 'int';
    const TYPE_TINYINT = 'tinyint';
    const TYPE_FLOAT   = 'float';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_TEXT    = 'text';
    
    private $type = self::TYPE_VARCHAR;
    private $length = 255;
    private $default = null;
    private $nullable = false;
    private $isLangField = false;
    private $autoIncrement = false;
    private $primaryKey = false;
    private $fieldName;

    public function __construct($name)
    {
        $this->fieldName = preg_replace('~[\W]~', '', $name);
    }

    public function isLangField()
    {
        return $this->isLangField;
    }

    public function isAutoIncrement()
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement()
    {
        $this->autoIncrement = true;
        return $this;
    }

    public function unsetAutoIncrement()
    {
        $this->autoIncrement = false;
        return $this;
    }

    public function isPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function setPrimaryKey()
    {
        $this->primaryKey = true;
        return $this;
    }

    public function unsetPrimaryKey()
    {
        $this->primaryKey = false;
        return $this;
    }

    public function getType()
    {
        return $this->type . (!empty($this->length) ? "({$this->length})" : "");
    }

    public function isNullable()
    {
        return $this->nullable === true;
    }

    public function isNotNullable()
    {
        return $this->nullable === false;
    }

    public function setNullable()
    {
        $this->nullable = true;
        return $this;
    }

    public function unsetNullable()
    {
        $this->nullable = false;
        return $this;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function getLength()
    {
        return $this->length;
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
    
    public function setTypeVarchar($length, $nullable = false)
    {
        if (!is_int($length)) {
            throw new Exception("Length must be integer");
        }

        $this->resetAll();
        
        $this->type = self::TYPE_VARCHAR;
        $this->nullable = $nullable;
        $this->length = $length;
        return $this;
    }

    public function setDefault($default)
    {
        $this->default = $default;
    }
    
    public function setTypeInt($length, $nullable = true)
    {
        if (!is_int($length)) {
            throw new Exception("Length must be integer");
        }

        $this->resetAll();
        
        $this->type = self::TYPE_INT;
        $this->nullable = $nullable;
        $this->length = $length;
        return $this;
    }
    
    public function setTypeTinyInt($length, $nullable = true)
    {
        if (!is_int($length)) {
            throw new Exception("Length must be integer");
        }

        $this->resetAll();
        
        $this->type = self::TYPE_TINYINT;
        $this->nullable = $nullable;
        $this->length = $length;
        return $this;
    }
    
    public function setTypeFloat($length, $nullable = true)
    {
        $this->resetAll();
        
        $this->type = self::TYPE_FLOAT;
        $this->nullable = $nullable;
        $this->length = $length;
        return $this;
    }
    
    public function setTypeDecimal($length, $nullable = true)
    {
        $this->resetAll();
        
        $this->type = self::TYPE_DECIMAL;
        $this->nullable = $nullable;
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
    
    private function resetAll()
    {
        $this->type = null;
        $this->length = null;
        $this->default = null;
        $this->nullable = false;
    }
    
}