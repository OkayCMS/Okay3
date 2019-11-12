<?php


namespace Okay\Core\Routes\Strategies\NewsItem;


use Okay\Core\EntityFactory;
use Okay\Core\Routes\Strategies\AbstractRouteStrategy;
use Okay\Core\ServiceLocator;
use Okay\Entities\BlogEntity;

class NoPrefixStrategy extends AbstractRouteStrategy
{
    /**
     * @var BlogEntity
     */
    private $blogEntity;

    private $mockRouteParams = ['{$url}', ['{$url}' => ''], ['{$url}' => '', '{$typePost}' => 'news']];

    public function __construct()
    {
        $serviceLocator = new ServiceLocator();
        $entityFactory  = $serviceLocator->getService(EntityFactory::class);

        $this->blogEntity = $entityFactory->get(BlogEntity::class);
    }

    public function generateRouteParams($url)
    {
        $post = $this->blogEntity->findOne([
            'url'       => (string) $url,
            'type_post' => 'news'
        ]);

        if (empty($post)) {
            return $this->mockRouteParams;
        }

        return ['{$url}', ['{$url}' => $url], ['{$url}' => $url, '{$typePost}' => 'news']];
    }
}