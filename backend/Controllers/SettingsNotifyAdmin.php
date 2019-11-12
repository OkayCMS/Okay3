<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendSettingsHelper;
use Okay\Core\Languages;

class SettingsNotifyAdmin extends IndexAdmin
{

    public function fetch(
        BackendSettingsHelper $backendSettingsHelper,
        Languages $languages
    ){
        if ($this->request->method('POST')) {
            $backendSettingsHelper->updateNotifySettings();
            $this->design->assign('message_success', 'saved');
        }

        $btrLanguages = [];
        foreach ($languages->getLangList() as $label=>$l) {
            if (file_exists("backend/lang/".$label.".php")) {
                $btrLanguages[$l->name] = $l->label;
            }
        }

        $this->design->assign('btr_languages', $btrLanguages);
        $this->response->setContent($this->design->fetch('settings_notify.tpl'));
    }

}
