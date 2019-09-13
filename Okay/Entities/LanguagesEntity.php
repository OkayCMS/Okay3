<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;
use Okay\Core\Languages;

class LanguagesEntity extends Entity
{
    protected static $fields = [
        'id',
        'label',
        'href_lang',
        'enabled',
        'position',
    ];

    protected static $langFields = [
        'name',
    ];

    protected static $defaultOrderFields = [
        'position ASC',
    ];

    protected static $table = '__languages';
    protected static $langObject = 'language';
    protected static $langTable = 'languages';
    protected static $tableAlias = 'le';
    
    private $allLanguages = [];
    private $mainLanguage;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->initLanguages();
    }
    
    private function initLanguages()
    {
        $this->mappedBy('id');
        $this->allLanguages = [];
        $this->allLanguages = parent::find();
        
        $this->mainLanguage = reset($this->allLanguages);
    }

    public function get($id)
    {
        if (empty($this->allLanguages)) {
            $this->initLanguages();
        }
        
        if (!empty($id)) {
            if (is_int($id) && isset($this->allLanguages[$id])) {
                return $this->allLanguages[$id];
            } elseif (is_string($id)) {
                foreach ($this->allLanguages as $language) {
                    if ($language->label == $id) {
                        return $language;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param $langId
     * @return object|null
     * @throws \Exception
     * Метод возвращает язык, с массивом его переводов на другие языки
     */
    public function getMultiLanguage($langId)
    {
        $this->initLanguages();
        
        $currentLangId = $this->lang->getLangId();
        $result = parent::get($langId);
        
        foreach ($this->allLanguages as $l) {
            $this->lang->setLangId($l->id);
            $this->mappedBy('id');
            $lang = parent::get((int)$langId);
            $result->names[$l->id] = $lang->name;
            $results[$result->id] = $result;
        }

        $this->lang->setLangId($currentLangId);
        return $result;
    }
    
    // Метод по сути ничего не фильтрует, только возвращает все языки
    public function find(array $filter = [])
    {
        if (empty($this->allLanguages)) {
            $this->initLanguages();
        }
        return $this->allLanguages;
    }
    
    /*Выборка первого языка сайта*/
    public function getMainLanguage()
    {
        return $this->mainLanguage;
    }

    public function update($ids, $language)
    {
        parent::update($ids, $language);
        
        $this->initLanguages();
        return true;
    }

    /*Добавление языка*/
    public function add($language)
    {
        
        $language = (object)$language;
        $langId = parent::add($language);
        
        /** @var Languages $languagesCore */
        $languagesCore = $this->serviceLocator->getService(Languages::class);

        /** @var TranslationsEntity $translations */
        $translations = $this->entity->get(TranslationsEntity::class);
        
        if (isset($langId)) {

            $translations->copyTranslations($this->mainLanguage->label, $language->label);
            
            if ($entitiesLangInfo = $languagesCore->getEntitiesLangInfo()) {
                foreach ($entitiesLangInfo as $entityLangInfo) {
                    $sql = $this->queryFactory->newSqlQuery();
                    $sql->setStatement('INSERT INTO __lang_' . $entityLangInfo->langTable . ' (' . implode(',', $entityLangInfo->fields) . ', ' . $entityLangInfo->object . '_id, lang_id)
                                    SELECT ' . implode(',', $entityLangInfo->fields) . ', id, ' . $langId . '
                                    FROM ' . $entityLangInfo->table);
                    $this->db->query($sql);
                }
            }
            
            if (isset($this->mainLanguage) && !empty($this->mainLanguage)) {
                $settings = $this->settings->getSettings($this->mainLanguage->id);
                if (!empty($settings)) {
                    foreach ($settings as $s) {
                        $sql = $this->queryFactory->newSqlQuery();
                        $sql->setStatement("REPLACE INTO `__settings_lang` SET 
                                                    `lang_id`=:lang_id,
                                                    `param`=:param,
                                                    `value`=:value
                                                    ");
                        $sql->bindValue('land_id', $this->db->escape($langId));
                        $sql->bindValue('param', $this->db->escape($s->param));
                        $sql->bindValue('value', $this->db->escape($s->value));
                        $this->db->query($sql);
                    }
                }
            } else {
                $sql = $this->queryFactory->newSqlQuery();
                $sql->setStatement("UPDATE `__settings_lang` SET `lang_id`=:lang_id");
                $sql->bindValue('lang_id', $this->db->escape($langId));
                $this->db->query($sql);
            }
        }
        $this->initLanguages();
        return $langId;
    }

    /*Удаление языка*/
    public function delete($ids)
    {
        /** @var TranslationsEntity $translationsEntity */
        $translationsEntity = $this->entity->get(TranslationsEntity::class);
        
        $ids = (array)$ids;
        $languages = $this->find();
        if (count($languages) == count($ids)) {
            $first = $this->getMainLanguage();
        }
        
        foreach ($ids as $id) {

            // Удалим переводы фронта
            $lang = $this->get((int)$id);
            $translationsEntity->deleteLang($lang->label);
            
            $saveMain = (isset($first) && $id == $first->id);
            if (empty($id)) {
                continue;
            }
            
            $id = (int)$id;
            parent::delete($id);
            
            $tables = $this->getLangTables();
            
            foreach ($tables as $table) {
                $delete = $this->queryFactory->newDelete();
                $delete->from($table)->where("lang_id={$id}");
                $this->db->query($delete);
            }

            if (!$saveMain) {
                $delete = $this->queryFactory->newDelete();
                $delete->from('__settings_lang')->where("lang_id={$id}");
                $this->db->query($delete);
            } else {
                $update = $this->queryFactory->newUpdate();
                $update->table('__settings_lang')
                    ->set('lang_id', 0)
                    ->where("lang_id={$id}");
                $this->db->query($update);
            }
        }
        $this->initLanguages();
        return true;
    }
    
    private function getLangTables()
    {
        $sql = $this->queryFactory->newSqlQuery();
        $sql->setStatement("SHOW TABLES LIKE '%__lang\_%'");
        $this->db->query($sql);

        $tables = [];
        while ($table = $this->db->result()) {
            $tables[] = reset($table);
        }
        return $tables;
    }

}
