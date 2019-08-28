<?php


namespace Okay\Core;


class ManagerMenu
{
    
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
            'left_setting_feed_title'   => ['SettingsFeedAdmin'],
            'left_currency_title'       => ['CurrencyAdmin'],
            'left_delivery_title'       => ['DeliveriesAdmin', 'DeliveryAdmin'],
            'left_payment_title'        => ['PaymentMethodsAdmin', 'PaymentMethodAdmin'],
            'left_managers_title'       => ['ManagersAdmin', 'ManagerAdmin'],
            'left_languages_title'      => ['LanguagesAdmin', 'LanguageAdmin'],
            'left_system_title'         => ['SystemAdmin']
        ],
    ];

    private $managers;
    
    public function __construct(Managers $managers)
    {
        $this->managers = $managers;
    }

    public function getMenu($manager)
    {
        $menu = $manager->menu;
        $modulesPermissions = $this->managers->getModulesPermissions();
        foreach ($this->leftMenu as $section => $items) {
            foreach ($items as $title => $modules) {
                $modules = reset($modules);//$modules = ($l->valid === true ? reset($modules) : 'LicenseAdmin'); // todo check license
                if (!in_array($modulesPermissions[$modules], $manager->permissions)) {
                    unset($this->leftMenu[$section][$title]);
                    if (isset($menu[$section][$title])) {
                        unset($menu[$section][$title]);
                    }
                } else {
                    $menu[$section][$title] = $modules;
                }
            }
            if (count($this->leftMenu[$section]) == 0) {
                unset($this->leftMenu[$section]);
            }
            if (isset($menu[$section]) && count($menu[$section]) == 0) {
                unset($menu[$section]);
            }
            unset($modules);
        }
        unset($items);
        return $menu;
    }
    
    public function getActiveModuleClass($manager, $module)
    {
        $modulesPermissions = $this->managers->getModulesPermissions();

        if (empty($module) || !is_file('backend/Controllers/'.$module.'.php')) {
            foreach ($this->getMenu($manager) as $section => $items) {
                foreach ($items as $title => $modules) {
                    if ($this->managers->access($modulesPermissions[$modules], $manager)) {
                        $module = $modules;
                        break 2;
                    }
                }
            }
            unset($modules);
        }

        return $module;
    }
    
    public function getActiveModuleName($manager, $module)
    {
        $modulesPermissions = $this->managers->getModulesPermissions();
        $activeModuleName = null;
        // Если не запросили модуль - используем модуль первый из разрешенных
        if (empty($module) || !is_file('backend/Controllers/'.$module.'.php')) {
            foreach ($this->getMenu($manager) as $section => $items) {
                foreach ($items as $title => $modules) {
                    if ($this->managers->access($modulesPermissions[$modules], $manager)) {
                        //$module = $modules;
                        $activeModuleName = $title;
                        break 2;
                    }
                }
            }
            unset($modules);
        } else {
            foreach ($this->leftMenu as $section => $items) {
                foreach ($items as $title => $modules) {
                    if (in_array($module, $modules)) {
                        $activeModuleName = $title;
                        break 2;
                    }
                }
            }
        }
        
        return $activeModuleName;
    }

    public function getPermissionMenu($btr = null)
    {
        $permissionMenu = [];

        $menu = $this->leftMenu;
        foreach($menu as $blockName => $items) {
            $permissionMenu[$blockName] = $this->groupPermissionByBlockMenu($items);
        }

        if (is_null($btr)) {
            return $permissionMenu;
        }

        return $this->replaceTranslations($btr, $permissionMenu);
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

        foreach($blockMenu as $itemName => $modules) {
            $permissionBlockMenu[$itemName] = $this->managers->getPermissionByModule($modules[0]);
        }

        return array_flip($permissionBlockMenu);
    }
}
