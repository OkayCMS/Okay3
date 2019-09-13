<?php

namespace Okay\Modules\OkayCMS\Integration1C\Integration;


use Okay\Core\Config;
use Okay\Core\Request;
use Okay\Core\Managers;
use Okay\Core\Settings;
use Okay\Core\Translit;
use Okay\Core\Database;
use Okay\Core\DataCleaner;
use Okay\Core\QueryFactory;
use Okay\Core\EntityFactory;
use Okay\Entities\OrdersEntity;
use Okay\Entities\ManagersEntity;
use Okay\Entities\PurchasesEntity;

class Integration1C
{
    
    public $fullUpdate = true;     // Обновлять все данные при каждой синхронизации
    
    public $deleteAll	= false;    // Очищать всю базу товаров при каждой выгрузке true:false PLEASE BE CAREFULL
    
    public $brandOptionName = 'Производитель'; // Название параметра товара, используемого как бренд
    
    public $onlyEnabledCurrencies = false; // Учитывает все валюты(false) или только включенные(true)
    
    public $stock_from_1c = true;   // TRUE Учитывать количество товара из 1с FALSE установить доступность в бесконечное количество
    
    public $importProductsOnly = true;   // TRUE Импортировать только товары, без услуг и прочего (ВидНоменклатуры == Товар)
    
    public $startTime;             // В начале скрипта засекли время, нужно для определения когда закончиться max_execution_time
    
    private $dir;                    // Папка для хранения временных файлов синхронизации
    
    public $maxExecTime;
    
    /**
     * @var array Какие расширения файлов можно загружать из 1С
     */
    public $allowed_extensions = array(
        'xml',
        'jpg',
        'jpeg',
        'png',
        'gif',
        'pdf',
        'zip',
        'rar',
        'xls',
        'doc',
        'xlsx',
        'docx',
    );

    /** @var Managers*/
    public $managers;
    
    /** @var EntityFactory */
    public $entityFactory;
    
    /** @var DataCleaner */
    public $dataCleaner;
    
    /** @var QueryFactory */
    public $queryFactory;
    
    /** @var Settings */
    public $settings;
    
    /** @var Config */
    public $config;
    
    /** @var Translit */
    public $translit;
    
    /** @var Database */
    public $db;
    
    public function __construct(
        Managers $managers,
        EntityFactory $entityFactory,
        DataCleaner $dataCleaner,
        Database $database,
        QueryFactory $queryFactory,
        Request $request,
        Settings $settings,
        Config $config,
        Translit $translit
    ) {
        $this->managers       = $managers;
        $this->entityFactory  = $entityFactory;
        $this->dataCleaner    = $dataCleaner;
        $this->db             = $database;
        $this->queryFactory   = $queryFactory;
        $this->settings       = $settings;
        $this->config         = $config;
        $this->translit       = $translit;
        $this->startTime      = $request->getStartTime();
        
        $this->dir        = dirname(__DIR__) . '/temp/';

        $this->maxExecTime = min(30, @ini_get("max_execution_time"));
        if (empty($this->maxExecTime)) {
            $this->maxExecTime = 30;
        }
    }

    public function getTmpDir()
    {
        return $this->dir;
    }
    
    public function checkAuth()
    {
        /** @var ManagersEntity $managersEntity */
        $managersEntity = $this->entityFactory->get(ManagersEntity::class);

        if (!isset($_SERVER['PHP_AUTH_USER']) && (!empty($_SERVER["REMOTE_USER"]) || !empty($_SERVER['REDIRECT_REMOTE_USER']))) {

            $remoteUser = !empty($_SERVER["REMOTE_USER"]) ? $_SERVER["REMOTE_USER"] : $_SERVER['REDIRECT_REMOTE_USER'];
            
            $remoteUser = base64_decode(substr($remoteUser,6)) ;
            if ((strlen($remoteUser) > 0) || ( strcasecmp($remoteUser, ":" ) > 0)) {
                list($name, $password) = explode(':', $remoteUser);
                $_SERVER['PHP_AUTH_USER'] = $name;
                $_SERVER['PHP_AUTH_PW']   = $password;
            }
        }
        
        if (!isset($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
            return false;
        }

        $login = $_SERVER['PHP_AUTH_USER'];
        $pass  = $_SERVER['PHP_AUTH_PW'];
        
        if (!$manager1c = $managersEntity->get((string)$login)) {
            return false;
        }
        
        if (!$this->managers->checkPassword($pass, $manager1c->password)) {
            return false;
        }
        
        if (!$this->managers->access('integration_1c', $manager1c)) {
            return false;
        }
        
        return true;
    }
    
    public function uploadFile($xml_file)
    {
        
        // Создаем дерево категорий для файла
        $file_path = pathinfo(str_replace($this->dir, '', $xml_file), PATHINFO_DIRNAME);
        if ($file_path !== false && $file_path != '.') {
            $path = explode('/', $file_path);
            $temp_path = '';
            foreach($path as $p) {
                mkdir($this->dir . $temp_path . $p);
                $temp_path .= $p.'/';
            }
        }
        
        $f = fopen($xml_file, 'ab');
        fwrite($f, file_get_contents('php://input'));
        fclose($f);
    }
    
    public function getFullPath($filename)
    {
        return $this->dir . preg_replace('~\.\./~', '', $filename);
    }
    
    public function validateFile($xml_file)
    {
        $is_valid = true;
        $ext = pathinfo($xml_file, PATHINFO_EXTENSION);

        if (empty($ext) || !in_array($ext, $this->allowed_extensions)) {
            $is_valid = false;
        }

        if ($is_valid === true && filesize($xml_file) == 0) {
            $is_valid = false;
        }
        
        // Удалим файл, если он не валидный, чтобы не грузили все подряд
        if ($is_valid === false) {
            unlink($xml_file);
        }
        
        return $is_valid;
    }

    public function setToStorage($param, $value)
    {
        $_SESSION["integration_1c"][$param] = $value;
    }
    
    public function getFromStorage($param)
    {
        return (isset($_SESSION["integration_1c"][$param]) ? $_SESSION["integration_1c"][$param] : null);
    }
    
    public function clearStorage()
    {
        unset($_SESSION["integration_1c"]);
    }
    
    public function flushDatabase()
    {
        $this->dataCleaner->clearCatalogData();

        $sql = $this->queryFactory->newSqlQuery();
        $sql->setStatement("TRUNCATE TABLE `".OrdersEntity::getTable()."`");
        $this->db->query($sql);

        $sql = $this->queryFactory->newSqlQuery();
        $sql->setStatement("TRUNCATE TABLE `".PurchasesEntity::getTable()."`");
        $this->db->query($sql);
    }

    public function rrmdir($dir, $level = 0)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object == "." || $object == "..") {
                continue;
            }

            if (is_dir($dir."/".$object)) {
                $this->rrmdir($dir . "/" . $object, $level+1);
            } else {
                unlink($dir . "/" . $object);
            }
        }

        if ($level > 0) {
            rmdir($dir);
        }
    }
}
