<?php


namespace Okay\Core\SmartyPlugins\Plugins;


use Okay\Core\EntityFactory;
use Okay\Entities\BannersEntity;
use Okay\Entities\BannersImagesEntity;
use Okay\Core\SmartyPlugins\Func;

class GetBanner extends Func
{

    protected $tag = 'get_banner';
    
    /**
     * @var BannersEntity
     */
    private $banners;

    /**
     * @var BannersImagesEntity
     */
    private $bannersImages;
    
    public function __construct(EntityFactory $entityFactory)
    {
        $this->banners = $entityFactory->get(BannersEntity::class);
        $this->bannersImages = $entityFactory->get(BannersImagesEntity::class);
    }

    public function run($params, \Smarty_Internal_Template $smarty)
    {
        if (!isset($params['group']) || empty($params['group'])) {
            return false;
        }

        $banner = $this->banners->get($params['group']);
        if (!empty($banner)) {
            if ($banner->show_all_pages || $this->checkVisible($smarty, $banner)) {
                if ($items = $this->bannersImages->find(['banner_id'=>$banner->id, 'visible'=>1])) {
                    $banner->items = $items;
                }
                $smarty->assign($params['var'], $banner);
            }
        }
    }
    
    private function checkVisible(\Smarty_Internal_Template $smarty, $banner)
    {
        $categoriesIds  = explode(',', $banner->categories);
        $pagesIds       = explode(',', $banner->pages);
        $brandsIds      = explode(',', $banner->brands);
        @$category  = $smarty->getTemplateVars('category');
        @$brand     = $smarty->getTemplateVars('brand');
        @$page      = $smarty->getTemplateVars('page');
        
        if (!empty($category->id) && in_array($category->id, $categoriesIds)) {
            return true;
        }
        
        if (!empty($brand->id) && in_array($brand->id, $brandsIds)) {
            return true;
        }
        
        if (!empty($page->id) && in_array($page->id, $pagesIds)) {
            return true;
        }
        
        return false;
    }
}