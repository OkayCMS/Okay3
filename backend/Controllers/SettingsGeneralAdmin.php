<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendSettingsHelper;

class SettingsGeneralAdmin extends IndexAdmin
{
    public function fetch(BackendSettingsHelper $backendSettingsHelper)
    {
        if ($this->request->method('POST')) {
            $backendSettingsHelper->updateGeneralSettings();
            $this->design->assign('message_success', 'saved');
        }

        $this->response->setContent($this->design->fetch('settings_general.tpl'));
    }
}
