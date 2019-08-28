<?php


namespace Okay\Core;


class BackendTranslations
{
    
    public function getTranslation($var)
    {
        return $this->$var;
    }
}
