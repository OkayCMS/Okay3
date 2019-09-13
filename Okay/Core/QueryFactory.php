<?php


namespace Okay\Core;


use Aura\SqlQuery\QueryFactory as AuraQueryFactory;
use Okay\Core\QueryFactory\SqlQuery;

class QueryFactory
{
    private $auraQueryFactory;

    public function __construct(AuraQueryFactory $auraQueryFactory)
    {
        $this->auraQueryFactory = $auraQueryFactory;
    }

    public function newSelect()
    {
        return $this->auraQueryFactory->newSelect();
    }

    public function newUpdate()
    {
        return $this->auraQueryFactory->newUpdate();
    }

    public function newInsert()
    {
        return $this->auraQueryFactory->newInsert();
    }

    public function newDelete()
    {
        return $this->auraQueryFactory->newDelete();
    }

    public function newSqlQuery()
    {
        return new SqlQuery();
    }
}