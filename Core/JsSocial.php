<?php


namespace Okay\Core;


class JsSocial
{

    private $jsSocials = [
        "email",
        "twitter",
        "facebook",
        "googleplus",
        "linkedin",
        "pinterest",
        "stumbleupon",
        "pocket",
        "whatsapp",
        "viber",
        "messenger",
        "vkontakte",
        "telegram",
        "line",
        "odnoklassniki",
    ];
    private $customJsSocials = [
        "odnoklassniki" => [
            "label" => "ok",
            "logo" => "fa fa-odnoklassniki",
            "shareUrl" => "https://connect.ok.ru/dk?st.cmd=WidgetSharePreview&st.shareUrl={url}&title={title}",
        ],
    ];
    
    public function getSocials()
    {
        return $this->jsSocials;
    }
    
    public function getCustomSocials()
    {
        return $this->customJsSocials;
    }
    
}