<?php


namespace Okay\Helpers\MetadataHelpers;


use Okay\Core\EntityFactory;
use Okay\Core\FrontTranslations;
use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Entities\PagesEntity;

class AllProductsMetadataHelper extends CommonMetadataHelper
{
    
    public function __construct()
    {
        parent::__construct();
        
        if (!$this->design->getVar('keyword')) {
            $entityFactory = $this->SL->getService(EntityFactory::class);
            /** @var PagesEntity $pagesEntity */
            $pagesEntity = $entityFactory->get(PagesEntity::class);
            $this->page = $pagesEntity->get('all-products');
        }
    }

    public function getH1()
    {
        if ($keyword = $this->design->getVar('keyword')) {
            /** @var FrontTranslations $translations */
            $translations = $this->SL->getService(FrontTranslations::class);
            $h1 = $translations->getTranslation('general_search') . ' ' . $keyword;
        } else {
            $h1 = parent::getH1();
        }

        $h1 = $this->compileMetadata($h1);
        return ExtenderFacade::execute(__METHOD__, $h1, func_get_args());
    }

    public function getMetaTitle()
    {
        if ($keyword = $this->design->getVar('keyword')) {
            /** @var FrontTranslations $translations */
            $translations = $this->SL->getService(FrontTranslations::class);
            $metaTitle = $translations->getTranslation('general_search') . ' ' . $keyword;
        } else {
            $metaTitle = parent::getMetaTitle();
        }

        $isAllPages = $this->design->getVar('is_all_pages');
        $currentPageNum = $this->design->getVar('current_page_num');

        if ((int)$currentPageNum > 1 && $isAllPages !== true) {
            /** @var FrontTranslations $translations */
            $translations = $this->SL->getService(FrontTranslations::class);
            $metaTitle .= $translations->getTranslation('meta_page') . ' ' . $currentPageNum;
        }

        $metaTitle = $this->compileMetadata($metaTitle);
        return ExtenderFacade::execute(__METHOD__, $metaTitle, func_get_args());
    }

    public function getMetaKeywords()
    {
        if ($keyword = $this->design->getVar('keyword')) {
            /** @var FrontTranslations $translations */
            $translations = $this->SL->getService(FrontTranslations::class);
            $metaKeywords = $translations->getTranslation('general_search') . ' ' . $keyword;
        } else {
            $metaKeywords = parent::getMetaTitle();
        }

        $metaKeywords = $this->compileMetadata($metaKeywords);
        return ExtenderFacade::execute(__METHOD__, $metaKeywords, func_get_args());
    }

    public function getMetaDescription()
    {
        if ($keyword = $this->design->getVar('keyword')) {
            /** @var FrontTranslations $translations */
            $translations = $this->SL->getService(FrontTranslations::class);
            $metaDescription = $translations->getTranslation('general_search') . ' ' . $keyword;
        } else {
            $metaDescription = parent::getMetaTitle();
        }

        $metaDescription = $this->compileMetadata($metaDescription);
        return ExtenderFacade::execute(__METHOD__, $metaDescription, func_get_args());
    }
    
}