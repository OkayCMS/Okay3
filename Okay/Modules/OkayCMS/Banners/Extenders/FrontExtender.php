<?php


namespace Okay\Modules\OkayCMS\Banners\Extenders;


use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\Modules\Module;
use Okay\Modules\OkayCMS\Banners\Entities\BannersEntity;
use Okay\Modules\OkayCMS\Banners\Entities\BannersImagesEntity;

class FrontExtender implements ExtensionInterface
{
    
    private $entityFactory;
    private $design;
    private $module;
    private $totalBannersHtml = '';
    private $shortCodesParts = [];
    
    public function __construct(EntityFactory $entityFactory, Design $design, Module $module)
    {
        $this->entityFactory = $entityFactory;
        $this->design = $design;
        $this->module = $module;
        
        $this->init();
    }

    public function assignCurrentBanners()
    {
        $this->design->assign('global_banners', $this->totalBannersHtml);
    }
    
    public function metadataGetParts(array $parts = [])
    {
        $parts = array_merge($parts, $this->shortCodesParts);
        return $parts;
    }
    
    public function init()
    {

        // Устанавливаем директорию HTML из модуля
        $vendor = $this->module->getVendorName(__CLASS__);
        $name = $this->module->getModuleName(__CLASS__);
        
        $moduleTemplateDir = $this->module->generateModuleTemplateDir(
            $vendor,
            $name
        );
        
        $this->design->setModuleTemplatesDir($moduleTemplateDir);
        $this->design->useModuleDir();
        
        $showOnFilter = [];
        if ($category = $this->design->getVar('category')) {
            $showOnFilter['categories'] = $category->id;
        }
        
        if ($brand = $this->design->getVar('brand')) {
            $showOnFilter['brands'] = $brand->id;
        }
        
        if ($page = $this->design->getVar('page')) {
            $showOnFilter['pages'] = $page->id;
        }
        
        $this->design->assign('page', $page);
        $bannersFilter = [
            'visible' => true,
            'show_on' => $showOnFilter,
        ];
        
        /** @var BannersEntity $bannersEntity */
        $bannersEntity = $this->entityFactory->get(BannersEntity::class);
        /** @var BannersImagesEntity $bannersImagesEntity */
        $bannersImagesEntity = $this->entityFactory->get(BannersImagesEntity::class);
        if ($banners = $bannersEntity->mappedBy('id')->find($bannersFilter)) {
            
            if ($bannersImages = $bannersImagesEntity->find(['banner_id' => array_keys($banners), 'visible' => true])) {
                foreach ($bannersImages as $bannersImage) {
                    if (isset($banners[$bannersImage->banner_id])) {
                        
                        if (!empty($bannersImage->settings)) {
                            $bannersImage->settings = unserialize($bannersImage->settings);
                        }
                        
                        if (empty($bannersImage->settings['desktop']['w'])) {
                            $bannersImage->settings['desktop']['w'] = BannersImagesEntity::DEFAULT_DESKTOP_W;
                        }
                        if (empty($bannersImage->settings['desktop']['h'])) {
                            $bannersImage->settings['desktop']['h'] = BannersImagesEntity::DEFAULT_DESKTOP_H;
                        }
                        if (empty($bannersImage->settings['mobile']['w'])) {
                            $bannersImage->settings['mobile']['w'] = BannersImagesEntity::DEFAULT_MOBILE_W;
                        }
                        if (empty($bannersImage->settings['mobile']['h'])) {
                            $bannersImage->settings['mobile']['h'] = BannersImagesEntity::DEFAULT_MOBILE_H;
                        }
                        if (empty($bannersImage->settings['variant_show'])) {
                            $bannersImage->settings['variant_show'] = BannersImagesEntity::SHOW_DEFAULT;
                        }
                        
                        $banners[$bannersImage->banner_id]->items[] = $bannersImage;
                    }
                }
            }
            
            foreach ($banners as $banner) {

                if (!empty($banner->settings)) {
                    $banner->settings = unserialize($banner->settings);
                }
                
                $this->design->assign('banner_data', $banner);
                // Если баннер отмечен как шорткод, передадим такую переменную в дизайн
                if (!empty($banner->individual_shortcode)) {
                    $bannerHtml = $this->design->fetch('show_banner.tpl');
                    $this->shortCodesParts['{$' . $banner->individual_shortcode . '}'] = $bannerHtml;
                } else {
                    $this->totalBannersHtml .= $this->design->fetch('show_banner.tpl');
                }
            }
        }
        
        // Вернём обратно стандартную директорию шаблонов
        $this->design->useDefaultDir();
    }
}