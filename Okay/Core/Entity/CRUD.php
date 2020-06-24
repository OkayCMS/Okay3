<?php


namespace Okay\Core\Entity;


use Okay\Core\QueryFactory\Select;
use Okay\Core\Modules\Extender\ExtenderFacade;

trait CRUD
{

    public function findOne(array $filter = [])
    {
        $filter['limit'] = 1;
        
        if (!$results = $this->find($filter)) {
            return false;
        }

        $result = reset($results);
        return ExtenderFacade::execute([static::class, __FUNCTION__], $result, func_get_args());
    }

    /**
     * Данный метод лучше не использовать, он в будущем будет определен как DEPRECATED
     * Вместо него используйте метод findOne()
     * 
     * @param $id
     * @return mixed|void|null
     */
    public function get($id)
    {
        if (empty($id)) {
            $this->flush();
            return ExtenderFacade::execute([static::class, __FUNCTION__], null, func_get_args());
        }
        
        $this->setUp();
        
        if (!is_int($id) && $this->getAlternativeIdField()) {
            $filter[$this->getAlternativeIdField()] = $id;
        } else {
            $filter['id'] = $id;
        }

        $this->buildFilter($filter);
        $this->select->cols($this->getAllFields());
        
        $this->db->query($this->select);

        $result = $this->getResult();
        return ExtenderFacade::execute([static::class, __FUNCTION__], $result, func_get_args());
    }

    /**
     * @param array $filter
     * @return Select
     */
    public function getSelect(array $filter = [])
    {
        $this->setUp();
        $this->buildPagination($filter);
        $this->buildFilter($filter);
        $this->select->distinct(true);
        
        $this->select->cache();
        
        $select = clone $this->select;
        $this->flush();
        return $select; // No ExtenderFacade
    }
    
    public function find(array $filter = [])
    {
        $this->setUp();
        $this->buildPagination($filter);
        $this->buildFilter($filter);
        $this->select->distinct(true);
        $this->select->cols($this->getAllFields());
        $this->select->cache();
        
        $this->db->query($this->select);
        
        // Получаем результирующие поля сущности
        $resultFields = $this->getAllFieldsWithoutAlias();
        $field = null;
        // Если запрашивали одну колонку, отдадим массив строк, а не объектов
        if (count($resultFields) == 1) {
            $field = reset($resultFields);
        }

        $results = $this->getResults($field, $this->mappedBy);
        return ExtenderFacade::execute([static::class, __FUNCTION__], $results, func_get_args());
    }

    public function count(array $filter = [])
    {
        $this->setUp();
        $this->buildFilter($filter);
        $this->select->distinct(true);
        $this->select->cols(["COUNT( DISTINCT " . $this->getTableAlias() . ".id) as count"]);
        
        // Уберем группировку и сортировку при подсчете по умолчанию
        $this->select->resetGroupBy();
        $this->select->resetOrderBy();

        $this->db->query($this->select);

        $count = $this->getResult('count');
        return ExtenderFacade::execute([static::class, __FUNCTION__], $count, func_get_args());
    }

    public function add($object)
    {
        $object = (array)$object;
        unset($object['id']);
        
        $object = (object)$object;

        // Проверяем есть ли мультиязычность и забираем описания для перевода
        $result = $this->getDescription($object);

        $insert = $this->queryFactory->newInsert();

        foreach ($object as $field=>$value) {
            if (strtolower($value) == 'now()') {
                $insert->set($field, $value);
                unset($object->$field);
            }
        }

        // todo добавлять только колонки, которые есть у entity
        $insert->into($this->getTable())
            ->cols((array)$object); // todo здесь нужно сделать через bindValues

        $this->db->query($insert);

        if (!$id = $this->db->insertId()) {
            return ExtenderFacade::execute([static::class, __FUNCTION__], false, func_get_args());
        }

        $update = $this->queryFactory->newUpdate();
        if (in_array('position', $this->getFields())) {
            $update->table($this->getTable())
                ->set('position', $id)
                ->where('id=:id')
                ->bindValue('id', $id);
            $this->db->query($update);
        }
        
        // todo last modify
        // Добавляем мультиязычные данные
        if (!empty($result->description)) {
            $this->actionDescription($id, $result->description);
        }

        return ExtenderFacade::execute([static::class, __FUNCTION__], (int) $id, func_get_args());
    }

    public function update($ids, $object)
    {
        $ids = (array)$ids;
        // todo last modify

        $object = (array)$object;
        unset($object['id']);
        
        $object = (object)$object;
        $update = $this->queryFactory->newUpdate();

        // Проверяем есть ли мультиязычность и забираем описания для перевода
        $result = $this->getDescription($object);

        foreach ($object as $field=>$value) {
            if (is_array($value) || is_object($value)) {
                unset($object->$field);
                continue;
            }

            if (strtolower($value) == 'now()') {
                $update->set($field, $value);
                unset($object->$field);
            }
        }

        $props = get_object_vars($object);
        if (empty($props) && empty($result->description)) {
            return ExtenderFacade::execute([static::class, __FUNCTION__], false, func_get_args());
        }

        // Вдруг обновляют только мультиязычные поля
        if (!empty((array)$object) && !empty($ids)) {
            $update->table($this->getTable() . ' AS ' . $this->getTableAlias())
                ->cols((array)$object)// todo здесь нужно сделать через bindValues
                ->where($this->getTableAlias() . '.id IN (:update_entity_id)');
            $update->bindValue('update_entity_id', $ids);

            $this->db->query($update);
        }

        // Если есть описание для перевода. Указываем язык для обновления
        if (!empty($result->description)) {
            $this->actionDescription($ids, $result->description, $this->lang->getLangId());
        }

        return ExtenderFacade::execute([static::class, __FUNCTION__], true, func_get_args());
    }

    public function delete($ids)
    {
        if (empty($ids)) {
            return ExtenderFacade::execute([static::class, __FUNCTION__], false, func_get_args());
        }
        $ids = (array)$ids;
        
        $delete = $this->queryFactory->newDelete();
        $delete->from($this->getTable())->where('id IN (:ids)');
        $delete->bindValue('ids', $ids);
        $this->db->query($delete);

        if (!empty($this->getLangTable()) && !empty($this->getLangObject())) {
            $delete = $this->queryFactory->newDelete();
            $delete->from($this->getLangTable())->where($this->getLangObject() . '_id IN (:lang_object_ids)');
            $delete->bindValue('lang_object_ids', $ids);
            $this->db->query($delete);
        }

        return ExtenderFacade::execute([static::class, __FUNCTION__], true, func_get_args());
    }
    
    final public function cols(array $cols)
    {
        $this->setSelectFields($cols);
        return $this;
    }

    public function getResult($field = null)
    {
        $results = $this->db->result($field);
        $this->flush();
        return $results;
    }

    public function getResults($field = null, $mapped = null)
    {
        $results = $this->db->results($field, $mapped);
        $this->flush();
        return $results;
    }

    protected function setUp()
    {
        // Подключаем языковую таблицу
        $langQuery = $this->lang->getQuery(
            $this->getTableAlias(),
            $this->getLangTable(),
            $this->getLangObject()
        );

        $this->select->from($this->getTable() . ' AS ' . $this->getTableAlias());
        if (!empty($langQuery['join'])) {
            $this->select->join('LEFT', $langQuery['join'], $langQuery['cond']);
        }
    }
    
}