;<? exit(); ?>

license = 9tsd9gwzfs bcicnizugw ohqmpyqjml lznmnruyui row877flsy qurqbuqavh jevhqhmkuk xwonzrmppn kvvrzztgph ouwmt8sh7h tpcxswzwrt pzruknuqjz jxijjvynun zwwum4yqm4 uin9ybvxv9 rnog8tgwug ifbsanejdj vkxrytkmuw lqruh5r7rk vfqwqvyisn tjvuusak6s ywfrwkamtm qtoszzuuvu his6t4inyg oyqasbqnzt okpntwtyru tutzoyaz7o wy7yjsroi7 gokly5irhl ugoevnorsg umvvywqwro zrtzarfnpw fwavlu8pih ouxfptcys7 yfjqgf

[database]

;Сервер базы данных
db_server = localhost

;Пользователь базы данных
db_user = root

;Пароль к базе
db_password = ""

;Имя базы
db_name = okaycms-git

;Драйвер базы данных
db_driver = mysql

;Префикс для таблиц
db_prefix = ok_

;Кодировка базы данных
db_charset = UTF8

;Режим SQL
db_sql_mode = "STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"

;Смещение часового пояса
;db_timezone = +04:00

[php]
error_reporting = E_ALL
php_charset = UTF8
php_locale_collate = ru_RU
php_locale_ctype = ru_RU
php_locale_monetary = ru_RU
php_locale_numeric = ru_RU
php_locale_time = ru_RU
;php_timezone = Europe/Moscow
debug_mode = false

[smarty]
smarty_compile_check = true
smarty_caching = false
smarty_cache_lifetime = 0
smarty_debugging = false
smarty_html_minify = false
smarty_security = true

[design]
debug_translation = false
scripts_defer = true

[images]
;Указываем какую библиотеку использовать для нарезки изображений. Варианты: Gregwar, Imagick или GD. Это имя класса адаптера
resize_adapter = Gregwar

;Директория общих изображений дизайна (лого, фавикон...)
design_images = files/images/

;Файл изображения с водяным знаком
watermark_file = backend/files/watermark/watermark.png

;Промо изображения
special_images_dir = files/special/

;Директория оригиналов и нарезок фоток товаров
original_images_dir = files/originals/products/
resized_images_dir = files/resized/products/

;Изображения оригиналов и нарезок фоток блога
original_blog_dir = files/originals/blog/
resized_blog_dir = files/resized/blog/

;Изображения оригиналов и нарезок фоток брендов
original_brands_dir = files/originals/brands/
resized_brands_dir = files/resized/brands/

;Изображения оригиналов и нарезок фоток категории
original_categories_dir = files/originals/categories/
resized_categories_dir = files/resized/categories/

;Изображения оригиналов и нарезок фоток доставки
original_deliveries_dir = files/originals/deliveries/
resized_deliveries_dir = files/resized/deliveries/

;Изображения оригиналов и нарезок фоток способов оплаты
original_payments_dir = files/originals/payments/
resized_payments_dir = files/resized/payments/

;Изображения баннеров
banners_images_dir = files/originals/slides/
resized_banners_images_dir = files/resized/slides/

; Папка изображений языков
lang_images_dir = files/originals/lang/
lang_resized_dir = files/resized/lang/

[files]

;Директория хранения цифровых товаров
downloads_dir = files/downloads/
