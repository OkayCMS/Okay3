<?php


namespace Okay\Core;


use Psr\Log\LoggerInterface;

class BackendTranslations
{
    
    private $_logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->_logger = $logger;
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
        if (isset($this->$var)) {
            $this->_logger->notice("Backend translation var \"{$var}\" already exists");
        }
        
        $var = preg_replace('~[^\w]~', '', $var);
        $this->$var = $translation;
    }
}
