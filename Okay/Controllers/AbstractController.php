<?php


namespace Okay\Controllers;


use Okay\Core\Cart;
use Okay\Core\Comparison;
use Okay\Core\Config;
use Okay\Core\JsSocial;
use Okay\Core\Modules\Module;
use Okay\Core\Notify;
use Okay\Core\Router;
use Okay\Core\ServiceLocator;
use Okay\Core\Validator;
use Okay\Core\WishList;
use Okay\Entities\CallbacksEntity;
use Okay\Entities\ManagersEntity;
use Okay\Entities\MenuEntity;
use Okay\Entities\MenuItemsEntity;
use Okay\Entities\PagesEntity;
use Okay\Entities\SubscribesEntity;
use Okay\Entities\TranslationsEntity;
use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Languages;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Settings;
use Okay\Core\TemplateConfig;
use Okay\Entities\LanguagesEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\UserGroupsEntity;
use Okay\Entities\UsersEntity;

class AbstractController
{
    
    /* Смысл класса в доступности следующих переменных в любом контроллере */
    public $currency;
    public $currencies;
    public $user;
    public $group;
    public $page;
    public $language;

    /** @var Design */
    protected $design;
    
    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var Settings */
    protected $settings;

    /** @var Config */
    protected $config;

    /** @var Languages */
    protected $languages;

    /** @var TemplateConfig */
    protected $templateConfig;

    /** @var EntityFactory */
    protected $entityFactory;
    
    /** @var Router */
    protected $router;

    /** @var Cart */
    protected $cart;

    /** @var Comparison */
    protected $comparison;

    /** @var WishList */
    protected $wishList;

    /** @var JsSocial */
    protected $jsSocial;

    /** @var ServiceLocator */
    protected $serviceLocator;

    /*
     * Метод, который вызывается всегда перед вызовом метода контроллера.
     * В методе можно принимать аргументы, с указанием типа данных, они автоматически через DI сюда передадутся
     * ВНИМАНИЕ! В конструкторе эти переменные еще не доступны!
     */
    final public function onInit(
        Validator $validator,
        Notify $notify,
        Design $design,
        Request $request,
        Response $response,
        Settings $settings,
        Config $config,
        Languages $languages,
        EntityFactory $entityFactory,
        Router $router,
        Cart $cart,
        Comparison $comparison,
        WishList $wishList,
        JsSocial $jsSocial,
        Module $module
    ) {
        $this->design       = $design;
        $this->request      = $request;
        $this->response     = $response;
        $this->settings     = $settings;
        $this->config       = $config;
        $this->languages    = $languages;
        $this->router       = $router;
        $this->entityFactory  = $entityFactory;
        $this->cart           = $cart;
        $this->comparison     = $comparison;
        $this->wishList       = $wishList;
        $this->jsSocial       = $jsSocial;
        $this->serviceLocator = new ServiceLocator();

        $this->activateDynamicJs();// метод должен быть в начале
        
        // Фронт конфигурируем не в директории модуля.
        $this->configureFront();

        // сменим директорию шаблонов на директорию модуля
        if ($module->isModuleController(static::class)) {
            $moduleTemplateDir = $module->generateModuleTemplateDir(
                $module->getVendorName(static::class),
                $module->getModuleName(static::class)
            );
            
            $design->setModuleTemplatesDir($moduleTemplateDir);
            $design->useModuleDir();
        }        
        
        $this->activatePRG();
        $this->rootPOST($validator, $notify);
    }
    
    private function activateDynamicJs()
    {
        // Если пришли не за скриптом, очищаем все переменные для динамического JS
        if (($routeName = $this->router->getCurrentRouteName()) != 'dynamic_js') {
            unset($_SESSION['dynamic_js']);
            $route = $this->router->getRouteByName($routeName);
            $_SESSION['dynamic_js']['controller'] = $route['params']['controller'];
        }
    }
    
