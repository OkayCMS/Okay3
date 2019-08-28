<?php
/**
 * Метод register_css принимает четыре параметра
 * register_css( string $filename [, string $position = 'head' [, string $dir = null [, $individual = false]]])
 * $filename   - имя файла
 * $position   - позиция файла (head|footer)
 * $dir        - папка с css файлом, относительно корня сайта
 * $individual - скомпилированный файл должен должен отдельно подключаться или нет
 * 
 * register_js() все тоже самое что и register_css(), но добавлен еще один параметр $defer = false
 * если установить в true совместно с $individual = true скрипту добавиться атрибут defer
 */

$this->templateConfig->register_css('okay.css');
$this->templateConfig->register_css('font-awesome.min.css');

$this->templateConfig->register_css('libs.css');
$this->templateConfig->register_css('media.css');
$this->templateConfig->register_css('grid.css');
$this->templateConfig->register_css('mobile_menu.css');

$this->templateConfig->register_js('jquery-3.3.1.min.js');
$this->templateConfig->register_js('owl.carousel.min.js');
$this->templateConfig->register_js('select2.min.js', 'footer');
$this->templateConfig->register_js('jquery.matchHeight-min.js');
$this->templateConfig->register_js('okay.js', 'footer');
$this->templateConfig->register_js('jquery-ui.min.js', 'footer');
$this->templateConfig->register_js('ui.touch-punch.min.js', 'footer');
$this->templateConfig->register_js('jquery.fancybox.min.js', 'footer');
$this->templateConfig->register_js('lazyload.min.js', 'footer');
$this->templateConfig->register_js('xzoom.min.js', 'footer');
$this->templateConfig->register_js('jquery.hammer.min.js', 'footer');
$this->templateConfig->register_js('select2.min.js', 'footer');
$this->templateConfig->register_js('readmore.min.js', 'footer');
$this->templateConfig->register_js('mobile_menu.js', 'footer');
$this->templateConfig->register_js('sticky.min.js', 'footer');
$this->templateConfig->register_js('jquery.autocomplete-min.js', 'footer');
$this->templateConfig->register_js('jquery.validate.min.js', 'footer');
$this->templateConfig->register_js('additional-methods.min.js', 'footer');
