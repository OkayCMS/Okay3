<?php


namespace Okay\Admin\Controllers;


use Okay\Core\Modules\Installer;
use Okay\Entities\ModulesEntity;

class ModulesAdmin extends IndexAdmin
{
    public function fetch(ModulesEntity $modulesEntity, Installer $modulesInstaller)
    {
        // Обработка действий
        if ($this->request->method('post')) {
            if (!empty($this->request->post('install_module'))) {
                $modulesInstaller->install($this->request->post('install_module'));
            }

            // Сортировка
            $positions = $this->request->post('positions');
            $ids = array_keys($positions);
            rsort($positions);
            foreach ($positions as $i=>$position) {
                $modulesEntity->update($ids[$i], ['position'=>$position]);
            }

            // Действия с выбранными
            $ids = $this->request->post('check');
            if (is_array($ids)) {
                switch ($this->request->post('action')) {
                    case 'disable': {
                        $modulesEntity->disable($ids);
                        break;
                    }
                    case 'enable': {
                        $modulesEntity->enable($ids);
                        break;
                    }
                    case 'delete': {
                        $modulesEntity->delete($ids);
                        break;
                    }
                }
            }
        }

        $modules = array_merge($modulesEntity->findNotInstalled(), $modulesEntity->find());
        $this->design->assign('modules', $modules);
        $this->response->setContent($this->design->fetch('modules.tpl'));
    }
}