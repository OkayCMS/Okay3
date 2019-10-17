<?php


namespace Okay\Core;


use Okay\Core\Modules\Modules;
use Okay\Entities\TranslationsEntity;

class FrontTranslations
{
    
    private $_debugTranslation;
    
    public function __construct(EntityFactory $entityFactory, Languages $languages, Modules $modules, $debugTranslation = false)
    {
        $langLabel = $languages->getLangLabel();
        $this->_debugTranslation = (bool)$debugTranslation;
        
        /** @var TranslationsEntity $translations */
        $translations = $entityFactory->get(TranslationsEntity::class);
        foreach ($translations->find(['lang' => $langLabel]) as $var=>$value) {
            $this->$var = $value;
        }
        
        // Дополняем переводы из активных модулей 
        foreach ($modules->getRunningModules() as $runningModule) {
            foreach ($modules->getModuleFrontTranslations($runningModule['vendor'], $runningModule['module_name'], $langLabel) as $var=>$value) {
                $this->$var = $value;
            }
        }
    }

    // Если включили дебаг переводов, выведим соответствующее сообщение на неизвестный перевод
    public function __get($var)
    {
        if ($this->_debugTranslation === true) {
            return '<b style="color: red!important;">Incorrect $lang->' . $var . '</b>';
        }
    }
    
    public function getTranslation($var)
    {
        return $this->$var;
    }
    
    public function addTranslation($var, $translation)
    {
        $var = preg_replace('~[^\w]~', '', $var);
        $this->$var = $translation;
    }
}
