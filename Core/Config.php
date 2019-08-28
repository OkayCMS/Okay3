<?php


namespace Okay\Core;


/**
 * Класс-обертка для конфигурационного файла с настройками магазина
 * В отличие от класса Settings, Config оперирует низкоуровневыми настройками, например найстройками базы данных.
 */
class Config
{

    /*Версия системы*/
    public $version = '3.0.2 Beta';
    /*Тип системы*/
    public $version_type = 'pro';
    
    /*Файл для хранения настроек*/
    public $config_file = 'config/config.php';
    public $config_local_file = 'config/config.local.php';

    public $salt;
    private $vars = array();
    private $local_vars = array();

    /*
     * В конструкторе записываем настройки файла в переменные этого класса
     *  для удобного доступа к ним. Например: $okay->config->db_user
     * */
    public function __construct() {
        
        /*Читаем настройки из дефолтного файла*/
        $ini = parse_ini_file(dirname(dirname(__FILE__)).'/'.$this->config_file);
        /*Записываем настройку как переменную класса*/
        foreach($ini as $var=>$value) {
            $this->vars[$var] = $value;
        }

        /*Заменяем настройки, если есть локальный конфиг*/
        if (file_exists(dirname(dirname(__FILE__)).'/'.$this->config_local_file)) {
            $ini = parse_ini_file(dirname(dirname(__FILE__)) . '/' . $this->config_local_file);
            foreach ($ini as $var => $value) {
                $this->local_vars[$var] = $this->vars[$var] = $value;
            }
        }

        // Вычисляем DOCUMENT_ROOT вручную, так как иногда в нем находится что-то левое
        $localpath=getenv("SCRIPT_NAME");
        $absolutepath=getenv("SCRIPT_FILENAME");
        $_SERVER['DOCUMENT_ROOT']=substr($absolutepath,0,strpos($absolutepath,$localpath));
        
        // Адрес сайта - тоже одна из настроек, но вычисляем его автоматически, а не берем из файла
        $script_dir1 = realpath(dirname(dirname(__FILE__)));
        $script_dir2 = realpath($_SERVER['DOCUMENT_ROOT']);
        $subdir = trim(substr($script_dir1, strlen($script_dir2)), "/\\");
        
        // Подпапка в которую установлен OkayCMS относительно корня веб-сервера
        $this->vars['subfolder'] = $subdir.'/';
        
        // Определяем корневую директорию сайта
        $this->vars['root_dir'] =  dirname(dirname(__FILE__)).'/';
        
        // Максимальный размер загружаемых файлов
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        $memory_limit = (int)(ini_get('memory_limit'));
        $this->vars['max_upload_filesize'] = min($max_upload, $max_post, $memory_limit)*1024*1024;
        
        // Соль (разная для каждой копии сайта, изменяющаяся при изменении config-файла)
        $s = stat(dirname(dirname(__FILE__)).'/'.$this->config_file);
        $this->vars['salt'] = md5(md5_file(dirname(dirname(__FILE__)).'/'.$this->config_file).$s['dev'].$s['ino'].$s['uid'].$s['mtime']);
        
        // Часовой пояс
        if(!empty($this->vars['php_timezone'])) {
            date_default_timezone_set($this->vars['php_timezone']);
        }
    }

    /*Выборка настройки*/
    public function __get($name)
    {
        if ($name == 'root_url') {
            throw new \Exception('Config::root_url is remove. Use Request::getRootUrl()');
        }
        
        if(isset($this->vars[$name])) {
            return $this->vars[$name];
        } else {
            return null;
        }
    }

    /*Запись данных в конфиг*/
    public function __set($name, $value) {
        if(isset($this->vars[$name]) || isset($this->local_vars[$name])) {
            
            // Определяем в каком файле конфига переопределять значения
            if (isset($this->local_vars[$name])) {
                $config_file = $this->config_local_file;
            } else {
                $config_file = $this->config_file;
            }
            
            $conf = file_get_contents(dirname(dirname(__FILE__)).'/'.$config_file);
            $conf = preg_replace("/".$name."\s*=.*\n/i", $name.' = '.$value."\r\n", $conf);
            $cf = fopen(dirname(dirname(__FILE__)).'/'.$config_file, 'w');
            fwrite($cf, $conf);
            fclose($cf);
            $this->vars[$name] = $value;
        }
    }

    /*Формирование токена*/
    public function token($text) {
        return md5($text.$this->salt);
    }

    /*Проверка токена*/
    public function check_token($text, $token) {
        if(!empty($token) && $token === $this->token($text)) {
            return true;
        }
        return false;
    }
    
}
