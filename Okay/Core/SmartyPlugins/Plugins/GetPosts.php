<?php


namespace Okay\Core\SmartyPlugins\Plugins;


use Okay\Core\EntityFactory;
use Okay\Entities\BlogEntity;
use Okay\Core\SmartyPlugins\Func;

class GetPosts extends Func
{

    protected $tag = 'get_posts';
    
    /**
     * @var BlogEntity
     */
    private $blogEntity;

    
    public function __construct(EntityFactory $entityFactory)
    {
        $this->blogEntity = $entityFactory->get(BlogEntity::class);
    }

    public function run($params, \Smarty_Internal_Template $smarty)
    {
        if (!isset($params['visible'])) {
            $params['visible'] = 1;
        }
        
        if (!empty($params['var'])) {
            $smarty->assign($params['var'], $this->blogEntity->find($params));
        }
    }
}