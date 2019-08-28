<?php


namespace Okay\Core\Adapters\Response;


class Image extends AbstractResponse
{

    public function send($content)
    {
        header('Content-type: image', true);

        print $content;
    }
}
