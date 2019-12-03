# Dependency injection container

Контейнер реализовывает интерфейс [Psr\Container\ContainerInterface](https://www.php-fig.org/psr/psr-11/).
Описание всех сервисов и их зависимостей описано в файлах:
+ Okay/Core/config/services.php ([основные сервисы ядра](./core.md))
+ Okay/Core/config/requests.php ([сервисы Requests](./requests.md))
+ Okay/Core/config/helpers.php ([сервисы хелперов](./helpers.md))

Не допускается использования цекличиских зависимостей.

### Регистрация сервиса <a name="serviceRegister"></a>
Чтобы зарегистрировать сервис, нужно в одном из файлов описания сервисов добавить его.

Рассмотрим пример регистрации сервиса ядра. Регистрировать его нужно файле Okay/Core/config/services.php

(todo версия для модулей).

Файл services.php возвращает массив с описанием сервисов, где ключ, это название сервиса, значение - описание сервиса.

`Best practices: в качестве имени сервиса держать полное имя класса`

Пример:
```php
use Okay\Core\OkayContainer\Reference\ParameterReference as PR;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;

[
    MyClass::class => [ // Имя сервиса
        'class' => MyClass::class, // Имя класса, из которого создавать экземпляр сервиса
        'arguments' => [ // Аргументы конструктора класса MyClass. Принимать в порядке, как здесь передаём
            new SR(OtherClass::class),
            new PR('foo.bar'),
        ],
    ],
];
```
Описание классов [ParameterReference](#ParameterReference) и [ServiceReference](#ServiceReference)


#### Инъекция зависимости

Чтобы получить для класса сервиса как зависимость, другой класс сервиса, нужно...

### Получение сервиса
Чтобы получить зависимость в [классе контроллера](./controllers.md) нужно аргумент метода контроллера принять
переменную с указанием Type hint. И 

#### <a name="ParameterReference"></a> ParameterReference
Описание класса ParameterReference
#### <a name="ServiceReference"></a> ServiceReference
Описание класса ServiceReference