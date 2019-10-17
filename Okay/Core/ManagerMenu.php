<?php


namespace Okay\Core;


use Okay\Core\Modules\Module;

class ManagerMenu
{
    
    /**
     * Массив системных контроллеров, которые в меню не выводятся, но на них отдельные разрешения
     *
     * @var array
     */
    private $systemControllers = [
        'left_support'       => ['SupportAdmin', 'TopicAdmin'],
        'left_license_title' => ['LicenseAdmin'],
    ];
    
    /**
     * Массив с меню админ. части (из него автоматически формируется главное меню админки)
     *
     * @var array
     */
    private $leftMenu = [
        'left_catalog' => [
            'left_products_title'        => ['ProductsAdmin', 'ProductAdmin'],
            'left_categories_title'      => ['CategoriesAdmin', 'CategoryAdmin'],
            'left_brands_title'          => ['BrandsAdmin', 'BrandAdmin'],
            'left_features_title'        => ['FeaturesAdmin', 'FeatureAdmin'],
        ],
        'left_orders' => [
            'left_orders_title'          => ['OrdersAdmin', 'OrderAdmin'],
            'left_orders_settings_title' => ['OrderSettingsAdmin'],
        ],
        'left_users' => [
            'left_users_title'           => ['UsersAdmin', 'UserAdmin'],
            'left_groups_title'          => ['UserGroupsAdmin', 'UserGroupAdmin'],
            'left_coupons_title'         => ['CouponsAdmin'],
            'left_subscribe_title'       => ['SubscribeMailingAdmin'],
        ],
        'left_pages' => [
            'left_pages_title'           => ['PagesAdmin', 'PageAdmin'],
            'left_menus_title'           => ['MenusAdmin', 'MenuAdmin'],
        ],
        'left_blog' => [
            'left_blog_title'            => ['BlogAdmin', 'PostAdmin'],
        ],
        'left_comments' => [
            'left_comments_title'        => ['CommentsAdmin'],
            'left_feedbacks_title'       => ['FeedbacksAdmin'],
            'left_callbacks_title'       => ['CallbacksAdmin'],
        ],
        'left_auto' => [
            'left_import_title'          => ['ImportAdmin'],
            'left_export_title'          => ['ExportAdmin'],
            'left_log_title'             => ['ImportLogAdmin'],
        ],
        'left_stats' => [
            'left_stats_title'           => ['StatsAdmin'],
            'left_products_stat_title'   => ['ReportStatsAdmin'],
            'left_categories_stat_title' => ['CategoryStatsAdmin'],
        ],
        'left_seo' => [
            'left_robots_title'          => ['RobotsAdmin'],
            'left_setting_counter_title' => ['SettingsCounterAdmin'],
            'left_seo_patterns_title'    => ['SeoPatternsAdmin'],
            'left_seo_filter_patterns_title' => ['SeoFilterPatternsAdmin'],
            'left_feature_aliases_title'     => ['FeaturesAliasesAdmin'],
        ],
        'left_design' => [
            'left_theme_title'           => ['ThemeAdmin'],
            'left_template_title'        => ['TemplatesAdmin'],
            'left_style_title'           => ['StylesAdmin'],
            'left_script_title'          => ['ScriptsAdmin'],
            'left_images_title'          => ['ImagesAdmin'],
            'left_translations_title'    => ['TranslationsAdmin', 'TranslationAdmin'],
            'left_settings_theme_title'  => ['SettingsThemeAdmin'],
        ],
        'left_settings' => [
            'left_setting_general_title' => ['SettingsGeneralAdmin'],
            'left_setting_notify_title'  => ['SettingsNotifyAdmin'],
            'left_setting_catalog_title' => ['SettingsCatalogAdmin'],
            'left_currency_title'        => ['CurrencyAdmin'],
            'left_delivery_title'        => ['DeliveriesAdmin', 'DeliveryAdmin'],
            'left_payment_title'         => ['PaymentMethodsAdmin', 'PaymentMethodAdmin'],
            'left_managers_title'        => ['ManagersAdmin', 'ManagerAdmin'],
            'left_languages_title'       => ['LanguagesAdmin', 'LanguageAdmin'],
            'learning_title'             => ['LearningAdmin'],
            'left_system_title'          => ['SystemAdmin']
        ],
        'left_modules' => [
            'left_modules_list'          => ['ModulesAdmin'],
        ],
    ];

    /**
     * Ссылки на изображения для дополнительных секцый меню. Представляют из себя ассоциативный массив с именем
     * секции в качестве ключа и путем к картинке относительно корня проекта
     *
     * @var array
     */
    private $additionalSectionIcons = [];

    /**
     * Список контроллеров, которые имеют собственную вкладку в меню
     *
     * @var array
     */
    private $modulesСontrollersHasOwnMenuItem = [];

    private $managers;
    private $module;
    
    public function __construct(Managers $managers, Module $module)
    {
        $this->managers = $managers;
        $this->module   = $module;
    }

    /**
     * Добавить новый контроллера в меню. Чтобы зайдя на этот модуль "Модули" отображался как активный пункт меню
     *
     * @param $vendorModuleController
     * @throws \Exception
     */
    public function addCommonModuleController($vendorModuleController)
    {
        if (in_array($vendorModuleController, $this->modulesСontrollersHasOwnMenuItem)) {
            return;
        }

        if ($this->module->getBackendControllerParams($vendorModuleController)
            && !in_array($vendorModuleController, $this->leftMenu['left_modules']['left_modules_list'])) {
            $this->leftMenu['left_modules']['left_modules_list'][] = $vendorModuleController;
        }
    }

