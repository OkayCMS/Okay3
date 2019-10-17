<?php


namespace Okay\Helpers;


use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Core\Request;
use Okay\Core\Settings;
use Okay\Core\Validator;

class ValidateHelper
{

    private $validator;
    private $settings;
    private $request;

    public function __construct(Validator $validator, Settings $settings, Request $request)
    {
        $this->validator = $validator;
        $this->settings = $settings;
        $this->request = $request;
    }

    public function getCartValidateError($order)
    {
        $captchaCode =  $this->request->post('captcha_code', 'string');
        
        $error = null;
        if (!$this->validator->isName($order->name, true)) {
            $error = 'empty_name';
        } elseif (!$this->validator->isEmail($order->email, true)) {
            $error = 'empty_email';
        } elseif (!$this->validator->isPhone($order->phone)) {
            $error = 'empty_phone';
        } elseif (!$this->validator->isAddress($order->address)) {
            $error = 'empty_address';
        } elseif (!$this->validator->isComment($order->comment)) {
            $error = 'empty_comment';
        } elseif ($this->settings->get('captcha_cart') && !$this->validator->verifyCaptcha('captcha_cart', $captchaCode)) {
            $error = 'captcha';
        }

        return ExtenderFacade::execute(__METHOD__, $error, func_get_args());
    }
}