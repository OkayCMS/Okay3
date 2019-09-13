<?php


namespace Okay\Core;


class BackendTranslations
{
    
    public function getTranslation($var)
    {
        return $this->$var;
    }
    
    public function addTranslation($var, $translation)
    {
        $var = preg_replace('~[^\w]~', '', $var);
        $this->$var = $translation;
    }
}
