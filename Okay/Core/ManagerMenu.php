<?php


namespace Okay\Core;


use Okay\Core\Modules\Module;

class ManagerMenu
{
    
    // Массив системных контроллеров, которые в меню не выводятся, но на них отдельные разрешения
    private $systemControllers = [
            'left_support'          => ['SupportAdmin', 'TopicAdmin'],
            'left_license_title'    => ['LicenseAdmin'],
        ];
    
    /*Массив с меню админ. части (из него автоматически формируется главное меню админки)*/
    private $leftMenu = [
        'left_catalog' => [
            'left_products_title'       => ['ProductsAdmin', 'ProductAdmin'],
            'left_categories_title'     => ['CategoriesAdmin', 'CategoryAdmin'],
            'left_brands_title'         => ['BrandsAdmin', 'BrandAdmin'],
            'left_features_title'       => ['FeaturesAdmin', 'FeatureAdmin'],
        ],
        'left_orders' => [
            'left_orders_title'         => ['OrdersAdmin', 'OrderAdmin'],
            'left_orders_settings_title'=> ['OrderSettingsAdmin'],
        ],
        'left_users' => [
            'left_users_title'          => ['UsersAdmin', 'UserAdmin'],
            'left_groups_title'         => ['UserGroupsAdmin', 'UserGroupAdmin'],
            'left_coupons_title'        => ['CouponsAdmin'],
            'left_subscribe_title'      => ['SubscribeMailingAdmin'],
        ],
        'left_pages' => [
            'left_pages_title'          => ['PagesAdmin', 'PageAdmin'],
            'left_menus_title'          => ['MenusAdmin', 'MenuAdmin'],
        ],
        'left_blog' => [
            'left_blog_title'           => ['BlogAdmin', 'PostAdmin'],
        ],
        'left_comments' => [
            'left_comments_title'       => ['CommentsAdmin'],
            'left_feedbacks_title'      => ['FeedbacksAdmin'],
            'left_callbacks_title'      => ['CallbacksAdmin'],
        ],
        'left_auto' => [
            'left_import_title'         => ['ImportAdmin'],
            'left_export_title'         => ['ExportAdmin'],
            'left_log_title'            => ['ImportLogAdmin'],
        ],
        'left_stats' => [
            'left_stats_title'          => ['StatsAdmin'],
            'left_products_stat_title'  => ['ReportStatsAdmin'],
            'left_categories_stat_title'=> ['CategoryStatsAdmin'],
        ],
        'left_seo' => [
            'left_robots_title'         => ['RobotsAdmin'],
            'left_setting_counter_title'=> ['SettingsCounterAdmin'],
            'left_seo_patterns_title'   => ['SeoPatternsAdmin'],
            'left_seo_filter_patterns_title'   => ['SeoFilterPatternsAdmin'],
            'left_feature_aliases_title'       => ['FeaturesAliasesAdmin'],
        ],
        'left_design' => [
            'left_theme_title'          => ['ThemeAdmin'],
            'left_template_title'       => ['TemplatesAdmin'],
            'left_style_title'          => ['StylesAdmin'],
            'left_script_title'         => ['ScriptsAdmin'],
            'left_images_title'         => ['ImagesAdmin'],
            'left_translations_title'   => ['TranslationsAdmin', 'TranslationAdmin'],
            'left_settings_theme_title' => ['SettingsThemeAdmin'],
        ],
        'left_banners' => [
            'left_banners_title'        => ['BannersAdmin', 'BannerAdmin'],
            'left_banners_images_title' => ['BannersImagesAdmin', 'BannersImageAdmin'],
        ],
        'left_settings' => [
            'left_setting_general_title'=> ['SettingsGeneralAdmin'],
            'left_setting_notify_title' => ['SettingsNotifyAdmin'],
            'left_setting_catalog_title'=> ['SettingsCatalogAdmin'],
            'left_currency_title'       => ['CurrencyAdmin'],
            'left_delivery_title'       => ['DeliveriesAdmin', 'DeliveryAdmin'],
            'left_payment_title'        => ['PaymentMethodsAdmin', 'PaymentMethodAdmin'],
            'left_managers_title'       => ['ManagersAdmin', 'ManagerAdmin'],
            'left_languages_title'      => ['LanguagesAdmin', 'LanguageAdmin'],
            'left_system_title'         => ['SystemAdmin']
        ],
        'left_modules' => [
            'left_modules_list'         => ['ModulesAdmin'],
        ],

    ];

    private $managers;
    private $module;
    
    public function __construct(Managers $managers, Module $module)
    {
        $this->managers = $managers;
        $this->module = $module;
    }

    /**
     * Добавить новый контроллера в меню. Чтобы зайдя на этот модуль "Модули" отображался как активный пункт меню
     * @param $vendorModuleController
     * @throws \Exception
     */
    public function addCommonModuleController($vendorModuleController)
    {
        if ($this->module->getBackendControllerParams($vendorModuleController) 
            && !in_array($vendorModuleController, $this->leftMenu['left_modules']['left_modules_list'])) {
            $this->leftMenu['left_modules']['left_modules_list'][] = $vendorModuleController;
        }
    }
    
    public function getMenu($manager)
    {
        $menu = $manager->menu;
        $controllersPermissions = $this->managers->getControllersPermissions();
        foreach ($this->leftMenu as $section => $items) {
            foreach ($items as $title => $controllers) {
                $controllers = reset($controllers);//$controllers = ($l->valid === true ? reset($controllers) : 'LicenseAdmin'); // todo check license

                if (in_array($controllersPermissions[$controllers], $manager->permissions)) {
                    $menu[$section][$title] = $controllers;
                    continue;
                }

                unset($this->leftMenu[$section][$title]);
                if (isset($menu[$section][$title])) {
                    unset($menu[$section][$title]);
                }
            }
            if (count($this->leftMenu[$section]) == 0) {
                unset($this->leftMenu[$section]);
            }
            if (isset($menu[$section]) && count($menu[$section]) == 0) {
                unset($menu[$section]);
            }
            unset($controllers);
        }
        unset($items);
        return $menu;
    }
    
    private function getActiveControllerClass($manager, $controller) // todo пока приватный, он возможно не нужен будет
    {
        $controllersPermissions = $this->managers->getControllersPermissions();

        if (empty($controller) || !is_file('backend/Controllers/'.$controller.'.php')) {
            foreach ($this->getMenu($manager) as $section => $items) {
                foreach ($items as $title => $controllers) {
                    if ($this->managers->access($controllersPermissions[$controllers], $manager)) {
                        $controller = $controllers;
                        break 2;
                    }
                }
            }
            unset($controllers);
        }

        return $controller;
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
