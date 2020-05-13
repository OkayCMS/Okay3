<?php


namespace Okay\Modules\OkayCMS\Banners\Requests;


use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Core\Request;

class BannersRequest
{

    /** @var Request */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function postBanner()
    {
        $banner = new \stdClass;
        $banner->id = $this->request->post('id', 'integer');
        $banner->name = $this->request->post('name');
        $banner->group_name = $this->request->post('group_name');
        $banner->visible = $this->request->post('visible', 'boolean');
        $banner->show_all_pages = (int)$this->request->post('show_all_pages');
        $banner->show_all_products = (int)$this->request->post('show_all_products');
        $banner->categories = implode(",",$this->request->post('categories'));
        $banner->brands = implode(",",$this->request->post('brands'));
        $banner->pages = implode(",",$this->request->post('pages'));
        $banner->settings = serialize($this->request->post('settings'));

        if ($this->request->post('use_individual_shortcode', 'boolean') && ($individualShortCode = $this->request->post('individual_shortcode'))) {
            $individualShortCode = preg_replace('~^{*\$*([\w]+?)}*$~', '$1', $individualShortCode);
            $banner->individual_shortcode = $individualShortCode;
        }

        return ExtenderFacade::execute(__METHOD__, $banner, func_get_args());
    }
}