<?php


namespace Okay\Core;


class Request
{
    
    private $langId;
    private $startTime;

    /**
     * @var string содержит название директории в которой установлен окай.
     */
    private $basePath;

    /**
     * @var string содержит URL страницы, без папки и языковой приставки
     */
    private $pageUrl;
    
    public function __construct()
    {
        $_POST = $this->stripslashes_recursive($_POST);
        $_GET = $this->stripslashes_recursive($_GET);

        $this->setBasePath($_SERVER['REQUEST_URI']);

        if ($this->get('lang_id', 'integer')) {
            $this->langId = $this->get('lang_id', 'integer');
        }
        
    }
    
    public function getLangId()
    {
        return $this->langId;
    }
    
    public function setLangId($langId)
    {
        $this->langId = (int)$langId;
    }
    
    public function getStartTime()
    {
        if (empty($this->startTime)) {
            throw new \Exception('Start time not been setted');
        }
        return $this->startTime;
    }
    
    public function setStartTime($time)
    {
        $this->startTime = (int)$time;
    }
    
    public function getBasePathWithDomain()
    {
        return $this->getProtocol() . '://' . $this->getDomain() . $this->getBasePath();
    }
    
    public static function getRootUrl()
    {
        return self::getDomainWithProtocol() . self::getSubDir();
    }
    
    public static function getDomainWithProtocol()
    {
        return self::getProtocol() . '://' . self::getDomain();
    }
    
    public function getBasePath()
    {
        return $this->basePath;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    public function getPageUrl()
    {
        return $this->pageUrl;
    }

    public function setPageUrl($pageUrl)
    {
        $this->pageUrl = $pageUrl;
    }
    
    private static function getDomain()
    {
        return rtrim($_SERVER['HTTP_HOST']);
    }
    
    private static function getProtocol()
    {
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5)) == 'https' ? 'https' : 'http';
        if($_SERVER["SERVER_PORT"] == 443)
            $protocol = 'https';
        elseif (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1')))
            $protocol = 'https';
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
            $protocol = 'https';
        return $protocol;
    }
    
