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

class PostMetadataHelper extends CommonMetadataHelper
{
 

    public function getH1()
    {
        $post = $this->design->getVar('post');

        if ($pageH1 = parent::getH1()) {
            $autoH1 = $pageH1;
        } else {
            $autoH1 = $post->name;
        }
        
        $h1 = $this->compileMetadata($autoH1);
        return ExtenderFacade::execute(__METHOD__, $h1, func_get_args());
    }
    
    public function getDescription()
    {
        $post = $this->design->getVar('post');
        
        if ($pageDescription = parent::getDescription()) {
            $autoDescription = $pageDescription;
        } else {
            $autoDescription = $post->description;
        }

        $description = $this->compileMetadata($autoDescription);
        return ExtenderFacade::execute(__METHOD__, $description, func_get_args());
    }
    
    public function getMetaTitle()
    {
        $post = $this->design->getVar('post');
        if ($pageTitle = parent::getMetaTitle()) {
            $autoMetaTitle = $pageTitle;
        } else {
            $autoMetaTitle = $post->meta_title;
        }
        
        $metaTitle = $this->compileMetadata($autoMetaTitle);
        return ExtenderFacade::execute(__METHOD__, $metaTitle, func_get_args());
    }
    
    public function getMetaKeywords()
    {
        $post = $this->design->getVar('post');
        
        if ($pageKeywords = parent::getMetaKeywords()) {
            $autoMetaKeywords = $pageKeywords;
        } else {
            $autoMetaKeywords = $post->meta_keywords;
        }

        $metaKeywords = $this->compileMetadata($autoMetaKeywords);
        return ExtenderFacade::execute(__METHOD__, $metaKeywords, func_get_args());
    }
    
    public function getMetaDescription()
    {
        $post = $this->design->getVar('post');
        
        if ($pageMetaDescription = parent::getMetaDescription()) {
            $autoMetaDescription = $pageMetaDescription;
        } else {
            $autoMetaDescription = $post->meta_description;
        }

        $metaDescription = $this->compileMetadata($autoMetaDescription);
        return ExtenderFacade::execute(__METHOD__, $metaDescription, func_get_args());
    }
}