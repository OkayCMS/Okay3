<?php


namespace Okay\Admin\Controllers;


class SettingsGeneralAdmin extends IndexAdmin
{

    /*Настройки сайта*/
    public function fetch()
    {
        if ($this->request->method('POST')) {
            $this->settings->update('site_name', $this->request->post('site_name'));
            $this->settings->date_format = $this->request->post('date_format');
            $this->settings->admin_email = $this->request->post('admin_email');
            $this->settings->site_work = $this->request->post('site_work');
            $this->settings->update('site_annotation', $this->request->post('site_annotation'));
            $this->settings->captcha_product = $this->request->post('captcha_product', 'boolean');
            $this->settings->captcha_post = $this->request->post('captcha_post', 'boolean');
            $this->settings->captcha_cart = $this->request->post('captcha_cart', 'boolean');
            $this->settings->captcha_register = $this->request->post('captcha_register', 'boolean');
            $this->settings->captcha_feedback = $this->request->post('captcha_feedback', 'boolean');
            $this->settings->captcha_callback = $this->request->post('captcha_callback', 'boolean');
            $this->settings->public_recaptcha = $this->request->post('public_recaptcha');
            $this->settings->secret_recaptcha = $this->request->post('secret_recaptcha');
            $this->settings->public_recaptcha_invisible = $this->request->post('public_recaptcha_invisible');
            $this->settings->secret_recaptcha_invisible = $this->request->post('secret_recaptcha_invisible');
            $this->settings->captcha_type = $this->request->post('captcha_type');
            $this->settings->gather_enabled = $this->request->post('gather_enabled', 'boolean');
            $this->settings->public_recaptcha_v3 = $this->request->post('public_recaptcha_v3');
            $this->settings->secret_recaptcha_v3 = $this->request->post('secret_recaptcha_v3');

            if ($recaptcha_scores = $this->request->post('recaptcha_scores')) {
                foreach ($recaptcha_scores as $k=>$score) {
                    $score = (float)str_replace(',', '.', $score);
                    $recaptcha_scores[$k] = round($score, 1);
                }
            }
            $this->settings->recaptcha_scores = $recaptcha_scores;
            
            $this->design->assign('message_success', 'saved');
        }

        $this->response->setContent($this->design->fetch('settings_general.tpl'));
    }

}
