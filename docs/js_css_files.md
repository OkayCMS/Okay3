# Подключение JS и CSS файлов

В OkayCMS JS и CSS файлы не подключаются напрямую через тег `<script></script>` или `<link />`, их нужно регистрировать.
Все зарегистрированные файлы собираются в несколько (зависит от параметров) общих, которые минифицируются, и 
подключаются в шаблон.
Регистрация JavaScript происходит в файле `design/<theme name>/js.php`, CSS соответственно в 
`design/<theme name>/css.php`.

Для подключения JS файлов, нужно создать файл `design/<theme name>/js.php`, который возвращает массив объектов
[Okay\Core\TemplateConfig\Js](#TemplateConfigJS). Или файл `design/<theme name>/css.php` с массивом 
[Okay\Core\TemplateConfig\CSS](#TemplateConfigCSS) соответственно.

Из модуля также эти файлы можно подключать, расположив регистрационные файлы в директории 
`Okay/Modules/Vendor/Module/design/`.


<a name="commonScript"></a>
#### Общее описание классов Okay\Core\TemplateConfig\JS и Okay\Core\TemplateConfig\CSS

Класс в конструктор принимает название файла, который нужно зарегистрировать (без пути).
Если путь не указать, это имеется в виду, что файл лежит в `design/<theme name>/js/` или `design/<theme name>/css/`.
В случае если подключается файл из модуля, имеется в виду директория 
`Okay/Modules/Vendor/Module/design/js/` или `Okay/Modules/Vendor/Module/design/css/`.
По умолчанию все зарегистрированные скрипты выводятся в одном общем файле в head шаблона.
Оба класса (`Okay\Core\TemplateConfig\JS` и `Okay\Core\TemplateConfig\CSS`) имеют общую реализацию
(в `Okay\Core\TemplateConfig\Common`) следующих методов:


<a name="setDir"></a>
```php
setDir( string $dir)
```

Установка директории скрипта.
Если скрипт находится в теме (директория js или css соответственно), директорию можно не указывать.

Аргумент | Описание
---|---
$dir | Путь к директории скрипта, относительно корня сайта.


<a name="setPosition"></a>
```php
setPosition( string $position)
```

Установка позиции, где нужно выводить скрипт (head/footer)

Аргумент | Описание
---|---
$position | Позиция скрипта (head/footer).


<a name="setIndividual"></a>
```php
setIndividual( bool $individual)
```

Установка флага что файл должен подключиться индивидуально, не в общем скомпилированном файле

Аргумент | Описание
---|---
$individual | true - подключаем индивидуально, false - файл будет подключен в общем скомпилированном файле.


<a name="TemplateConfigCSS"></a>
#### Класс Okay\Core\TemplateConfig\CSS

Класс `Okay\Core\TemplateConfig\CSS` не имеет индивидуальной реализации, содержит только 
[общие методы](#commonScript).

Пример регистрации:
```php
use Okay\Core\TemplateConfig\Css;

return [
    (new Css('font.css')),
    (new Css('font-awesome.min.css'))->setPosition('footer'),
    (new Css('grid.css'))->setDir('/custom_js/')->setIndividual(true),
];
```


<a name="TemplateConfigJS"></a>
#### Класс Okay\Core\TemplateConfig\JS

Класс `Okay\Core\TemplateConfig\JS` имеет индивидуальную реализацию, следующего метода, в остальном он соответствует 
[общей реализации](#commonScript).

<a name="setDefer"></a>
```php
setDefer( string $defer)
```

Установка JavaScript файлу флага defer. Флаг defer будет добавлен в случае [individual](#setIndividual) = true

Аргумент | Описание
---|---
$defer | Путь к директории скрипта, относительно корня сайта.

Пример регистрации:
```php
use Okay\Core\TemplateConfig\Js;

return [
    (new Js('jquery-3.4.1.min.js')),
    (new Js('owl.carousel.min.js'))->setIndividual(true)->setDefer(true),
    (new Js('select2.min.js'))->setPosition('footer'),
];
```

