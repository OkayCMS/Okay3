<?php


namespace Okay\Controllers;


use Okay\Entities\ProductsEntity;

class OpenSearchController extends AbstractController
{
    
    
    public function renderXml() {
        $this->design->set_templates_dir('xml');
        $this->design->set_compiled_dir('xml/compiled');
        
        if ($this->settings->site_favicon) {
            $ext = pathinfo($this->settings->site_favicon, PATHINFO_EXTENSION);
            $faviconMime = '';

            switch ($ext) {
                case 'png':
                    $faviconMime = 'image/png';
                    break;
                case 'jpeg':// no break
                case 'jpg':
                $faviconMime = 'image/jpeg';
                    break;
                case 'ico':
                    $faviconMime = 'image/x-icon';
                    break;
            }
            $this->design->assign('favicon_mime', $faviconMime);
        }
        
        $this->response->setContent($this->design->fetch('opensearch.xml.tpl'), RESPONSE_XML);
    }
    
    public function liveSearch(ProductsEntity $productsEntity)
    {
        $filter['keyword'] = $this->request->get('query', 'string');
        $filter['visible'] = true;
        $filter['limit'] = 10;

        $productsNames = $productsEntity->cols(['name'])->order('name')->find($filter);

        $res[] = $filter['keyword'];
        $res[] = $productsNames;
        
        $this->response->setContent(json_encode($res), RESPONSE_JSON);
    }
    
}
