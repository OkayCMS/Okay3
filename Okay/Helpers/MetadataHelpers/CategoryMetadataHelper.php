<?php


namespace Okay\Helpers\MetadataHelpers;


use Okay\Core\EntityFactory;
use Okay\Core\FrontTranslations;
use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Entities\FeaturesAliasesValuesEntity;
use Okay\Entities\FeaturesEntity;
use Okay\Entities\FeaturesValuesAliasesValuesEntity;
use Okay\Entities\SEOFilterPatternsEntity;
use Okay\Helpers\FilterHelper;

class CategoryMetadataHelper extends CommonMetadataHelper
{
 
    private $metaArray = [];
    private $seoFilterPattern;
    private $metaDelimiter = ', ';
    private $autoMeta;

    /**
     * @inheritDoc
     */
    public function getH1Template()
    {
        $category = $this->design->getVar('category');
        $seoFilterPattern = $this->getSeoFilterPattern();
        $filterAutoMeta = $this->getFilterAutoMeta();

        $categoryH1 = !empty($category->name_h1) ? $category->name_h1 : $category->name;
        if ($pageH1 = parent::getH1Template()) {
            $h1 = $pageH1;
        } elseif (!empty($seoFilterPattern->h1)) {
            $h1 = $seoFilterPattern->h1;
        } elseif (!empty($filterAutoMeta->h1)) {
            $h1 = $categoryH1 . ' ' . $filterAutoMeta->h1;
        } else {
            $h1 = $categoryH1;
        }

        return ExtenderFacade::execute(__METHOD__, $h1, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function getDescriptionTemplate()
    {
        $category = $this->design->getVar('category');
        $isFilterPage = $this->design->getVar('is_filter_page');
        $isAllPages = $this->design->getVar('is_all_pages');
        $currentPageNum = $this->design->getVar('current_page_num');
        $seoFilterPattern = $this->getSeoFilterPattern();
        $filterAutoMeta = $this->getFilterAutoMeta();

        if ((int)$currentPageNum > 1 || $isAllPages === true) {
            $description = '';
        } elseif ($pageDescription = parent::getDescriptionTemplate()) {
            $description = $pageDescription;
        } elseif (!empty($seoFilterPattern->description)) {
            $description = $seoFilterPattern->description;
        /*} elseif (!empty($filterAutoMeta->description)) {
            $description = $filterAutoMeta->description;*/
        } elseif ($isFilterPage === false) {
            $description = $category->description;
        } else {
            $description = '';
        }

        return ExtenderFacade::execute(__METHOD__, $description, func_get_args());
    }
    
    public function getMetaTitleTemplate() // todo проверить как отработают экстендеры если их навесить на этот метод (где юзается parent::getMetaTitle())
    {
        $category = $this->design->getVar('category');
        $seoFilterPattern = $this->getSeoFilterPattern();
        $filterAutoMeta = $this->getFilterAutoMeta();
        $isAllPages = $this->design->getVar('is_all_pages');
        $currentPageNum = $this->design->getVar('current_page_num');
        
        if ($pageTitle = parent::getMetaTitleTemplate()) {
            $metaTitle = $pageTitle;
        } elseif (!empty($seoFilterPattern->meta_title)) {
            $metaTitle = $seoFilterPattern->meta_title;
        } elseif (!empty($filterAutoMeta->meta_title)) {
            $metaTitle = $category->meta_title . ' ' . $filterAutoMeta->meta_title;
        } else {
            $metaTitle = $category->meta_title;
        }

        // Добавим номер страницы к тайтлу
        if ((int)$currentPageNum > 1 && $isAllPages !== true) {
            /** @var FrontTranslations $translations */
            $translations = $this->SL->getService(FrontTranslations::class);
            $metaTitle .= $translations->getTranslation('meta_page') . ' ' . $currentPageNum;
        }
        
        return ExtenderFacade::execute(__METHOD__, $metaTitle, func_get_args());
    }
    
    public function getMetaKeywordsTemplate()
    {
        $category = $this->design->getVar('category');
        $seoFilterPattern = $this->getSeoFilterPattern();
        $filterAutoMeta = $this->getFilterAutoMeta();
        
        if ($pageKeywords = parent::getMetaKeywordsTemplate()) {
            $metaKeywords = $pageKeywords;
        } elseif (!empty($seoFilterPattern->meta_keywords)) {
            $metaKeywords = $seoFilterPattern->meta_keywords;
        } elseif (!empty($filterAutoMeta->meta_keywords)) {
            $metaKeywords = $category->meta_keywords . ' ' . $filterAutoMeta->meta_keywords;
        } else {
            $metaKeywords = $category->meta_keywords;
        }

        return ExtenderFacade::execute(__METHOD__, $metaKeywords, func_get_args());
    }
    
    public function getMetaDescriptionTemplate()
    {
        $category = $this->design->getVar('category');
        $seoFilterPattern = $this->getSeoFilterPattern();
        $filterAutoMeta = $this->getFilterAutoMeta();
        
        if ($pageMetaDescription = parent::getMetaDescriptionTemplate()) {
            $metaDescription = $pageMetaDescription;
        } elseif (!empty($seoFilterPattern->meta_description)) {
            $metaDescription = $seoFilterPattern->meta_description;
        } elseif (!empty($filterAutoMeta->meta_description)) {
            $metaDescription = $category->meta_description . ' ' . $filterAutoMeta->meta_description;
        } else {
            $metaDescription = $category->meta_description;
        }

        return ExtenderFacade::execute(__METHOD__, $metaDescription, func_get_args());
    }

    private function getFilterAutoMeta()
    {
        /** @var FilterHelper $filterHelper */
        $filterHelper = $this->SL->getService(FilterHelper::class);
        if ($filterHelper->isSetCanonical() === true) {
            return null;
        }
        
        if (empty($this->autoMeta)) {
            
            $autoMeta = [
                'h1' => '',
                'meta_title' => '',
                'meta_keywords' => '',
                'meta_description' => '',
                'description' => '',
            ];

            $metaArray = $this->getMetaArray();
            if (!empty($metaArray)) {
                foreach ($metaArray as $type => $_meta_array) {
                    switch ($type) {
                        case 'brand': // no break
                        case 'filter':
                        {
                            $autoMeta['h1'] = $autoMeta['meta_title'] = $autoMeta['meta_keywords'] = $autoMeta['meta_description'] = $autoMeta['description'] = implode($this->metaDelimiter, $_meta_array);
                            break;
                        }
                        case 'features_values':
                        {
                            foreach ($_meta_array as $f_id => $f_array) {
                                $autoMeta['h1'] .= (!empty($autoMeta['h1']) ? $this->metaDelimiter : '') . implode($this->metaDelimiter, $f_array);
                                $autoMeta['meta_title'] .= (!empty($autoMeta['meta_title']) ? $this->metaDelimiter : '') . implode($this->metaDelimiter, $f_array);
                                $autoMeta['meta_keywords'] .= (!empty($autoMeta['meta_keywords']) ? $this->metaDelimiter : '') . implode($this->metaDelimiter, $f_array);
                                $autoMeta['meta_description'] .= (!empty($autoMeta['meta_description']) ? $this->metaDelimiter : '') . implode($this->metaDelimiter, $f_array);
                                $autoMeta['description'] .= (!empty($autoMeta['description']) ? $this->metaDelimiter : '') . implode($this->metaDelimiter, $f_array);
                            }
                            break;
                        }
                    }
                }
            }
            $this->autoMeta = (object)$autoMeta;
        }

        return $this->autoMeta;
    }
    
    /**
     * @inheritDoc
     */
    protected function getParts()
    {

        if (!empty($this->parts)) {
            return $this->parts; // no ExtenderFacade
        }
        
        $category = $this->design->getVar('category');
        
        $this->parts = [
            '{$category}' => ($category->name ? $category->name : ''),
            '{$category_h1}' => ($category->name_h1 ? $category->name_h1 : ''),
            '{$sitename}' => ($this->settings->get('site_name') ? $this->settings->get('site_name') : ''),
        ];

        $selectedFilters = $this->design->getVar('selected_filters');
        
        /** @var EntityFactory $entityFactory */
        $entityFactory = $this->SL->getService(EntityFactory::class);
        
        if (!empty($selectedFilters)) {
            /** @var FeaturesAliasesValuesEntity $featuresAliasesValuesEntity */
            $featuresAliasesValuesEntity = $entityFactory->get(FeaturesAliasesValuesEntity::class);

            /** @var FeaturesValuesAliasesValuesEntity $featuresValuesAliasesValuesEntity */
            $featuresValuesAliasesValuesEntity = $entityFactory->get(FeaturesValuesAliasesValuesEntity::class);
            
            $featuresIds = array_keys($selectedFilters);
            
            foreach ($featuresAliasesValuesEntity->find(array('feature_id'=>$featuresIds)) as $fv) {
                $this->parts['{$f_alias_'.$fv->variable.'}'] = $fv->value;
            }

            $aliasesValuesFilter['feature_id'] = $featuresIds;
            // Если только одно значение одного свойства, получим для него все алиасы значения
            if (count($featuresIds) == 1 && (count($translits = reset($selectedFilters))) == 1) {
                $aliasesValuesFilter['translit'] = reset($translits);
            }
            foreach ($featuresValuesAliasesValuesEntity->find($aliasesValuesFilter) as $ov) {
                $this->parts['{$o_alias_'.$ov->variable.'}'] = $ov->value;
            }
        }

        $metaArray = $this->getMetaArray();

        if (!empty($metaArray['brand']) && count($metaArray['brand']) == 1 && empty($metaArray['features_values'])) {
            $this->parts['{$brand}'] = reset($metaArray['brand']);
        } elseif (!empty($metaArray['features_values']) && count($metaArray['features_values']) == 1 && empty($metaArray['brand'])) {

            /** @var FeaturesEntity $featuresEntity */
            $featuresEntity = $entityFactory->get(FeaturesEntity::class);
            
            reset($metaArray['features_values']);
            $featureId = key($metaArray['features_values']);
            $feature = $featuresEntity->get((int)$featureId);

            $this->parts['{$feature_name}'] = $feature->name;
            $this->parts['{$feature_val}'] = implode(', ', reset($metaArray['features_values']));
        }
        
        return $this->parts = ExtenderFacade::execute(__METHOD__, $this->parts, func_get_args());
    }

    private function getSeoFilterPattern()
    {
        /** @var FilterHelper $filterHelper */
        $filterHelper = $this->SL->getService(FilterHelper::class);
        if ($filterHelper->isSetCanonical() === true) {
            return null;
        }
        
        if (empty($this->seoFilterPattern)) {
            $category = $this->design->getVar('category');

            /** @var EntityFactory $entityFactory */
            $entityFactory = $this->SL->getService(EntityFactory::class);

            /** @var SEOFilterPatternsEntity $SEOFilterPatternsEntity */
            $SEOFilterPatternsEntity = $entityFactory->get(SEOFilterPatternsEntity::class);

            $metaArray = $this->getMetaArray();

            if (!empty($metaArray['brand']) && count($metaArray['brand']) == 1 && empty($metaArray['features_values'])) {
                $seoFilterPatterns = $SEOFilterPatternsEntity->find(['category_id' => $category->id, 'type' => 'brand']);
                $this->seoFilterPattern = reset($seoFilterPatterns);

            } elseif (!empty($metaArray['features_values']) && count($metaArray['features_values']) == 1 && empty($metaArray['brand'])) {

                /** @var FeaturesEntity $featuresEntity */
                $featuresEntity = $entityFactory->get(FeaturesEntity::class);

                $seoFilterPatterns = [];
                foreach ($SEOFilterPatternsEntity->find(['category_id' => $category->id, 'type' => 'feature']) as $p) {
                    $key = 'feature' . (!empty($p->feature_id) ? '_' . $p->feature_id : '');
                    $seoFilterPatterns[$key] = $p;
                }

                reset($metaArray['features_values']);
                $featureId = key($metaArray['features_values']);
                $feature = $featuresEntity->get((int)$featureId);

                // Определяем какой шаблон брать, для категории + определенное свойство, или категории и любое свойство
                if (isset($seoFilterPatterns['feature_' . $feature->id])) {
                    $this->seoFilterPattern = $seoFilterPatterns['feature_' . $feature->id];
                } elseif (isset($seoFilterPatterns['feature'])) {
                    $this->seoFilterPattern = $seoFilterPatterns['feature'];
                }
            }
        }
        return $this->seoFilterPattern;
    }

    private function getMetaArray()
    {
        if (empty($this->metaArray)) {
            /** @var FilterHelper $filterHelper */
            $filterHelper = $this->SL->getService(FilterHelper::class);
            $this->metaArray = $filterHelper->getMetaArray();
        }
        return $this->metaArray;
    }
    
}