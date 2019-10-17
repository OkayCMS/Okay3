<?php


namespace Okay\Modules\OkayCMS\FastOrder\Init;


use Okay\Core\Modules\AbstractInit;

class Init extends AbstractInit
{
    public function install()
    {
        $this->setBackendMainController('DescriptionAdmin');
    }

    public function init()
    {
        $this->addPermission('okaycms__fast_order');

        $this->registerBackendController('DescriptionAdmin');
        $this->addBackendControllerPermission('DescriptionAdmin', 'okaycms__fast_order');

        $this->addFrontBlock('front_after_footer_content', 'fast_order_form.tpl');
    }
}