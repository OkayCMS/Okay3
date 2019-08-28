<?php


namespace Okay\Core\Adapters\Response;


class Xml extends AbstractResponse
{
    
    public function send($content)
    {
        header('Content-type: text/xml; charset=UTF-8', true);

        print $content;
    }
}