    private function configureFront()
    {

        /** @var CategoriesEntity $categoriesEntity */
        $categoriesEntity = $this->entityFactory->get(CategoriesEntity::class);

        /** @var CurrenciesEntity $currenciesEntity */
        $currenciesEntity = $this->entityFactory->get(CurrenciesEntity::class);

        /** @var PagesEntity $pagesEntity */
        $pagesEntity = $this->entityFactory->get(PagesEntity::class);

        $langId = $this->request->getLangId();
        $this->languages->setLangId($langId);

        // Передаем стили и скрипты в шаблон
        $templateConfig = $this->serviceLocator->getService(TemplateConfig::class);
        $this->design->assign('ok_head', $templateConfig->head());
        $this->design->assign('ok_footer', $templateConfig->footer());
        
        // Передаем в дизайн название текущего роута
        $this->design->assign('route_name', $this->router->getCurrentRouteName());
        $this->design->assign('current_page', $this->request->get('page'));
        
        $langEntity = $this->entityFactory->get(LanguagesEntity::class);
        $this->language = $langEntity->get($langId);
        
        $translations = $this->entityFactory->get(TranslationsEntity::class);
        $translations->setDebug($this->config->debug_translation);
        $this->design->assign('lang', $translations->find(['lang' => $this->language->label]));
        
        $this->design->assign('settings', $this->settings);
        $this->design->assign('config', $this->config);
        $this->design->assign('rootUrl', $this->request->getRootUrl());

        $this->design->assign('is_mobile', $this->design->isMobile());
        $this->design->assign('is_tablet', $this->design->isTablet());
        
        $counters = array();
        $tmp_counters = $this->settings->counters;
        if (!empty($tmp_counters)) {
            foreach ($tmp_counters as $c) {
                $counters[$c->position][] = $c;
            }
        }
        $this->design->assign('counters', $counters);
        
        // Категории товаров
        $allCategories = $categoriesEntity->find();
        $this->countVisible($categoriesEntity->getCategoriesTree(), $allCategories);
        $this->design->assign('categories', $categoriesEntity->getCategoriesTree());
        
        $allLanguages = $langEntity->find();
        foreach ($allLanguages as $l) {
            $routeParams = $this->router->getCurrentRouteRequiredParams();
            $l->url = $this->router->generateUrl($this->router->getCurrentRouteName(), $routeParams, true, $l->id);
        }
        
        $this->design->assign('language', $this->language);
        $this->design->assign('languages', $allLanguages);

        $this->design->assign('base', $this->request->getRootUrl());
        
        // Все валюты
        $this->currencies = $currenciesEntity->find(['enabled'=>1]);
        // Выбор текущей валюты
        if ($currencyId = $this->request->get('currency_id', 'integer')) {
            $_SESSION['currency_id'] = $currencyId;
            $this->response->redirectTo($this->request->url(['currency_id'=>null]));
        }
        // Берем валюту из сессии
        if (isset($_SESSION['currency_id'])) {
            $this->currency = $currenciesEntity->get((int)$_SESSION['currency_id']);
        } else {
            $this->currency = reset($this->currencies);
        }

        $this->design->assign('cart', $this->cart->get());
        $this->design->assign('wishlist', $this->wishList->get());
        $this->design->assign('comparison', $this->comparison->get());

        $this->design->assign('currencies', $this->currencies);
        $this->design->assign('currency',   $this->currency);
        
        $this->design->assign('js_custom_socials', $this->jsSocial->getCustomSocials());
        
        $this->page = $pagesEntity->get($this->request->getPageUrl());
        $this->design->assign('page', $this->page);

        $pages = $pagesEntity->find(['visible'=>1]);
        $this->design->assign('pages', $pages);

        $reflector = new \ReflectionClass(static::class);
        $this->design->assign('controller', $reflector->getShortName());

        $menuEntity = $this->entityFactory->get(MenuEntity::class);
        $menuItemsEntity = $this->entityFactory->get(MenuItemsEntity::class);
        $menus = $menuEntity->find(['visible' => 1]);
        if (!empty($menus)) {
            foreach ($menus as $menu) {
                $this->design->assign("menu", $menu);
                $all_menu_items = $menuItemsEntity->getMenuItems();
                $this->countVisible($menuItemsEntity->getMenuItemsTree((int)$menu->id), $all_menu_items, 'submenus');
                $this->design->assign("menu_items", $menuItemsEntity->getMenuItemsTree((int)$menu->id));
                $this->design->assign(MenuEntity::MENU_VAR_PREFIX . $menu->group_id, $this->design->fetch("menu.tpl"));
            }
        }

        // Пользователь, если залогинен
        if (isset($_SESSION['user_id'])) {
            /** @var UsersEntity $usersEntity */
            $usersEntity = $this->entityFactory->get(UsersEntity::class);
            
            /** @var UserGroupsEntity $userGroupsEntity */
            $userGroupsEntity = $this->entityFactory->get(UserGroupsEntity::class);
            
            $user = $usersEntity->get((int)$_SESSION['user_id']);
            if (!empty($user)) {
                $this->user = $user;
                $this->group = $userGroupsEntity->get($this->user->group_id);
            }
            $this->design->assign('user',       $this->user);
            $this->design->assign('group',      $this->group);
        }
        
    }

    /*Подсчет количества видимых дочерних элементов*/
    private function countVisible(array $items, $allItems, $subItemsName = 'subcategories')
    {
        foreach ($items as $item) {
            if (isset($allItems[$item->parent_id]) && !isset($allItems[$item->parent_id]->count_children_visible)) {
                $allItems[$item->parent_id]->count_children_visible = 0;
            }
            if ($item->parent_id && $item->visible) {
                $allItems[$item->parent_id]->count_children_visible++;
            }
            if (isset($item->{$subItemsName})) {
                $this->countVisible($item->{$subItemsName}, $allItems, $subItemsName);
            }
        }
    }
    
