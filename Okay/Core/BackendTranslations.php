<?php


namespace Okay\Core;


use Psr\Log\LoggerInterface;

class BackendTranslations
{
    
    private $_logger;
    private $_initializedLang;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->_logger = $logger;
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
        $this->_initializedLang = $langLabel;
    }

    public function getTranslation($var)
    {
        return $this->$var;
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
