<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;

class CurrenciesEntity extends Entity {
    
    protected static $fields = [
        'id',
        'code',
        'rate_from',
        'rate_to',
        'cents',
        'position',
        'enabled',
    ];

    protected static $langFields = [
        'name',
        'sign',
    ];

    protected static $defaultOrderFields = [
        'position',
    ];

    protected static $table = '__currencies';
    protected static $langObject = 'currency';
    protected static $langTable = 'currencies';
    protected static $tableAlias = 'c';
    protected static $alternativeIdField = 'code';

    private $mainCurrency;
    
    public function getMainCurrency()
    {
        if (empty($this->mainCurrency)) {
            if ($currencies = $this->find()) {
                $this->mainCurrency = reset($currencies);
            } else {
                return false;
            }
        }
        
        return $this->mainCurrency;
    }
    
    /*public function get($id)
    {
        $filter['limit'] = 1;

        if (is_int($id)) {
            $filter['id'] = $id;
        } else {
            $filter['code'] = $id;
        }

        $this->buildFilter($filter);
        $this->select->cols($this->getAllFields());

        $this->db->query($this->select);
        // Почистим после себя состояние
        $this->flush();
        return $this->db->result();
    }*/

}