    /**
    * Определение request-метода обращения к странице (GET, POST)
    * Если задан аргумент функции (название метода, в любом регистре), возвращает true или false
    * Если аргумент не задан, возвращает имя метода
    * Пример:
    *
    *    if($request->method('post'))
    *        print 'Request method is POST';
    *
    */
    /*Возвращение метода передачи данных*/
    public function method($method = null) {
        if(!empty($method)) {
            return strtolower($_SERVER['REQUEST_METHOD']) == strtolower($method);
        }
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
    * Возвращает переменную _GET, отфильтрованную по заданному типу, если во втором параметре указан тип фильтра
    * Второй параметр $type может иметь такие значения: integer, string, boolean
    * Если $type не задан, возвращает переменную в чистом виде
    */
    /*Прием параметров с массива $_GET*/
    public function get($name, $type = null, $default = '') {
        $val = null;
        if(isset($_GET[$name])) {
            $val = $_GET[$name];
        }
        
        if(!empty($type) && is_array($val)) {
            $val = reset($val);
        }

        if (empty($val) && (!empty($default) || $default === null)) {
            $val = $default;
        }

        if($type == 'string') {
            return strval(preg_replace('/[^\p{L}\p{Nd}\d\s_\-\.\%\s]/ui', '', $val));
        }
        
        if($type == 'integer') {
            return intval($val);
        }
        
        if($type == 'float') {
            return floatval($val);
        }
        
        if($type == 'boolean') {
            return !empty($val);
        }
        
        return $val;
    }
    
    /**
    * Возвращает переменную _POST, отфильтрованную по заданному типу, если во втором параметре указан тип фильтра
    * Второй параметр $type может иметь такие значения: integer, string, boolean
    * Если $type не задан, возвращает переменную в чистом виде
    */
    /*Прием параметров с массива $_POST*/
    public function post($name = null, $type = null, $default = null) {
        $val = null;
        if(!empty($name) && isset($_POST[$name])) {
            $val = $_POST[$name];
        } elseif(empty($name)) {
            $val = file_get_contents('php://input');
        }

        if (empty($val) && $default !== null) {
            $val = $default;
        }

        if($type == 'string') {
            return strval(preg_replace('/[^\p{L}\p{Nd}\d\s_\-\.\%\s]/ui', '', $val));
        }
        
        if($type == 'integer') {
            return intval($val);
        }
        
        if($type == 'float') {
            return floatval($val);
        }
        
        if($type == 'boolean') {
            return !empty($val);
        }
        
        return $val;
    }
    
    /**
    * Возвращает переменную _FILES
    * Обычно переменные _FILES являются двухмерными массивами, поэтому можно указать второй параметр,
    * например, чтобы получить имя загруженного файла: $filename = $request->files('myfile', 'name');
    */
    /*Прием параметров с массива $_FILES*/
    public function files($name, $name2 = null) {
        if(!empty($name2) && !empty($_FILES[$name][$name2])) {
            return $_FILES[$name][$name2];
        } elseif(empty($name2) && !empty($_FILES[$name])) {
            return $_FILES[$name];
        }

        return null;
    }

    public static function getSubDir()
    {
        $scriptDir1 = realpath(dirname(dirname(__DIR__)));
        $scriptDir2 = realpath($_SERVER['DOCUMENT_ROOT']);
        $subDir = trim(substr($scriptDir1, strlen($scriptDir2)), "/\\");

        if (!empty($subDir)) {
            $subDir = '/' . $subDir;
        }

        return $subDir;
    }

    /**
     * Рекурсивная чистка магических слешей
     */
    private function stripslashes_recursive($var) {
        if(!get_magic_quotes_gpc()) {
            return $var;
        }

        if (!is_array($var)) {
            return stripcslashes($var);
        }

        $res = null;
        foreach($var as $k=>$v) {
            $res[stripcslashes($k)] = $this->stripslashes_recursive($v);
        }

        return $res;
    }
    
    /**
    * Проверка сессии
    */
    public function checkSession()
    {
        if (!empty($_POST)) {
            if (empty($_POST['session_id']) || $_POST['session_id'] != session_id()) {
                unset($_POST);
                return false;
            }
        }
        return true;
    }
    
    /**
    * Формирование ссылки
    */
    public function url($params = array()) {
        $url = @parse_url($_SERVER["REQUEST_URI"]);
        if (!empty($url['query'])) {
            parse_str($url['query'], $query);

            if (get_magic_quotes_gpc()) {
                foreach ($query as &$v) {
                    if (!is_array($v)) {
                        $v = stripslashes(urldecode($v));
                    }
                }
            }
        }
        
        foreach($params as $name=>$value) {
            $query[$name] = $value;
        }
        
        $query_is_empty = true;
        foreach($query as $name=>$value) {
            if($value!=='' && $value!==null) {
                $query_is_empty = false;
            }
        }
        
        if(!$query_is_empty) {
            $url['query'] = http_build_query($query);
        } else {
            $url['query'] = null;
        }
        
        $result = http_build_url(null, $url);
        return $result;
    }
    
}

/*Обьявление функции построения ссылки*/
if (!function_exists('http_build_url')) {
    define('HTTP_URL_REPLACE', 1);                // Replace every part of the first URL when there's one of the second URL
    define('HTTP_URL_JOIN_PATH', 2);            // Join relative paths
    define('HTTP_URL_JOIN_QUERY', 4);            // Join query strings
    define('HTTP_URL_STRIP_USER', 8);            // Strip any user authentication information
    define('HTTP_URL_STRIP_PASS', 16);            // Strip any password authentication information
    define('HTTP_URL_STRIP_AUTH', 32);            // Strip any authentication information
    define('HTTP_URL_STRIP_PORT', 64);            // Strip explicit port numbers
    define('HTTP_URL_STRIP_PATH', 128);            // Strip complete path
    define('HTTP_URL_STRIP_QUERY', 256);        // Strip query string
    define('HTTP_URL_STRIP_FRAGMENT', 512);        // Strip any fragments (#identifier)
    define('HTTP_URL_STRIP_ALL', 1024);            // Strip anything but scheme and host
    
    // Build an URL
    // The parts of the second URL will be merged into the first according to the flags argument.
    //
    // @param    mixed            (Part(s) of) an URL in form of a string or associative array like parse_url() returns
    // @param    mixed            Same as the first argument
    // @param    int                A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
    // @param    array            If set, it will be filled with the parts of the composed url like parse_url() would return
    function http_build_url($url, $parts=array(), $flags=HTTP_URL_REPLACE, &$new_url=false) {
        $keys = array('user','pass','port','path','query','fragment');
        
        // HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
        if ($flags & HTTP_URL_STRIP_ALL) {
            $flags |= HTTP_URL_STRIP_USER;
            $flags |= HTTP_URL_STRIP_PASS;
            $flags |= HTTP_URL_STRIP_PORT;
            $flags |= HTTP_URL_STRIP_PATH;
            $flags |= HTTP_URL_STRIP_QUERY;
            $flags |= HTTP_URL_STRIP_FRAGMENT;
        }
        // HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
        else if ($flags & HTTP_URL_STRIP_AUTH) {
            $flags |= HTTP_URL_STRIP_USER;
            $flags |= HTTP_URL_STRIP_PASS;
        }
        
        // Parse the original URL
        $parse_url = parse_url($url);
        
        // Scheme and Host are always replaced
        if (isset($parts['scheme'])) {
            $parse_url['scheme'] = $parts['scheme'];
        }
        if (isset($parts['host'])) {
            $parse_url['host'] = $parts['host'];
        }
        
        // (If applicable) Replace the original URL with it's new parts
        if ($flags & HTTP_URL_REPLACE) {
            foreach ($keys as $key) {
                if (isset($parts[$key])) {
                    $parse_url[$key] = $parts[$key];
                }
            }
        } else {
            // Join the original URL path with the new path
            if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH)) {
                if (isset($parse_url['path'])) {
                    $parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
                } else {
                    $parse_url['path'] = $parts['path'];
                }
            }
            
            // Join the original query string with the new query string
            if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY)) {
                if (isset($parse_url['query'])) {
                    $parse_url['query'] .= '&' . $parts['query'];
                } else {
                    $parse_url['query'] = $parts['query'];
                }
            }
        }
        
        // Strips all the applicable sections of the URL
        // Note: Scheme and Host are never stripped
        foreach ($keys as $key) {
            if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key))) {
                unset($parse_url[$key]);
            }
        }
        
        $new_url = $parse_url;
        
        return
             ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
            .((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') .'@' : '')
            .((isset($parse_url['host'])) ? $parse_url['host'] : '')
            .((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
            .((isset($parse_url['path'])) ? $parse_url['path'] : '')
            .((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
            .((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '')
        ;
    }
}

if(!function_exists('http_build_query')) {
    function http_build_query($data,$prefix=null,$sep='',$key='') {
        $ret = array();
        foreach((array)$data as $k => $v) {
            $k    = urlencode($k);
            if(is_int($k) && $prefix != null) {
                $k    = $prefix.$k;
            };
            if(!empty($key)) {
                $k    = $key."[".$k."]";
            };
            
            if(is_array($v) || is_object($v)) {
                array_push($ret,http_build_query($v,"",$sep,$k));
            } else {
                array_push($ret,$k."=".urlencode($v));
            };
        };
        
        if(empty($sep)) {
            $sep = ini_get("arg_separator.output");
        };
        
        return    implode($sep, $ret);
    };
};
