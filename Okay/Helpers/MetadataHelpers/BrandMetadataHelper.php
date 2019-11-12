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

class BrandMetadataHelper extends CommonMetadataHelper
{
 
    private $metaArray = [];
    private $seoFilterPattern;
    private $metaDelimiter = ', ';
    private $autoMeta;

    public function getH1()
    {
        $brand = $this->design->getVar('brand');
        $filterAutoMeta = $this->getFilterAutoMeta();

        if ($pageH1 = parent::getH1()) {
            $autoH1 = $pageH1;
        } elseif (!empty($filterAutoMeta->h1)) {
            $autoH1 = $brand->name . ' ' . $filterAutoMeta->h1;
        } else {
            $autoH1 = $brand->name;
        }
        
        $h1 = $this->compileMetadata($autoH1);
        return ExtenderFacade::execute(__METHOD__, $h1, func_get_args());
    }
    
    public function getDescription()
    {
        $brand = $this->design->getVar('brand');
        $isFilterPage = $this->design->getVar('is_filter_page');
        $isAllPages = $this->design->getVar('is_all_pages');
        $currentPageNum = $this->design->getVar('current_page_num');
        $filterAutoMeta = $this->getFilterAutoMeta();
        
        if ((int)$currentPageNum > 1 || $isAllPages === true) {
            $autoDescription = '';
        } elseif ($pageDescription = parent::getDescription()) {
            $autoDescription = $pageDescription;
        /*} elseif (!empty($filterAutoMeta->description)) {
            $autoDescription = $filterAutoMeta->description;*/
        } elseif ($isFilterPage === false) {
            $autoDescription = $brand->description;
        } else {
            $autoDescription = '';
        }

        $description = $this->compileMetadata($autoDescription);
        return ExtenderFacade::execute(__METHOD__, $description, func_get_args());
    }
    
    public function getMetaTitle()
    {
        $brand = $this->design->getVar('brand');
        $filterAutoMeta = $this->getFilterAutoMeta();
        $isAllPages = $this->design->getVar('is_all_pages');
        $currentPageNum = $this->design->getVar('current_page_num');
        
        if ($pageTitle = parent::getMetaTitle()) {
            $autoMetaTitle = $pageTitle;
        } elseif (!empty($filterAutoMeta->meta_title)) {
            $autoMetaTitle = $brand->meta_title . ' ' . $filterAutoMeta->meta_title;
        } else {
            $autoMetaTitle = $brand->meta_title;
        }

        // Добавим номер страницы к тайтлу
        if ((int)$currentPageNum > 1 && $isAllPages !== true) {
            /** @var FrontTranslations $translations */
            $translations = $this->SL->getService(FrontTranslations::class);
            $autoMetaTitle .= $translations->getTranslation('meta_page') . ' ' . $currentPageNum;
        }
        
        $metaTitle = $this->compileMetadata($autoMetaTitle);
        return ExtenderFacade::execute(__METHOD__, $metaTitle, func_get_args());
    }
    
    public function getMetaKeywords()
    {
        $brand = $this->design->getVar('brand');
        $filterAutoMeta = $this->getFilterAutoMeta();
        
        if ($pageKeywords = parent::getMetaKeywords()) {
            $autoMetaKeywords = $pageKeywords;
        } elseif (!empty($filterAutoMeta->meta_keywords)) {
            $autoMetaKeywords = $brand->meta_keywords . ' ' . $filterAutoMeta->meta_keywords;
        } else {
            $autoMetaKeywords = $brand->meta_keywords;
        }

        $metaKeywords = $this->compileMetadata($autoMetaKeywords);
        return ExtenderFacade::execute(__METHOD__, $metaKeywords, func_get_args());
    }
    
    public function getMetaDescription()
    {
        $brand = $this->design->getVar('brand');
        $filterAutoMeta = $this->getFilterAutoMeta();
        
        if ($pageMetaDescription = parent::getMetaDescription()) {
            $autoMetaDescription = $pageMetaDescription;
        } elseif (!empty($filterAutoMeta->meta_description)) {
            $autoMetaDescription = $brand->meta_description . ' ' . $filterAutoMeta->meta_description;
        } else {
            $autoMetaDescription = $brand->meta_description;
        }

        $metaDescription = $this->compileMetadata($autoMetaDescription);
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

        $brand = $this->design->getVar('brand');
        
        $this->parts = [
            '{$brand}' => ($brand->name ? $brand->name : ''),
            '{$sitename}' => ($this->settings->get('site_name') ? $this->settings->get('site_name') : ''),
        ];
        
        return $this->parts = ExtenderFacade::execute(__METHOD__, $this->parts, func_get_args());
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