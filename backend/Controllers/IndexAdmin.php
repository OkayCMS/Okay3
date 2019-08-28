<?php


namespace Okay\Admin\Controllers;


use Okay\Core\Config;
use Okay\Core\Database;
use Okay\Core\Managers;
use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Languages;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Settings;
use Okay\Core\ManagerMenu;
use Okay\Core\BackendTranslations;
use Okay\Core\Translit;
use Okay\Entities\ManagersEntity;
use Okay\Entities\LanguagesEntity;
use Okay\Entities\CallbacksEntity;
use Okay\Entities\CommentsEntity;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\FeedbacksEntity;
use Okay\Entities\OrdersEntity;
use Okay\Entities\OrderStatusEntity;

class IndexAdmin
{

    protected $manager;
    
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
    
    public function onInit(
        Design $design,
        Request $request,
        Response $response,
        Settings $settings,
        Config $config,
        Languages $languages,
        EntityFactory $entityFactory,
        ManagerMenu $managerMenu,
        BackendTranslations $backendTranslations,
        Database $db,
        Translit $translit,
        LanguagesEntity $languagesEntity,
        Managers $managers,
        ManagersEntity $managersEntity
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
        
        $design->set_templates_dir('backend/design/html');
        $design->set_compiled_dir('backend/design/compiled');
        
        $is_mobile = $design->is_mobile();
        $is_tablet = $design->is_tablet();
        $design->assign('is_mobile', $is_mobile);
        $design->assign('is_tablet', $is_tablet);

        $design->assign('settings',  $this->settings);
        $design->assign('config',    $this->config);

        $this->design->assign('rootUrl', $this->request->getRootUrl());
        
        $design->assign('manager', $this->manager);

        if ($this->module != "AuthAdmin") {
            $menu = $managerMenu->getMenu($this->manager);
            $activeModuleName = $managerMenu->getActiveModuleName($this->manager, $this->module);
            $design->assign('left_menu', $menu);
            $design->assign('menu_selected', $activeModuleName);
        }

        $design->assign('translit_pairs', $translit->getTranslitPairs());

        /** @var CurrenciesEntity $currenciesEntity */
        $currenciesEntity = $this->entityFactory->get(CurrenciesEntity::class);
        $this->design->assign("currency", $currenciesEntity->getMainCurrency());
        $this->evensCounters();
        
        // Язык
        $languagesList = $languagesEntity->find();
        $design->assign('languages', $languagesList);
        
        if (count($languagesList)) {
            $postLangId = $this->request->post('lang_id', 'integer');
            $adminLangId = ($postLangId ? $postLangId : $request->get('lang_id', 'integer'));
            
            if ($adminLangId) {
                $_SESSION['admin_lang_id'] = $adminLangId;
            }
            
            if (!isset($_SESSION['admin_lang_id']) || !isset($languagesList[$_SESSION['admin_lang_id']])) {
                $l = $languagesEntity->getMainLanguage();
                $_SESSION['admin_lang_id'] = $l->id;
            }
            
            $this->design->assign('current_language', $languagesList[$_SESSION['admin_lang_id']]);
            $languages->setLangId($_SESSION['admin_lang_id']);
        }

        $langId = $languages->getLangId();
        $design->assign('lang_id', $langId);
        $design->assign('lang_link', $languages->getLangLink());
        
        if (!empty($this->manager)) {
            // Перевод админки
            $file = "backend/lang/" . $this->manager->lang . ".php";
            if (!file_exists($file)) {
                foreach (glob("backend/lang/??.php") as $f) {
                    $file = "backend/lang/" . pathinfo($f, PATHINFO_FILENAME) . ".php";
                    break;
                }
            }
            require_once($file);
        }

        $design->assign('btr', $backendTranslations);

        if ($request->method('post') && !empty($this->manager->id)) {
            $managersEntity->updateLastActivityDate($this->manager->id);
        }

        if ($this->module === 'AuthAdmin' || $this->managers->access($this->managers->getPermissionByModule($this->module), $this->manager)) {
            return true;
        }

        return false;
    }
    
    private function evensCounters()
    {
        /** @var OrderStatusEntity $orderStatusesEntity */
        $orderStatusesEntity = $this->entityFactory->get(OrderStatusEntity::class);
        
        /** @var OrdersEntity $ordersEntity */
        $ordersEntity = $this->entityFactory->get(OrdersEntity::class);
        
        /** @var CommentsEntity $commentsEntity */
        $commentsEntity = $this->entityFactory->get(CommentsEntity::class);
        
        /** @var FeedbacksEntity $feedbacksEntity */
        $feedbacksEntity = $this->entityFactory->get(FeedbacksEntity::class);
        
        /** @var CallbacksEntity $callbacksEntity */
        $callbacksEntity = $this->entityFactory->get(CallbacksEntity::class);

        $newOrdersCounter = 0;
        if ($statusId = $orderStatusesEntity->order('position_asc')->cols(['id'])->find(['limit' => 1])) {
            $statusId = reset($statusId);

            $newOrdersCounter = $ordersEntity->count(['status_id' => $statusId]);
            $this->design->assign("new_orders_counter", $newOrdersCounter);
        }

        $newCommentsCounter = $commentsEntity->count(['approved'=>0]);
        $this->design->assign("new_comments_counter", $newCommentsCounter);

        $newFeedbacksCounter = $feedbacksEntity->count(['processed'=>0]);
        $this->design->assign("new_feedbacks_counter", $newFeedbacksCounter);

        $newCallbacksCounter = $callbacksEntity->count(['processed'=>0]);
        $this->design->assign("new_callbacks_counter", $newCallbacksCounter);

        $this->design->assign("all_counter", $newOrdersCounter+$newCommentsCounter+$newFeedbacksCounter+$newCallbacksCounter);
    }

    public function __construct($manager, $module)
    {
        $this->manager = $manager;
        $this->module  = $module;
    }    
}
