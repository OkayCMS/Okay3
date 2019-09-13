# Yandex.Checkout API PHP Client Library

Russian | [English](https://github.com/yandex-money/yandex-checkout-sdk-php/blob/master/README.en.md)

Клиент для работы с платежами по [API Яндекс.Кассы](https://kassa.yandex.ru/docs/checkout-api/)
Подходит тем, у кого способ подключения к Яндекс.Кассе называется API.

## Требования
PHP 5.3.2 (и выше) с расширением libcurl

## Установка
### В консоли с помощью Composer

1. Установите менеджер пакетов Composer.
2. В консоли выполните команду
```bash
composer require yandex-money/yandex-checkout-sdk-php
```

### В файле composer.json своего проекта
1. Добавьте строку `"yandex-money/yandex-checkout-sdk-php": "*"` в список зависимостей вашего проекта в файле composer.json
```
...
    "require": {
        "php": ">=5.3.2",
        "yandex-money/yandex-checkout-sdk-php": "*"
...
```
2. Обновите зависимости проекта. В консоли перейдите в каталог, где лежит composer.json, и выполните команду:
```bash
composer update
```

### Вручную

1. Скачайте [архив Yandex.Checkout API PHP Client Library](https://github.com/yandex-money/yandex-checkout-sdk-php/archive/master.zip), распакуйте его и скопируйте каталог lib в нужное место в вашем проекте.
2. В коде вашего проекта подключите автозагрузку файлов нашего клиента:
```php
require __DIR__ . '/lib/autoload.php'; 
```

## Начало работы

1. Подключите зависимости
```php
require __DIR__ . '/vendor/autoload.php';
```
2. Импортируйте нужные классы
```php
use YandexCheckout\Client;
```
3. Создайте экземпляр объекта клиента и задайте идентификатор магазина и секретный ключ (их можно получить в личном кабинете Яндекс.Кассы). [Как выпустить секретный ключ](https://yandex.ru/support/checkout/payments/keys.html)
```php
$client = new Client();
$client->setAuth('shopId', 'secretKey');
```
4. Вызовите нужный метод API. [Подробнее в документации к API Яндекс.Кассы](https://kassa.yandex.ru/docs/checkout-api/)
