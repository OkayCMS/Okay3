<?php


namespace Okay\Admin\Controllers;


class SettingsCounterAdmin extends IndexAdmin
{

    /*Настройки счетчиков*/
    public function fetch()
    {
        if ($this->request->method('POST')) {

            if ($this->request->post('counters')) {
                foreach ($this->request->post('counters') as $n=>$co) {
                    foreach ($co as $i=>$c) {
                        if (empty($counters[$i])) {
                            $counters[$i] = new \stdClass;
                        }
                        $counters[$i]->$n = $c;
                    }
                }
            }
            $this->settings->counters = $counters;
            $this->design->assign('message_success', 'saved');
        }
        $this->design->assign('counters', $this->settings->counters);
        $this->response->addHeader('X-XSS-Protection:0');

        $this->response->setContent($this->design->fetch('settings_counter.tpl'));
    }
}
