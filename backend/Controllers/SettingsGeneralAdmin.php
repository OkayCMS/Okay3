<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\AdvantagesEntity;
use Okay\Admin\Helpers\BackendSettingsHelper;
use Okay\Admin\Requests\BackendSettingsRequest;

class SettingsGeneralAdmin extends IndexAdmin
{
    public function fetch(
        BackendSettingsRequest $settingsRequest,
        BackendSettingsHelper  $backendSettingsHelper
    ){
        if ($this->request->method('post')) {
            $deleteImages = $settingsRequest->postDeleteAdvantageImages();
            foreach($deleteImages as $advantageId => $deleteImage) {
                $backendSettingsHelper->deleteAdvantageImage($advantageId);
            }

            $backendSettingsHelper->updateGeneralSettings();
            $this->design->assign('message_success', 'saved');
        }

        $this->response->setContent($this->design->fetch('settings_general.tpl'));
    }
}