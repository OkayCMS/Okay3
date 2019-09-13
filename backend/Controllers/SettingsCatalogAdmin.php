<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\ManagersEntity;
use Okay\Core\Managers;
use Okay\Core\DataCleaner;

class SettingsCatalogAdmin extends IndexAdmin
{

    private $allowedImageExtensions = ['png', 'gif', 'jpg', 'jpeg', 'ico'];

    /*Настройки каталога*/
    public function fetch(
        ManagersEntity $managersEntity,
        Managers $managersCore,
        DataCleaner $dataCleaner
    ) {
        $managersList = $managersEntity->find();
        $this->design->assign('managers', $managersList);

        if ($this->request->method('POST')) {
            $this->settings->decimals_point = $this->request->post('decimals_point');
            $this->settings->thousands_separator = $this->request->post('thousands_separator');
            $this->settings->products_num = $this->request->post('products_num');
            $this->settings->max_order_amount = $this->request->post('max_order_amount');
            $this->settings->comparison_count = $this->request->post('comparison_count');
            $this->settings->update('units', $this->request->post('units'));
            $this->settings->posts_num = $this->request->post('posts_num');
            
            if ($this->request->post('truncate_table_confirm') && ($pass = $this->request->post('truncate_table_password'))) {
                $manager = $managersEntity->get($_SESSION['admin']);
                if ($managersCore->checkPassword($pass, $manager->password)) {
                    $dataCleaner->clearAllCatalogImages();
                    $dataCleaner->clearCatalogData();
                }
                else {
                    $this->design->assign('message_error', 'truncate_table_password_failed');
                }
            }
            
            if ($this->request->post('is_preorder', 'integer')){
                $this->settings->is_preorder = $this->request->post('is_preorder', 'integer');
            } else {
                $this->settings->is_preorder = 0;
            }
            // Водяной знак
            $clearImageCache = false;

            if ($this->request->post('delete_watermark')) {
                $clearImageCache = true;
                unlink($this->config->root_dir.$this->config->watermark_file);
                $this->config->watermark_file = '';
            }

            $watermark = $this->request->files('watermark_file', 'tmp_name');
            if (!empty($watermark) && in_array(pathinfo($this->request->files('watermark_file', 'name'), PATHINFO_EXTENSION), $this->allowedImageExtensions)) {
                $this->config->watermark_file = 'backend/files/watermark/watermark.png';
                if (@move_uploaded_file($watermark, $this->config->root_dir.$this->config->watermark_file)) {
                    $clearImageCache = true;
                } else {
                    $this->design->assign('message_error', 'watermark_is_not_writable');
                }
            }

            if ($this->settings->watermark_offset_x != $this->request->post('watermark_offset_x')) {
                $this->settings->watermark_offset_x = $this->request->post('watermark_offset_x');
                $clearImageCache = true;
            }
            
            if ($this->settings->watermark_offset_y != $this->request->post('watermark_offset_y')) {
                $this->settings->watermark_offset_y = $this->request->post('watermark_offset_y');
                $clearImageCache = true;
            }
            
            // Удаление заресайзеных изображений
            if ($clearImageCache === true) {
                $dataCleaner->clearResizeImages();
            }
            $this->design->assign('message_success', 'saved');
            
        }

        $this->response->setContent($this->design->fetch('settings_catalog.tpl'));
    }

}
