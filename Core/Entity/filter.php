<?php


namespace Okay\Core\Entity;


use ReflectionClass;

trait filter
{

    /**
     * @var int
     * Количество сущностей в списке по умолчанию
     */
    private $entitiesLimit = 100;

    /**
     * @param array $filter
     * @return void
     * @throws \ReflectionException
     * Чтобы применить кастомный фильтр (напр. $filter['price']),
     * нужно в классе сущности объявить метод filter__price($price) {...}
     * Внутри этого метода работаем с объектом queryBuilder // todo ссылка на пример
     */
    final protected function buildFilter($filter)
    {
        // Сортируем фильтр, в соответствии с приоритетом (какие индексы использовать в первую очередь)
        $filter = $this->orderFilterByPriority($filter);
        $entityClass = new ReflectionClass(static::class);
        foreach ($filter as $filterName => $value) {
            // Применяем кастомные фильтры, если данные не пусты
            if ($value !== [] && $value !== '' && $value !== null) {
                $filterMethod = 'filter__' . $filterName;

                if ($entityClass->hasMethod($filterMethod)) {
                    // Применяем фильтр
                    $this->$filterMethod($value, $filter);

                    // Удаляем фильтр, чтобы он повторно не применился
                    unset($filter[$filterName]);
                } else {
                    $this->autoFilter($filterName, $value);
                }
            }
        }
    }

    protected function buildPagination($filter = [])
    {
        $this->select->setPaging($this->entitiesLimit);

        if (isset($filter['limit'])) {
            $this->select->setPaging(max(1, (int)$filter['limit']));
            if (empty($filter['page'])) {
                $filter['page'] = 1;
            }
        }

        if (isset($filter['page'])) {
            $this->select->page(max(1, (int)$filter['page']));
        }
    }

    /**
     * @param string $filterName
     * @param string|array $value
     * @return void
     * "Магический" фильтр, если передали $filterName и у сущности зарегистрированно такое поле, по нему пройдет фильтрация автоматически
     */
    private function autoFilter($filterName, $value)
    {
        $langFields = $this->getLangFields();
        $fields = $this->getFields();
        $allFields = array_merge($langFields, $fields);
        
        // Если есть фильтр по полю, добавим такой фильтр автоматически
        if (array_search($filterName, $allFields) !== false) {

            $tableAlias = $this->getTableAlias();

            // Если применили фильтр по полю, которое объявлено как мультиленговое, установим соответствующий алиас
            if (in_array($filterName, $langFields)) {
                $tableAlias = $this->lang->getLangAlias(
                    $this->getTableAlias()
                );
            }
            
            if (is_array($value)) {
                $this->select->where("{$tableAlias}.{$filterName} IN (:magic_filter_{$filterName})");
            } else {
                $this->select->where("{$tableAlias}.{$filterName} = :magic_filter_{$filterName}");
            }

            $this->select->bindValue("magic_filter_" . $filterName, $value);
        }
    }
}