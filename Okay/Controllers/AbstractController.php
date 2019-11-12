<?php


namespace Okay\Controllers;


use Okay\Core\Cart;
use Okay\Core\Comparison;
use Okay\Core\Config;
use Okay\Core\Router;
use Okay\Core\ServiceLocator;
use Okay\Core\WishList;
use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Languages;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Settings;
use Okay\Core\TemplateConfig;
use Okay\Helpers\MainHelper;
use Okay\Helpers\MetadataHelpers\MetadataInterface;
use Okay\Helpers\CommonHelper;

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
    
    private $metadataHelper;

    protected function setMetadataHelper(MetadataInterface $metadataHelper)
    {
        $this->metadataHelper = $metadataHelper;
    }
    
    /*
     * Метод, который вызывается всегда перед вызовом метода контроллера.
     * В методе можно принимать аргументы, с указанием типа данных, они автоматически через DI сюда передадутся
     * ВНИМАНИЕ! В конструкторе эти переменные еще не доступны!
     */
    final public function onInit(
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
        MainHelper $mainHelper,
        CommonHelper $commonHelper
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

        $commonHelper->rootPostProcedure();
    }
    
    /*
     * Метод, который вызывается всегда после вызовом метода контроллера.
     * В методе можно принимать аргументы, с указанием типа данных, они автоматически через DI сюда передадутся
     */
    final public function afterController(MainHelper $mainHelper)
    {
        $mainHelper->commonAfterControllerProcedure($this->metadataHelper);
    }
    
}