    private function activatePRG()
    {
        if ($prgSeoHide = $this->request->post("prg_seo_hide")) {
            $this->response->redirectTo($prgSeoHide);
            exit;
        }
    }
    
    private function rootPOST(Validator $validator, Notify $notify)
    {
        if ($this->request->method('post') && $this->request->post('callback')) {
            
            /** @var CallbacksEntity $callbacksEntity */
            $callbacksEntity = $this->entityFactory->get(CallbacksEntity::class);
            
            $callback = new \stdClass();
            $callback->phone        = $this->request->post('phone');
            $callback->name         = $this->request->post('name');
            $callback->url          = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $callback->message      = $this->request->post('message');
            $captcha_code =  $this->request->post('captcha_code', 'string');

            $this->design->assign('callname',  $callback->name);
            $this->design->assign('callphone', $callback->phone);
            $this->design->assign('callmessage', $callback->message);

            /*Валидация данных клиента*/
            if (!$validator->isName($callback->name, true)) {
                $this->design->assign('call_error', 'empty_name', true);
            } elseif (!$validator->isPhone($callback->phone, true)) {
                $this->design->assign('call_error', 'empty_phone', true);
            } elseif (!$validator->isComment($callback->message)) {
                $this->design->assign('call_error', 'empty_comment', true);
            } elseif ($this->settings->captcha_callback && !$validator->verifyCaptcha('captcha_callback', $captcha_code)) {
                $this->design->assign('call_error', 'captcha', true);
            } elseif ($callbackId = $callbacksEntity->add($callback)) {
                $this->design->assign('call_sent', true, true);
                // Отправляем email
                $notify->emailCallbackAdmin($callbackId);
            } else {
                $this->design->assign('call_error', 'unknown error', true);
            }
        }

        // Если прилетел токен, вероятно входят через соц. сеть
        if (empty($this->user) && $this->request->method('post') && ($token = $this->request->post('token'))) {

            /** @var UsersEntity $usersEntity */
            $usersEntity = $this->entityFactory->get(UsersEntity::class);
            
            $uLoginData = $usersEntity->getUloginUser($token);

            if (!empty($uLoginData)) {
                $user = new \stdClass();
                $user->last_ip = $_SERVER['REMOTE_ADDR'];
                $user->name    = $uLoginData['last_name'] . ' ' . $uLoginData['first_name'];
                $user->email   = $uLoginData['email'];

                if (empty($usersEntity->count(['email' => (string)$user->email]))) {
                    $user->password = $usersEntity->generatePass(6);
                    $userId = $usersEntity->add($user);
                    $_SESSION['user_id'] = $userId;
                    // Перенаправляем пользователя в личный кабинет
                    $this->response->redirectTo(Router::generateUrl('user', [], true));
                    exit;
                }
            }
        }

        /*E-mail подписка*/
        if ($this->request->post('subscribe')) {

            /** @var SubscribesEntity $subscribesEntity */
            $subscribesEntity = $this->entityFactory->get(SubscribesEntity::class);
            
            $email = $this->request->post('subscribe_email');
            
            if (!$validator->isEmail($email, true)) {
                $this->design->assign('subscribe_error', 'empty_email', true);
            } elseif ($subscribesEntity->count(['email' => $email]) > 0) {
                $this->design->assign('subscribe_error', 'email_exist', true);
            } else {
                $subscribesEntity->add(['email' => $email]);
                
                $this->design->assign('subscribe_success', '1', true);
            }
        }
    }
    
    protected function setHeaderLastModify($lastModify)
    {
        $lastModify = empty($lastModify) ? date("Y-m-d H:i:s") : $lastModify;
        $tmpDate = date_parse($lastModify);
        @$lastModifiedUnix = mktime( $tmpDate['hour'], $tmpDate['minute'], $tmpDate['second '], $tmpDate['month'],$tmpDate['day'],$tmpDate['year'] );
        
        //Проверка модификации страницы
        $lastModified = gmdate("D, d M Y H:i:s \G\M\T", $lastModifiedUnix);                
        $ifModifiedSince = false;
        if (isset($_ENV['HTTP_IF_MODIFIED_SINCE'])) {
            $ifModifiedSince = strtotime(substr($_ENV['HTTP_IF_MODIFIED_SINCE'], 5));
        }  
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $ifModifiedSince = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));
        }
        if ($ifModifiedSince && $ifModifiedSince >= $lastModifiedUnix) {
            $this->response->addHeader($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
            exit;
        }
        
        $this->response->addHeader('Last-Modified: '. $lastModified);
    }
    
}
