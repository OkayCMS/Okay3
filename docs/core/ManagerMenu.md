# Класс Okay\Core\ManagerMenu

<a name="addCounter"></a>
```php
addCounter( string $menuItemTitle, int $counter)
```

Добавления счетчика новых событий в 
[админ-меню](./../dev_mode.md#backendMenu)

Аргумент | Описание
---|---
$menuItemTitle | [Название пункта меню](./../dev_mode.md#backendMenu), в который стоит добавить счётчик событий. К группе меню счётчик добавляется автоматически.
$counter | Количество новых событий, которое нужно вывести в меню.

Для добавления счетчика, следует создать [экстендер](./../modules/extenders.md), который расширит [хелпер](./../helpers.md) 
`Okay\Admin\Helpers\BackendMainHelper::evensCounters()`.

Пример экстендера:
```php
class BackendExtender implements ExtensionInterface
{
    private $managerMenu;
    private $entityFactory;
    
    public function __construct(ManagerMenu $managerMenu, EntityFactory $entityFactory)
    {
        $this->managerMenu = $managerMenu;
        $this->entityFactory = $entityFactory;
    }

    public function setNewEventsProcedure()
    {
        /** @var SomeEntity $someEntity */
        $someEntity = $this->entityFactory->get(SomeEntity::class);
        $this->managerMenu->addCounter('left_custom_form_data_title', $someEntity->count(['processed' => 0]));
    }
}
```

Пример инициализации:
```php
class Init extends AbstractInit
{
    public function init()
    {
        // ...abstract
        $this->registerChainExtension(
            ['class' => BackendMainHelper::class, 'method' => 'evensCounters'],
            ['class' => BackendExtender::class, 'method' => 'setNewEventsProcedure']
        );
    }
}
```
