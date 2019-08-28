<?php


namespace Okay\Core\Adapters\Response;


class Json extends AbstractResponse
{

    public function send($content)
    {
        header('Content-type: application/json; charset=utf-8', true);
        header('Cache-Control: must-revalidate');
        header('Pragma: no-cache');
        header('Expires: -1');

        print $content;
    }
}
