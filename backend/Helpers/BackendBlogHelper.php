<?php


namespace Okay\Admin\Helpers;


use Okay\Core\Config;
use Okay\Core\EntityFactory;
use Okay\Core\Image;
use Okay\Core\Request;
use Okay\Core\Settings;
use Okay\Entities\BlogEntity;
use Okay\Core\Modules\Extender\ExtenderFacade;

class BackendBlogHelper
{
    /**
     * @var BlogEntity
     */
    private $blogEntity;

    /**
     * @var Request
     */
    private $request;
    
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var Image
     */
    private $imageCore;
    
    /**
     * @var Settings
     */
    private $settings;

    public function __construct(
        EntityFactory $entityFactory,
        Request $request,
        Config $config,
        Image $imageCore,
        Settings $settings
    ) {
        $this->blogEntity = $entityFactory->get(BlogEntity::class);
        $this->request    = $request;
        $this->config     = $config;
        $this->imageCore  = $imageCore;
        $this->settings   = $settings;
    }

    public function disable($ids)
    {
        if (is_array($ids)) {
            $this->blogEntity->update($ids, ['visible'=>0]);
        }

        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }

    public function enable($ids)
    {
        if (is_array($ids)) {
            $this->blogEntity->update($ids, ['visible' => 1]);
        }

        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }

    public function delete($ids)
    {
        if (is_array($ids)) {
            $this->blogEntity->delete($ids);
        }

        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }

    public function buildPostsFilter()
    {
        $filter = [];
        $filter['page'] = max(1, $this->request->get('page', 'integer'));
        $filter['limit'] = 20;

        $keyword = $this->request->get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
        }

        $typePost = $this->request->get('type_post', 'string');
        if (!empty($typePost)) {
            $filter['type_post'] = $typePost;
        }

        $postsCount = $this->blogEntity->count($filter);
        if($this->request->get('page') == 'all') {
            $filter['limit'] = $postsCount;
        }

        return ExtenderFacade::execute(__METHOD__, $filter, func_get_args());
    }

    public function getPostsCount($filter)
    {
        $obj = new \ArrayObject();
        $countFilter = $obj->getArrayCopy();
        unset($countFilter['limit']);
        $count = $this->blogEntity->count($filter);
        return ExtenderFacade::execute(__METHOD__, $count, func_get_args());
    }

    public function findPosts($filter)
    {
        $posts = $this->blogEntity->find($filter);
        return ExtenderFacade::execute(__METHOD__, $posts, func_get_args());
    }

    public function prepareAdd($post)
    {
        return ExtenderFacade::execute(__METHOD__, $post, func_get_args());
    }

    public function add($post)
    {
        $insertId = $this->blogEntity->add($post);
        return ExtenderFacade::execute(__METHOD__, $insertId, func_get_args());
    }

    public function prepareUpdate($post)
    {
        return ExtenderFacade::execute(__METHOD__, $post, func_get_args());
    }

    public function update($id, $post)
    {
        $this->blogEntity->update($id, $post);
        return ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }

    public function getPost($id)
    {
        $post = $this->blogEntity->get($id);

        if (empty($post)) {
            $post = new \stdClass;
            $post->date = date($this->settings->get('date_format'), time());
            $post->visible = 1;
        }
        
        return ExtenderFacade::execute(__METHOD__, $post, func_get_args());
    }

    public function deleteImage($post)
    {
        $this->imageCore->deleteImage(
            $post->id,
            'image',
            BlogEntity::class,
            $this->config->get('original_blog_dir'),
            $this->config->get('resized_blog_dir')
        );

        return ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }

    public function uploadImage($image, $post)
    {
        if (!empty($image['name']) && ($filename = $this->imageCore->uploadImage($image['tmp_name'], $image['name'], $this->config->get('original_blog_dir')))) {
            $this->imageCore->deleteImage(
                $post->id,
                'image',
                BlogEntity::class,
                $this->config->get('original_blog_dir'),
                $this->config->get('resized_blog_dir')
            );

            $this->blogEntity->update($post->id, ['image'=>$filename]);
        }

        return ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }

    public function prepareUpdateRelatedProducts($post, $relatedProducts)
    {
        return ExtenderFacade::execute(__METHOD__, $relatedProducts, func_get_args());
    }

    public function updateRelatedProducts($post, $relatedProducts)
    {
        $this->blogEntity->deleteRelatedProduct($post->id);
        if (is_array($relatedProducts)) {
            $pos = 0;
            foreach($relatedProducts  as $i=>$relatedProduct) {
                $this->blogEntity->addRelatedProduct($post->id, $relatedProduct->related_id, $pos++);
            }
        }

        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }
    
}