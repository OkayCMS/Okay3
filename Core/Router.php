<?php


namespace Okay\Core;


use \Bramus\Router\Router as BRouter;
use Okay\Entities\LanguagesEntity;

class Router {
    
    private $currentRouteName;
    private $routeParams;
    
    private static $routes;

    /**
     * @var BRouter 
     */
    private $router;

    /**
     * @var Request 
     */
    private $request;

    /**
     * @var Response 
     */
    private $response;

    /**
     * @var EntityFactory 
     */
    private $entityFactory;

    /**
     * @var ServiceLocator 
     */
    private $serviceLocator;

    /**
     * @var Languages 
     */
    private static $languages;
    
    // todo сделать валидацию роутов
    public function __construct(
        BRouter $router,
        Request $request,
        Response $response,
        EntityFactory $entityFactory,
        Languages $languages
    ) {
        
        // SL будем использовать только для получения сервисов, которые запросили для контроллера
        $this->serviceLocator = new ServiceLocator();
        
        $this->router       = $router;
        $this->request      = $request;
        $this->response     = $response;
        $this->entityFactory = $entityFactory;
        self::$languages    = $languages;
        
        self::$routes = require_once 'Core/config/routes.php';
        
    }

    public static function getRouteByName($name)
    {
        return isset(self::$routes[$name]) ? self::$routes[$name] : false;
    }
    
    public function run()
    {
        $router = $this->router;
        $routes = self::$routes;
        $request = $this->request;
        
        // Добавляем роуты по языкам в обратном порядке, т.к. первый язык не имеет приставки,
        // и роутер перехватывает $page с другого языка
        $languages = self::$languages->getAllLanguages();
        $languages = array_reverse($languages);
        foreach ($languages as $language) {
            $baseRoute = '';
            $label = self::$languages->getLangLink($language->id);

            if (!empty(trim($label, '/'))) {
                $baseRoute = '/' . trim($label, '/');
            }
            
            foreach ($routes as $routeName => $route) {
                $pattern = $baseRoute . $this->getPattern($route);
                
                $router->all("{$pattern}", function(...$params) use ($router, $route, $request, $language, $baseRoute, $routeName) {
                    $this->currentRouteName = $routeName;
                    $this->request->setBasePath($router->getBasePath());

                    $this->request->setPageUrl($this->getCurrentUri( // todo это должен сам Request знать
                        $router->getCurrentUri(),
                        $baseRoute
                    ));
                    
                    $request->setLangId($language->id);
                    $routeVars = [];
                    $controllerName = $route['params']['controller'];
                    
                    // Если не передали неймспейс, тогда укажем по умолчанию
                    if (strpos($controllerName, '\\') === false) {
                        $controllerName = '\\Okay\\Controllers\\' . $controllerName;
                    }
                    $method = $route['params']['method'];
                    
                    // Если язык выключен, отдадим 404
                    if (!$language->enabled && empty($_SESSION['admin'])) {
                        $controllerName = '\\Okay\\Controllers\\ErrorController';
                        $method = 'pageNotFound';
                    }
                    
                    $defaults = isset($route['defaults']) ? $route['defaults'] : [];

                    preg_match_all('~{\$(.+?)}~', $route['slug'], $matches);
                    $routeVars = array_merge($routeVars, $matches[1]);
                    
                    include_once 'Core/SmartyPlugins/SmartyPlugins.php';
                    include_once 'Modules/ModulesConfig.php';
                    // Если контроллер вернул false, кидаем 404
                    if ($this->createControllerInstance($controllerName, $method, $params, $routeVars, $defaults) === false) {
                        $this->createControllerInstance('\\Okay\\Controllers\\ErrorController', 'pageNotFound', $params, $routeVars, $defaults);
                    }
                });
            }
        }

        $response = $this->response;
        
        $router->run(function() use ($response) {
            $response->sendContent();
        });
        
    }
    
    private function createControllerInstance($controllerName, $method, $params = [], $routeVars = [], $defaults = [])
    {
        $controller = new $controllerName();

        foreach ($this->getMethodParams($controller, $method, $params, $routeVars, $defaults) as $name=>$paramValue) {
            if (!is_object($paramValue)) {
                $this->routeParams[$name] = $paramValue;
            }
        }

        // Передаем контроллеру, все, что запросили
        call_user_func_array([$controller, 'onInit'], $this->getMethodParams($controller, 'onInit', $params, $routeVars, $defaults));
        return call_user_func_array([$controller, $method], $this->getMethodParams($controller, $method, $params, $routeVars, $defaults));
    }
    
