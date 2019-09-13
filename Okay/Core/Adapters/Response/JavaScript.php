<?php


namespace Okay\Core\Adapters\Response;


class JavaScript extends AbstractResponse
{

    public function send($content)
    {
        header('Content-type: text/javascript; charset=utf-8', true);

        print $content;
    }
}
