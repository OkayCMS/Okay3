<?php


namespace Okay\Controllers;


use Okay\Core\Router;
use Okay\Core\Request;
use Okay\Entities\BlogEntity;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\PagesEntity;
use Okay\Entities\ProductsEntity;

class SiteMapController extends AbstractController
{
    
    private $siteMapIndex = 1;
    private $urlIndex = 0;
    private $params = [];
    
    const MAX_URLS = 50000;
    
    public function renderXml(
        ProductsEntity $productsEntity,
        CategoriesEntity $categoriesEntity,
        BrandsEntity $brandsEntity,
        PagesEntity $pagesEntity,
        BlogEntity $blogEntity
    ) {

        /*
         * параметры с крона в виде key=val
         * доступные пары:
         * lang_label=ru
         * root_url=http://domain.com
         *
         * чтобы сгенерировать файлы с браузера нужно в браузере перейти по ссылке
         * http://domain.com/sitemap.xml?output=file
         */
        if (!empty($argv)) {
            $this->params['output'] = 'file';
            for ($i = 1; $i < count($argv); $i++) {
                $arg = explode("=", $argv[$i]);
                $this->params[$arg[0]] = $arg[1];
            }
            $this->params['root_url'] = trim($this->params['root_url']);
            $this->params['root_url'] = preg_replace("~^(https?://[^/]+)/.*$~", "$1", $this->params['root_url']);
            $_GET['lang_label'] = $this->params['lang_label'];
        } else {
            if (isset($_GET['output']) && $_GET['output']=='file') {
                $this->params['output'] = 'file';
            } else {
                $this->params['output'] = 'browser';
            }
            $this->params['root_url'] = Request::getRootUrl();
        }
        
        $this->params['l'] = '_'.$this->language->label;
        
        if ($this->params['output'] == 'file') {
            $sub_sitemaps = glob($this->config->root_dir . "/sitemap" . $this->params['l'] . "_*.xml");
            if (is_array($sub_sitemaps)) {
                foreach ($sub_sitemaps as $sitemap) {
                    @unlink($sitemap);
                }
            }
            if (file_exists($this->config->root_dir . "/sitemap" . $this->params['l'] . ".xml")) {
                @unlink($this->config->root_dir . "sitemap" . $this->params['l'] . ".xml");
            }
        }

        $this->write("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
        $this->write("<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n");
        $s = "\t<url>\n";
        $s .= "\t\t<loc>" . Router::generateUrl('main', [], true) . "</loc>\n";
        $s .= "\t\t<changefreq>daily</changefreq>\n";
        $s .= "\t\t<priority>1.0</priority>\n";
        $s .= "\t</url>\n";
        $this->write($s, true);

        foreach ($pagesEntity->find(['visible'=>1]) as $p) {
            if ($p->url && $p->url != '404') {
                $url = Router::generateUrl('page', ['url' => $p->url], true);
                $lastModify = [];
                if ($p->url == 'blog') {
                    $lastModify = $blogEntity->cols(['last_modify'])->order('last_modify_desc')->find(['limit'=>1]);
                    $lastModify[] = $this->settings->lastModifyPosts;
                }
                $lastModify[] = $p->last_modify;
                $lastModify = max($lastModify);
                $lastModify = substr($lastModify, 0, 10);
                $s = "\t<url>\n";
                $s .= "\t\t<loc>$url</loc>\n";
                $s .= "\t\t<lastmod>$lastModify</lastmod>\n";
                $s .= "\t\t<changefreq>daily</changefreq>\n";
                $s .= "\t\t<priority>1.0</priority>\n";
                $s .= "\t</url>\n";
                $this->write($s, true);
            }
        }

        $postsCount = $blogEntity->count(['visible'=>1]);
        foreach ($blogEntity->find(['visible'=>1, 'limit'=>$postsCount]) as $p) {
            if ($p->type_post == 'news') {
                $url = Router::generateUrl('news_item', ['url' => $p->url], true);
            } else {
                $url = Router::generateUrl('blog_item', ['url' => $p->url], true);
            }
            $lastModify = substr($p->last_modify, 0, 10);
            $s = "\t<url>\n";
            $s .= "\t\t<loc>$url</loc>\n";
            $s .= "\t\t<lastmod>$lastModify</lastmod>\n";
            $s .= "\t\t<changefreq>daily</changefreq>\n";
            $s .= "\t\t<priority>1.0</priority>\n";
            $s .= "\t</url>\n";
            $this->write($s, true);
        }

        foreach ($categoriesEntity->find() as $c) {
            if ($c->visible) {
                $url = Router::generateUrl('category', ['url' => $c->url], true);
                $lastModify = $productsEntity->cols(['last_modify'])->order('last_modify_desc')->find([
                    'category_id' => $c->children,
                    'limit'=>1,
                ]);
                
                $lastModify[] = $c->last_modify;
                $lastModify = substr(max($lastModify), 0, 10);
                $s = "\t<url>\n";
                $s .= "\t\t<loc>$url</loc>\n";
                $s .= "\t\t<lastmod>$lastModify</lastmod>\n";
                $s .= "\t\t<changefreq>daily</changefreq>\n";
                $s .= "\t\t<priority>1.0</priority>\n";
                $s .= "\t</url>\n";
                $this->write($s, true);
            }
        }

        $brandsCount = $brandsEntity->count(['visible'=>1]);
        foreach ($brandsEntity->find(['visible'=>1, 'limit'=>$brandsCount]) as $b) {
            $url = Router::generateUrl('brand', ['url' => $b->url], true);
            $lastModify = $productsEntity->cols(['last_modify'])->order('last_modify_desc')->find([
                'brand_id' => $b->id,
                'limit'=>1,
            ]);
            $lastModify[] = $b->last_modify;
            $lastModify = substr(max($lastModify), 0, 10);
            $s = "\t<url>\n";
            $s .= "\t\t<loc>$url</loc>\n";
            $s .= "\t\t<lastmod>$lastModify</lastmod>\n";
            $s .= "\t\t<changefreq>daily</changefreq>\n";
            $s .= "\t\t<priority>1.0</priority>\n";
            $s .= "\t</url>\n";
            $this->write($s, true);
        }

        $products = $productsEntity->cols([
            'url',
            'last_modify',
        ])->find(['visible' => 1]);
        foreach ($products as $p) {
            $url = Router::generateUrl('product', ['url' => $p->url], true);
            $lastModify = substr($p->last_modify, 0, 10);
            $s = "\t<url>\n";
            $s .= "\t\t<loc>$url</loc>\n";
            $s .= "\t\t<lastmod>$lastModify</lastmod>\n";
            $s .= "\t\t<changefreq>weekly</changefreq>\n";
            $s .= "\t\t<priority>0.5</priority>\n";
            $s .= "\t</url>\n";
            $this->write($s, true);
        }
        
        $this->write("</urlset>\n");

        if ($this->params['output'] == 'file') {
            $last_modify = date("Y-m-d");
            $file = 'sitemap'.$this->params['l'].'.xml';
            file_put_contents($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
            file_put_contents($file, "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n", FILE_APPEND);
            for ($i = 1; $i <= $this->siteMapIndex; $i++) {
                $url = $this->params['root_url'].'/sitemap'.$this->params['l'].'_'.$i.'.xml';
                file_put_contents($file, "\t<sitemap>"."\n", FILE_APPEND);
                file_put_contents($file, "\t\t<loc>$url</loc>"."\n", FILE_APPEND);
                file_put_contents($file, "\t\t<lastmod>$last_modify</lastmod>"."\n", FILE_APPEND);
                file_put_contents($file, "\t</sitemap>"."\n", FILE_APPEND);
            }
            file_put_contents($file, '</sitemapindex>'."\n", FILE_APPEND);
            return;
        }
        
    }
    
    private function write($str, $countUrl = false) {
        if ($this->params['output'] == 'file') {
            $file = 'sitemap'.$this->params['l'].'_'.$this->siteMapIndex.'.xml';
            file_put_contents($file, $str, FILE_APPEND);
            if ($countUrl && ++$this->urlIndex == self::MAX_URLS) {
                file_put_contents($file, '</urlset>'."\n", FILE_APPEND);
                $this->urlIndex=0;
                $this->siteMapIndex++;
                $file = 'sitemap'.$this->params['l'].'_'.$this->siteMapIndex.'.xml';
                file_put_contents($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
                file_put_contents($file, "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n", FILE_APPEND);
            }
        } else {
            $this->response->setContent($str, RESPONSE_XML);
        }
    }
    
}
