**Задача:** не выводить в список новостей и записей блога статьи, которые были опубликованы не более 180 дней назад.
Но по прямой ссылке запись должна быть доступна.

**Решение:** 
из контроллера блога передавать фильтр $filter['new_only'] = TRUE, а в классе Okay\Entities\BlogEntity 
его применять.

Изменения вносились в классы:
 - Okay\Controllers\BlogController
 - Okay\Entities\BlogEntity
 - Okay\Core\SmartyPlugins\Plugins\GetPosts