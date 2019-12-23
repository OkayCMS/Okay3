<?php


namespace Okay\Admin\Requests;


use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Core\Request;

class BackendSettingsRequest
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function postTruncateTableConfirm()
    {
        $confirm = $this->request->post('truncate_table_confirm');
        return ExtenderFacade::execute(__METHOD__, $confirm, func_get_args());
    }

    public function postCounters()
    {
        $counters = [];
        if ($this->request->post('counters')) {
            foreach ($this->request->post('counters') as $n=>$co) {
                foreach ($co as $i=>$c) {
                    if (empty($counters[$i])) {
                        $counters[$i] = new \stdClass;
                    }
                    $counters[$i]->$n = $c;
                }
            }
        }

        return ExtenderFacade::execute(__METHOD__, $counters, func_get_args());
    }

    public function filesFavicon()
    {
        $siteFavicon = $this->request->files('site_favicon');
        return ExtenderFacade::execute(__METHOD__, $siteFavicon, func_get_args());
    }

    public function postFavicon()
    {
        $favicon = $this->request->post('site_favicon');
        return ExtenderFacade::execute(__METHOD__, $favicon, func_get_args());
    }

    public function postMultiLangLogo()
    {
        $multiLangLogo = $this->request->post('multilang_logo', 'integer');
        return ExtenderFacade::execute(__METHOD__, $multiLangLogo, func_get_args());
    }

    public function filesSiteLogo()
    {
        $siteLogo = $this->request->files('site_logo');
        return ExtenderFacade::execute(__METHOD__, $siteLogo, func_get_args());
    }

    public function postSiteLogo()
    {
        $siteLogo = $this->request->post('site_logo');
        return ExtenderFacade::execute(__METHOD__, $siteLogo, func_get_args());
    }

    public function filesAdvantageImages()
    {
        $images = $_FILES['advantages_image'];
        $advantageImages = [];
        foreach($images['name'] as $advantageId => $imageName) {
            $advantageImage = [];
            $advantageImage['name']     = $imageName;
            $advantageImage['tmp_name'] = $images['tmp_name'][$advantageId];
            $advantageImages[$advantageId] = $advantageImage;
        }

        return ExtenderFacade::execute(__METHOD__, $advantageImages, func_get_args());
    }

    public function postAdvantagesUpdates()
    {
        $advantages = [];

        $position = 0;
        foreach($this->request->post('advantages_text') as $id => $advantageText) {
            $advantage           = new \stdClass();
            $advantage->text     = $advantageText;
            $advantage->position = $position;

            $advantages[$id] = $advantage;

            $position++;
        }

        return ExtenderFacade::execute(__METHOD__, $advantages, func_get_args());
    }

    public function postDeleteAdvantageImages()
    {
        $deleteImages = $this->request->post('delete_image');

        if (empty($deleteImages)) {
            return ExtenderFacade::execute(__METHOD__, [], func_get_args());
        }

        foreach($deleteImages as $key => $deleteImage) {
            if (empty($deleteImage)) {
                unset($deleteImages[$key]);
            }
        }

        return ExtenderFacade::execute(__METHOD__, $deleteImages, func_get_args());
    }
}