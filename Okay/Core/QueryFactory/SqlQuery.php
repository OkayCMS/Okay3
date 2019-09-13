<?php


namespace Okay\Core\QueryFactory;


use Aura\SqlQuery\QueryInterface;

class SqlQuery implements QueryInterface
{
    private $bindValues = [];

    private $statement = '';

    public function __toString()
    {
        return $this->getStatement();
    }

    public function getQuoteNamePrefix()
    {
        // TODO реализовать
    }

    public function getQuoteNameSuffix()
    {
        // TODO реализовать
    }

    public function setStatement($statement)
    {
        $this->statement = $statement;
        return $this;
    }

    public function bindValues(array $bindValues)
    {
        foreach($bindValues as $name => $value) {
            $this->bindValues[$name] = $value;
        }
        return $this;
    }

    public function bindValue($name, $value)
    {
        $this->bindValues[$name] = $value;
        return $this;
    }

    public function getBindValues()
    {
        return $this->bindValues;
    }

    public function getStatement()
    {
        return $this->statement;
    }
}