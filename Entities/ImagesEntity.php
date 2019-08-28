<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;



class ImagesEntity extends Entity
{
    protected static $fields = [
        'id',
        'name',
        'product_id',
        'filename',
        'position'
    ];

    protected static $langFields = [];

    protected static $searchFields = [];

    protected static $defaultOrderFields = [
        'product_id',
        'position',
    ];

    protected static $table = '__images';
    protected static $langObject = 'image';
    protected static $tableAlias = 'i';    
}