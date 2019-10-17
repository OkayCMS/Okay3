<?php


namespace Okay\Controllers;


use Okay\Core\Cart;
use Okay\Core\Comparison;
use Okay\Core\Config;
use Okay\Core\Notify;
use Okay\Core\Router;
use Okay\Core\ServiceLocator;
use Okay\Core\Validator;
use Okay\Core\WishList;
use Okay\Entities\CallbacksEntity;
use Okay\Entities\SubscribesEntity;
use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Languages;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Settings;
use Okay\Core\TemplateConfig;
use Okay\Entities\UsersEntity;
use Okay\Helpers\MainHelper;

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
        MainHelper $mainHelper
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
        $this->serviceLocator = new ServiceLocator();

        $mainHelper->activatePRG();
        $mainHelper->activateDynamicJs();// метод должен быть в начале

        // Передаем на фронт все, что может там понадобиться
        $mainHelper->setDesignDataProcedure();
        
        $this->languages    = $mainHelper->getAllLanguages();
        $this->language     = $mainHelper->getCurrentLanguage();
        $this->page         = $mainHelper->getCurrentPage();
        $this->currencies   = $mainHelper->getAllCurrencies();
        $this->currency     = $mainHelper->getCurrentCurrency();
        $this->user         = $mainHelper->getCurrentUser();
        $this->group        = $mainHelper->getCurrentUserGroup();

        $mainHelper->configureTemplateDirProcedure();
        
        $this->rootPOST($validator, $notify);
    }
    
    /*
     * Метод, который вызывается всегда после вызовом метода контроллера.
     * В методе можно принимать аргументы, с указанием типа данных, они автоматически через DI сюда передадутся
     */
    final public function afterController(MainHelper $mainHelper)
    {
        $mainHelper->afterControllerProcedure();
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
    
}