    /**
     * Получить основное меню админ панели с учетом индивидуальной сортировки менеждера и прав доступа вышеупомянутого менеджера
     *
     * @param $manager
     * @return array
     */
    public function getMenu($manager)
    {
        $controllersPermissions = $this->managers->getControllersPermissions();

        foreach ($this->leftMenu as $section => $items) {
            if (!isset($manager->menu[$section])) {
                $manager->menu[$section] = $this->prepareItemsForManagerMenu($items);
            }

            foreach ($items as $title => $controllers) {
                $mainController = reset($controllers);

                if (!isset($manager->menu[$section][$title])) {
                    $manager->menu[$section][$title] = $mainController;
                }

                if (!isset($controllersPermissions[$mainController])) {
                    continue;
                }

                if ($this->managers->hasPermission($manager, $mainController)) {
                    $manager->menu[$section][$title] = $mainController;
                    continue;
                }

                unset($this->leftMenu[$section][$title]);
                if (isset($manager->menu[$section][$title])) {
                    unset($manager->menu[$section][$title]);
                }
            }

            if (empty($section)) {
                unset($this->leftMenu[$section]);

                if (isset($manager->menu[$section])) {
                    unset($manager->menu[$section]);
                }
            }
        }

        foreach($manager->menu as $section => $items) {
            if (!isset($this->leftMenu[$section])) {
                unset($manager->menu[$section]);
                continue;
            }

            foreach($items as $title => $controllers) {
                if (!isset($this->leftMenu[$section][$title])) {
                    unset($manager->menu[$section][$title]);
                }
            }
        }

        return $manager->menu;
    }

    public function removeMenuItem($section, $title)
    {
        if (isset($this->leftMenu[$section][$title])) {
            unset($this->leftMenu[$section][$title]);
        }
    }

    private function prepareItemsForManagerMenu($section)
    {
        $preparedItems = [];
        foreach($section as $title => $controllers) {
            $preparedItems[$title] = reset($controllers);
        }

        return $preparedItems;
    }

    public function extendMenu($section, array $menuItemsByControllers, $icon)
    {
        foreach($menuItemsByControllers as $itemName => $controllers) {
            if (is_string($controllers)) {
                $controllers = [$controllers];
            }

            if (!empty($this->leftMenu[$section][$itemName])) {
                throw new \Exception("Menu item by path {$section} -> {$itemName} already in use");
            }

            $this->leftMenu[$section][$itemName] = $controllers;
            $this->modulesСontrollersHasOwnMenuItem = array_merge($this->modulesСontrollersHasOwnMenuItem, $controllers);

            if (empty($icon)) {
                continue;
            }

            $iconObject = ['data' => $icon];
            if (is_file($icon)) {
                $iconObject['type'] = 'file';
            } else {
                $iconObject['type'] = 'text';
            }

            $this->additionalSectionIcons[$section] = $iconObject;
        }
    }

    public function getAdditionalSectionItems()
    {
        return $this->additionalSectionIcons;
    }
    
    public function getActiveControllerName($manager, $controller)
    {
        $controllersPermissions = $this->managers->getControllersPermissions();
        $activeControllerName = null;
        // Если не запросили модуль - используем модуль первый из разрешенных
        if (empty($controller)
            || (!is_file('backend/Controllers/'.$controller.'.php') && !$this->module->getBackendControllerParams($controller))) {
            foreach ($this->getMenu($manager) as $section => $items) {
                foreach ($items as $title => $controllers) {
                    if ($this->managers->access($controllersPermissions[$controllers], $manager)) {
                        //$controller = $controllers;
                        $activeControllerName = $title;
                        break 2;
                    }
                }
            }
            unset($controllers);
        } else {
            foreach ($this->leftMenu as $section => $items) {
                foreach ($items as $title => $controllers) {
                    if (in_array($controller, $controllers)) {
                        $activeControllerName = $title;
                        break 2;
                    }
                }
            }
        }
        
        return $activeControllerName;
    }

    public function getPermissionMenu($btr = null)
    {
        $permissionMenu = [];

        $menu = $this->leftMenu;
        foreach($menu as $blockName => $items) {
            $permissionMenu[$blockName] = $this->groupPermissionByBlockMenu($items);
        }
        
        $permissionMenu['left_system_controllers'] = $this->groupPermissionByBlockMenu($this->systemControllers);
        
        if (is_null($btr)) {
            return $permissionMenu;
        }

        $permissionMenu = $this->replaceTranslations($btr, $permissionMenu);
        
        // Разрешения для модулей добавляем без переводов, в качестве имени идёт Vendor/Module
        foreach ($this->managers->getModulesPermissions() as $permission=>$vendorModuleName) {
            $permissionMenu['left_modules'][$permission] = $vendorModuleName;
        }
        
        return $permissionMenu;
    }

    private function replaceTranslations($btr, $permissionMenu)
    {
        foreach($permissionMenu as $blockName => $blockItems) {
            foreach($blockItems as $permission => $title) {
                $permissionMenu[$blockName][$permission] = $btr->$title;
            }
        }

        return $permissionMenu;
    }

    private function groupPermissionByBlockMenu($blockMenu)
    {
        $permissionBlockMenu = [];

        foreach($blockMenu as $itemName => $controllers) {
            $permissionBlockMenu[$itemName] = $this->managers->getPermissionByController($controllers[0]);
        }

        return array_flip($permissionBlockMenu);
    }
}
