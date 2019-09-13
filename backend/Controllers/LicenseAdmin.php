<?php


namespace Okay\Admin\Controllers;


use OkayLicense\License;

class LicenseAdmin extends IndexAdmin
{
    
    public function fetch(License $license)
    {
        if ($this->request->method('POST')) {
            $this->config->license = trim($this->request->post('license'));
        }
        
        $this->design->assign('license_domains', $license->getLicenseDomains());
        $this->design->assign('license_expiration', $license->getLicenseExpiration());

        $this->response->setContent($this->design->fetch('license.tpl'));
    }
    
}
