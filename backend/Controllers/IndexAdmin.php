<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendMainHelper;
use Okay\Core\Config;
use Okay\Core\Database;
use Okay\Core\Managers;
use Okay\Core\Design;
use Okay\Core\Modules\Module;
use Okay\Core\BackendPostRedirectGet;
use Okay\Core\Router;
use Okay\Core\Support;
use Okay\Core\EntityFactory;
use Okay\Core\Languages;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Settings;
use Okay\Core\ManagerMenu;
use Okay\Core\BackendTranslations;
use Okay\Core\TemplateConfig;
use Okay\Core\Translit;
use Okay\Entities\ManagersEntity;
use Okay\Entities\LanguagesEntity;
use Okay\Entities\CallbacksEntity;
use Okay\Entities\CommentsEntity;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\FeedbacksEntity;
use Okay\Entities\OrdersEntity;
use Okay\Entities\OrderStatusEntity;
use OkayLicense\License;
use Okay\Entities\SupportInfoEntity;

class IndexAdmin
{

    protected $manager;
    protected $backendController;
    
    /**
     * @var EntityFactory
     */
    protected $entity;

    /**
     * @var Design
     */
    protected $design;
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Languages
     */
    protected $languages;

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var Managers
     */
    protected $managers;

    /**
     * @var SupportInfoEntity
     */
    protected $supportInfoEntity;

    /**
     * @var Support
     */
    protected $support;

    /**
     * @var BackendPostRedirectGet
     */
    protected $postRedirectGet;

    public function onInit(
        Design $design,
        Request $request,
        Response $response,
        Settings $settings,
        Config $config,
        License $license,
        Languages $languages,
        EntityFactory $entityFactory,
        ManagerMenu $managerMenu,
        Database $db,
        Translit $translit,
        LanguagesEntity $languagesEntity,
        Managers $managers,
        ManagersEntity $managersEntity,
        Support $support,
        SupportInfoEntity $supportInfoEntity,
        Router $router,
        BackendMainHelper $backendMainHelper,
        Module $module,
        BackendPostRedirectGet $postRedirectGet,
        TemplateConfig $templateConfig
    ) {
        $this->design        = $design;
        $this->request       = $request;
        $this->response      = $response;
        $this->settings      = $settings;
        $this->config        = $config;
        $this->languages     = $languages;
        $this->entityFactory = $entityFactory;
        $this->db            = $db;
        $this->managers      = $managers;
        $this->support       = $support;
        $this->supportInfoEntity = $supportInfoEntity;
        $this->postRedirectGet   = $postRedirectGet;
        
        $design->assign('is_mobile', $design->isMobile());
        $design->assign('is_tablet', $design->isTablet());
        $design->assign('is_module', $module->isBackendControllerName($this->backendController));

        $design->assign('settings',  $this->settings);
        $design->assign('config',    $this->config);

        $this->design->assign('rootUrl', $this->request->getRootUrl());
        
        $design->assign('manager', $this->manager);
        $design->assign('registered_front_css', $templateConfig->getRegisteredCss());

        $supportInfo = $supportInfoEntity->getInfo();
        $this->design->assign('support_info', $supportInfo);

        $this->design->assign('front_routes', $router->getFrontRoutes());
        
        $isNotLocalServer = !in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '0:0:0:0:0:0:0:1']);
        if (empty($supportInfo->public_key) && !empty($supportInfo->is_auto) && $isNotLocalServer) {
            $supportInfoEntity->updateInfo(['is_auto' => 0]);
            if ($support->getNewKeys() !== false) {
                $this->response->addHeader("Refresh:0");
                $this->response->sendHeaders();
                exit();
            }
        }

        if ($this->backendController != "AuthAdmin") {
            $menu = $managerMenu->getMenu($this->manager);
            $activeControllerName = $managerMenu->getActiveControllerName($this->manager, $this->backendController);
            $design->assign('left_menu', $menu);
            $design->assign('menu_selected', $activeControllerName);
        }

        $design->assign('translit_pairs', $translit->getTranslitPairs());

        /** @var CurrenciesEntity $currenciesEntity */
        $currenciesEntity = $this->entityFactory->get(CurrenciesEntity::class);
        $this->design->assign("currency", $currenciesEntity->getMainCurrency());
        $backendMainHelper->evensCounters();
        
        // Язык
        $languagesList = $languagesEntity->mappedBy('id')->find();
        $design->assign('languages', $languagesList);
        
        if (count($languagesList)) {
            $this->design->assign('current_language', $languagesList[$languages->getLangId()]);
        }

        $langId = $languages->getLangId();
        $design->assign('lang_id', $langId);
        
        $mainLanguage = $languages->getMainLanguage();
        if (!empty($mainLanguage->id)) {
            $design->assign('main_lang_id', $mainLanguage->id);
        }
        
        $design->assign('is_valid_license', $license->check());

        if ($request->method('post') && !empty($this->manager->id)) {
            $managersEntity->updateLastActivityDate($this->manager->id);
        }

        $additionalSectionIcons = $managerMenu->getAdditionalSectionItems();
        $this->design->assign('additional_section_icons', $additionalSectionIcons);

        $menuCounters = $managerMenu->getCounters();
        $this->design->assign('menu_counters', $menuCounters);

        $backendMainHelper->commonBeforeControllerProcedure();
        $backendMainHelper->beforeControllerProcedure(static::class);

        if ($messageSuccess = $this->postRedirectGet->matchMessageSuccess()) {
            $this->design->assign('message_success', $messageSuccess);
        }

        // Запоминаем логин менеджера для работы темы под админом
        if (!empty($this->manager->login)) {
            setcookie('admin_login', $this->manager->login, time() + 60 * 60 * 24 * 3, '/');
        }

        if (isset($_SESSION['show_learn'])) {
            unset($_SESSION['show_learn']);
            $response->redirectTo($this->request->getRootUrl() . '/backend/index.php?controller=LearningAdmin');
        }

        if ($this->backendController === 'AuthAdmin' || $this->managers->access($this->managers->getPermissionByController($this->backendController), $this->manager)) {
            return true;
        }

        return false;
    }

    public function __construct($manager, $backendController)
    {
        $this->manager = $manager;
        $this->backendController  = $backendController;
    }
}
