<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;

class CallbacksEntity extends Entity
{

    protected static $fields = [
        'id',
        'name',
        'phone',
        'message',
        'date',
        'processed',
        'url',
        'admin_notes',
    ];

    protected static $defaultOrderFields = [
        'date DESC',
    ];

    protected static $searchFields = [
        'name',
        'message',
        'phone',
    ];

    protected static $table = '__callbacks';
    protected static $tableAlias = 'c';
    
    public function add($callback)
    {
        $callback = (object)$callback;
        $callback->date = 'now()';
        return parent::add($callback);
    }
    
}
