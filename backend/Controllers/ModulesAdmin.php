<?php


namespace Okay\Admin\Controllers;


use Okay\Core\Managers;
use Okay\Core\Modules\Installer;
use Okay\Entities\ManagersEntity;
use Okay\Entities\ModulesEntity;
use Okay\Core\Modules\Module;

class ModulesAdmin extends IndexAdmin
{
    public function fetch(
        ModulesEntity  $modulesEntity,
        Installer      $modulesInstaller,
        Module         $moduleCore,
        ManagersEntity $managersEntity,
        Managers       $managersCore
    ) {
        // Обработка действий
        if ($this->request->method('post')) {
            if (!empty($this->request->post('install_module'))) {
                if ($modulesInstaller->install($this->request->post('install_module'))) {
                    $this->design->clearCompiled();
                }
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
                        $this->design->clearCompiled();
                        break;
                    }
                    case 'enable': {
                        $modulesEntity->enable($ids);
                        $this->design->clearCompiled();
                        break;
                    }
                    case 'delete': {
                        $modulesEntity->delete($ids);
                        $this->design->clearCompiled();
                        break;
                    }
                }
            }

            $this->response->redirectTo($this->request->getCurrentUrl());
        }

        $filter = [];
        $manager = $managersEntity->findOne(['login' => $_SESSION['admin']]);
        if ($managersCore->cannotVisibleSystemModules($manager)) {
            $filter['without_system'] = 1;
        }

        $modules = array_merge($modulesEntity->findNotInstalled(), $modulesEntity->find($filter));

        foreach ($modules as $module) {
            $preview = $moduleCore->findModulePreview($module->vendor, $module->module_name);
            if (!empty($preview)) {
                $module->preview = $preview;
            }
        }

        $this->design->assign('modules', $modules);
        $this->response->setContent($this->design->fetch('modules.tpl'));
    }
}