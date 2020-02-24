<?php


namespace Okay\Core;


use Okay\Core\Modules\Modules;
use Psr\Log\LoggerInterface;

class BackendTranslations
{
    
    private $_logger;
    private $_modules;
    private $_initializedLang;
    
    public function __construct(LoggerInterface $logger, Modules $modules)
    {
        $this->_logger = $logger;
        $this->_modules = $modules;
    }
    
    public function initTranslations($langLabel = 'en')
    {
        if ($this->_initializedLang === $langLabel) {
            return;
        }
        // Перевод админки
        $lang = [];
        $file = "backend/lang/" .$langLabel . ".php";
        if (!file_exists($file)) {
            foreach (glob("backend/lang/??.php") as $f) {
                $file = "backend/lang/" . pathinfo($f, PATHINFO_FILENAME) . ".php";
                break;
            }
        }
        require_once($file);
        foreach ($lang as $var=>$translation) {
            $this->addTranslation($var, $translation);
        }

        foreach ($this->_modules->getRunningModules() as $runningModule) {
            foreach ($this->_modules->getModuleBackendTranslations($runningModule['vendor'], $runningModule['module_name'], $langLabel) as $var => $translation) {
                $this->addTranslation($var, $translation);
            }
        }

        $this->_initializedLang = $langLabel;
    }

    public function getTranslation($var)
    {
        if (isset($this->$var) && !is_object($this->$var)) {
            return $this->$var;
        } else {
            return false;
        }
    }

    /**
     * @param $var
     * @param $translation
     * добавление перевода к уже существующему набору
     */
    public function addTranslation($var, $translation)
    {
        $var = preg_replace('~[^\w]~', '', $var);
        $this->$var = $translation;
    }
}
