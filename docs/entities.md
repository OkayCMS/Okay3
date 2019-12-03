# Entities

Классы сущностей нужны для управления наборами данных хранящихся в постоянной памяти.
Большинство классов Entities в OkayCMS работают с базой данных, но есть некоторые, которые хранят записи в файловой 
системе.

По умолчанию классы сущностей лежат в Okay/Entities/. Сущности из модулей нужно хранить в 
`Okay/Modules/Vendor/Module/Entities`.

Все классы реализовывают интерфейс `Okay\Core\Entity\EntityInterface`
Каждый класс сущности должен наследоваться от класса `Okay\Core\Entity\Entity`.

В классе `Okay\Core\Entity\Entity` уже есть базовая реализация класса Entity для работы с БД. Для корректной работы
нужно произвести первоначальную настройку.

### Настройка Entity для работы с БД

Для настройки нужно казать некоторые защищенные статические (protected static) свойства.

Обязательные свойства:
* `$table` - string название таблицы, в которой нужно сохранять данные (можно с префиксом `__`, можно без него)
* `$tableAlias` - string алиас для основной таблицы, который стоит использовать в SQL запросах
* `$fields` - array список полей, которые нужно доставать из БД

Не обязательные свойства:
* `$langTable` - string название таблицы, в которой хранятся переводы (без `__lang_`)
* `$langFields` - array список мультиязычных полей, которые нужно доставать из БД
* `$langObject` - string используется для связи с мультиязычными данными (в языковых таблицах blog_id, product_id)
* `$searchFields` - array список полей по которым происходит текстовый поиск (можно указывать и ленговые и нет).
* `$additionalFields` - array список дополнительных полей сущности, с других таблиц или которые как подзапросы идут 
(к ним префикс таблицы не добавляется).
* `$defaultOrderFields` - array список полей по которым происходит сортировка по умолчанию (с указанием направления).
* `$alternativeIdField` - string поле по которому может происходить get() если id передали строкой (url, code etc...)
Предпочтительнее использовать метод findOne(['field' => $value]).

Пример настройки:

```php
namespace Okay\Entities;
use Okay\Core\Entity\Entity;
class SomeEntity extends Entity
{
    protected static $fields = [
        'id',
        'url',
        'visible',
    ];
    
    protected static $langFields = [
        'name',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'annotation',
        'description',
    ];
    
    protected static $searchFields = [
        'name',
        'meta_keywords',
    ];

    protected static $table = 'some_entities';
    protected static $langObject = 'some_entity';
    protected static $langTable = 'some_entities';
    protected static $tableAlias = 's';
}
```

### Фильтрация выборки Entity из БД

Каждый экземпляр класса Entity содержит приватное свойство $select, в котором лежит экземпляр класса 
[Aura\SqlQuery\Common\SelectInterface](https://github.com/auraphp/Aura.SqlQuery/blob/3.x/docs/select.md).

Сброс состояния производится вызовом метода Entity::flush(). По умолчанию состояние сбрасывается автоматически после
вызова методов find(), count(), get() ect. Сбрасывать его вручную может потребоваться в каких-то особых случаях.

#### "Магические" фильтры

Методы find(), count() etc принимают ассоциативный массив данных, по которым нужно фильтровать, где ключ массива это 
название фильтра. "Магические" фильтры работают в случае если передали фильтр с названием как название колонки,
и при этом данный фильтр не переопределён. Эти фильтры также строят разные запросы, в случае если передали строку,
или другое единичное значение, и если передали массив значений.

Например:
```php
namespace Okay\Entities;
use Okay\Core\Entity\Entity;
class SomeEntity extends Entity
{
    protected static $fields = [
        'id',
        'url',
    ];
    
    protected static $langFields = [
        'name',
    ];

    // ...abstract 
}
```

Вызов с единичными значениями:
```php
$someEntity->find([
    'url' => 'some/url',
]);

$someEntity->find([
    'name' => 'name of entity item',
]);
```

построит запросы `SELECT ... WHERE entity_table.url = 'some/url'` и `SELECT ... WHERE lang_entity_table.name = 'name of entity item'`.

Вызов с множеством значений:
```php
$someEntity->find([
    'id' => [1, 2, 3, 4, 5],
]);
```

построит запрос `SELECT ... WHERE entity_table.id IN (1,2,3,4,5)`.

#### Пользовательские фильтры

Если поведение "магических" фильтров не устраивает, или его нужно по какой-то причине отменить вообще, или вы фильруете
не по полю, а скажем по таблице связей, нужно объявить свой пользовательский фильтр в вашем классе Entity.

Это должен быть защищенный (protected) метод, название которого состоит из ключевого слова `filter__` (обратите
внимане на два символа подчёркивания) и самого названия фильтра (он же будет ключем массива фильра при вызове find(), 
count() ...). Внутри этого метода мы работаем с объёктом 
[QueryBuilder](https://github.com/auraphp/Aura.SqlQuery/blob/3.x/docs/select.md), который лежит в свойстве $select.
Метод может принимать два аргумента, первым будет значение, которое предали в этот фильтр при вызове find(), count(), 
вторым будет полностью весь массив $filter (который не обязательно принимать).

Пример вызова:
```php
$someEntity->find([
    'url' => 'some/url',
    'field' => 'value',
]);
```

Пример пользовательского фильтра:
```php
namespace Okay\Entities;
use Okay\Core\Entity\Entity;
use Aura\SqlQuery\Common\Select;
class SomeEntity extends Entity
{
    /** @var Select */
    protected $select;
    protected static $tableAlias = 'e';

    // ...abstract 

    protected function filter__field($val, $filter)
    {
        // $val = 'value';
        // $filter = [
        //               'url' => 'some/url',
        //               'field' => 'value',
        //           ];
        
        $this->select->join('inner', '__second_table AS st', 'e.id = st.entity_id AND st.field=:value')
            ->bindValue('value', $val);
        
        $this->select->groupBy(['e.id']);
    }
}
```


