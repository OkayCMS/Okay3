<?php


namespace Okay\Modules\OkayCMS\Integration1C\Backend\Controllers;


use Okay\Admin\Controllers\IndexAdmin;

class Description1CAdmin extends IndexAdmin
{
    public function fetch()
    {
        $this->response->setContent($this->design->fetch('description.tpl'));
    }
}