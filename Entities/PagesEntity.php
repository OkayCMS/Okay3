<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;

class PagesEntity extends Entity
{

    // Системные url
    private $systemPages = [
        '',
        'catalog',
        'products',
        'all-products',
        'discounted',
        'bestsellers',
        'brands',
        'blog',
        'news',
        'wishlist',
        'comparison',
        'cart',
        'order',
        'contact',
        'user',
        '404',
    ];

    protected static $fields = [
        'id',
        'url',
        'visible',
        'position',
        'last_modify',
    ];

    protected static $langFields = [
        'name',
        'name_h1',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'description'
    ];

    protected static $defaultOrderFields = [
        'position ASC',
    ];

    protected static $table = '__pages';
    protected static $langObject = 'page';
    protected static $langTable = 'pages';
    protected static $tableAlias = 'p';
    protected static $alternativeIdField = 'url';
    
    public function getSystemPages()
    {
        return $this->systemPages;
    }

    public function get($id)
    {
        $this->setUp();

        if (!is_int($id) && $this->getAlternativeIdField()) {
            $filter[$this->getAlternativeIdField()] = $id;
        } else {
            $filter['id'] = $id;
        }

        $this->buildFilter($filter);
        $this->select->cols($this->getAllFields());

        $this->db->query($this->select);
        return $this->getResult();
    }
    
    public function delete($ids)
    {
        $ids = (array)$ids;
        $result = false;
        if (!empty($ids)) {
            $result = true;
            foreach ($ids as $id) {
                // Запретим удаление системных ссылок
                $page = $this->get(intval($id));
                if (!in_array($page->url, $this->systemPages)) {
                    parent::delete($id);
                } else {
                    $result = false;
                }
            }
        }
        return $result;
    }

}
