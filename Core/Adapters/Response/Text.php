<?php


namespace Okay\Core\Adapters\Response;


class Text extends AbstractResponse
{

    public function send($content)
    {
        header('Content-type: text/html; charset=utf-8', true);

        print $content;
    }
}