    /**
     * @return array
     * Метод возвращает все параметры в виде ассоциативного массива, которые указаны в поле slug роута
     */
    public function getCurrentRouteParams()
    {
        return $this->routeParams;
    }
    
    public static function generateUrl($routeName, $params = [], $isAbsolute = false, $langId = null) // todo наблюдать, нормально ли будет работать статически
    {
        if (empty($routeName)) {
            throw new \Exception('Empty param "route"');
        }

        if (!$route = self::getRouteByName($routeName)) {
            throw new \Exception("Route \"{$routeName}\" not found");
        }

        unset($params['route']);

        // Перебираем переданные параметры, чтобы подставить их как элементы роута
        $urlData = [];
        if (!empty($params)) {
            foreach ($params as $var=>$param) {
                $urlData['{$' . $var . '}'] = $param;
            }
        }

        $slug = $route['slug'];
        
        $result = trim(strtr($slug, $urlData), '/');

        // Если это не внешний урл, добавим языковой префикс
        if (!preg_match('~^https?://~', $result)) {
            $result = self::$languages->getLangLink($langId) . $result;
        }

        $result = preg_replace('~\{\$[^\$]*\}~', '', $result);
        $result = trim($result, '/');
        
        if ($isAbsolute === true) {
            $result = Request::getRootUrl() . '/' . $result;
        }
        
        return $result;
    }
    
    public function getCurrentRouteName()
    {
        return $this->currentRouteName;
    }
    
    public function getPattern($route)
    {
        $pattern = !empty($route['patterns']) ? strtr($route['slug'], $route['patterns']) : $route['slug'];
        $pattern = trim(preg_replace('~\{\$.+?\}~', '([^/]+)', $pattern), '/');
        return !empty($pattern) ? '/' . $pattern : $pattern;
    }
    
    private function getMethodParams($controller, $methodName, $routeParams = [], $routeVars = [], $defaults = [])
    {
        $methodParams = [];
        $allParams = [];
        
        // Перебираем переменные роута, чтобы заполнить их дефолтными значениями
        if (!empty($routeVars)) {
            foreach ($routeVars as $key => $routeVar) {
                $param = isset($routeParams[$key]) ? $routeParams[$key] : null;
                $allParams[$routeVar] = (empty($param) && !empty($defaults['{$' . $routeVar . '}']) ? $defaults['{$' . $routeVar . '}'] : $param);
            }
        }
        
        // Проходимся рефлексией по параметрам метода, определяем их тип, и пытаемся через DI передать нужный объект
        // Если тип не указан, тогда связываем название переменной в поле slug роута, с названием аргумента метода
        $reflectionMethod = new \ReflectionMethod($controller, $methodName);
        foreach ($reflectionMethod->getParameters() as $parameter) {
            
            if ($parameter->getClass() !== null) { // если для аргумента указан type hint, передадим экземляр соответствующего класса
                $class = new \ReflectionClass($parameter->getClass()->name);
                $namespace = trim($class->getNamespaceName(), '\\');
                
                // Определяем namespace запрашиваемого типа, это Entity или сервис из DI
                if ($namespace == 'Okay\Entities') {
                    $methodParams[$parameter->getClass()->name] = $this->entityFactory->get($parameter->getClass()->name);
                } else {
                    $methodParams[$parameter->getClass()->name] = $this->serviceLocator->getService($parameter->getClass()->name);
                }
            } elseif (!empty($allParams[$parameter->name])) { // если тип не указан, передаем строковую переменную как значение из поля slug (то, что попало под регулярку)
                $methodParams[$parameter->name] = $allParams[$parameter->name];
            } elseif (!empty($defaults['{$' . $parameter->name . '}'])) { // на крайний случай, может в поле defaults роута указано значение этой переменной
                $methodParams[$parameter->name] = $defaults['{$' . $parameter->name . '}'];
            } elseif ($parameter->isDefaultValueAvailable() === false) { // Если не нашли значения аргументу, и он не имеет значения по умолчанию в методе контроллера, ошибка
                $controllerName = $reflectionMethod->getDeclaringClass()->name;
                throw new \Exception("Missing argument \"\${$parameter->name}\" in \"{$controllerName}->{$methodName}()\"");
            }
        }

        return $methodParams;
    }

    private function getCurrentUri($currentUri, $baseUri)
    {
        return preg_replace('~^('.$baseUri.'/?)(.*)$~', '$2', $currentUri);
    }
    
}
