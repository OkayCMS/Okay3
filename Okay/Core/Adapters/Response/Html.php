<?php


namespace Okay\Core\Adapters\Response;


use Okay\Core\Design;
use Okay\Core\ServiceLocator;

class Html extends AbstractResponse
{

    private $design;
    
    public function __construct()
    {
        $serviceLocator = new ServiceLocator();
        $this->design = $serviceLocator->getService(Design::class);
    }

    public function send($content)
    {
        $this->design->assign('content', $content);

        // Создаем текущую обертку сайта (обычно index.tpl)
        $wrapper = $this->design->get_var('wrapper');
        if (is_null($wrapper)) {
            $wrapper = 'index.tpl';
        }

        header('Content-type: text/html; charset=utf-8', true);

        if (!empty($wrapper)) {
            print $this->design->fetch($wrapper);
        } else {
            print $content;
        }
    }
}
