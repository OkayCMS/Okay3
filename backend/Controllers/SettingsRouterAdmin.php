<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendSettingsHelper;

class SettingsRouterAdmin extends IndexAdmin
{

    public function fetch(BackendSettingsHelper $backendSettingsHelper)
    {
        if ($this->request->method('POST')) {
            $backendSettingsHelper->updateRouterSettings();
            $this->design->assign('message_success', 'saved');
        }

        $this->response->setContent($this->design->fetch('settings_router.tpl'));
    }
}