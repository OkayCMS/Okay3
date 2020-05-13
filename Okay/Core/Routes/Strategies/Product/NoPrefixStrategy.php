<?php


namespace Okay\Core\Routes\Strategies\Product;


use Okay\Core\Routes\Strategies\AbstractRouteStrategy;

class NoPrefixStrategy extends AbstractRouteStrategy
{

    private $mockRouteParams = ['{$url}', ['{$url}' => ''], ['{$url}' => '']];

    public function generateRouteParams($url)
    {
        $productUrl = $this->matchProductUrl($url);

        if (empty($productUrl)) {
            return $this->mockRouteParams;
        }

        return ['{$url}', ['{$url}' => $productUrl], ['{$url}' => $productUrl]];
    }

    private function matchProductUrl($url)
    {
        preg_match("/([^\/]+)/ui", $url, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }

        return '';
    }
}