<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;

class ModulesEntity extends Entity
{
    protected static $fields = [
        'id',
        'vendor',
        'module_name',
        'type',
        'enabled',
        'position',
        'backend_main_controller',
    ];

    protected static $langFields = [];

    protected static $searchFields = [
        'module_name',
    ];

    protected static $defaultOrderFields = [
        'position DESC',
    ];

    protected static $table = '__modules';
    protected static $tableAlias = 'm';

    public function enable($ids)
    {
        return $this->update($ids, ['enabled'=>1]);
    }

    public function delete($ids)
    {
        // TODO тут должна быть возможность удаления файлов модуля и откат его миграций
        return parent::delete($ids);
    }

    public function disable($ids)
    {
        return $this->update($ids, ['enabled'=>0]);
    }

    // TODO подумать над тем, чтоб модули автоматически индексировались в базу при заходе в вдминку, тогда этот метод будет не нужен
    public function findNotInstalled()
    {
        $modulesDir = __DIR__.'/../Modules/';
        $modulesDirContains = scandir($modulesDir);

        $notInstalledModules = [];
        $installedFullModuleNames = $this->installedFullModuleNames();
        foreach($modulesDirContains as $vendorName) {
            if ($this->isNotDir($modulesDir.$vendorName)) {
                continue;
            }

            $modulesByVendor = scandir($modulesDir.$vendorName);
            foreach($modulesByVendor as $moduleName) {
                if ($this->isNotDir($modulesDir.$vendorName.'/'.$moduleName)) {
                    continue;
                }

                $fullModuleName = $this->compileFullModuleName($vendorName, $moduleName);
                if (in_array($fullModuleName, $installedFullModuleNames)) {
                    continue;
                }

                $module = new \stdClass();
                $module->id       = null;
                $module->vendor   = $vendorName;
                $module->module_name = $moduleName;
                $module->position = null;
                $module->type     = null;
                $module->enabled  = 0;
                $module->status   = 'Not Installed';

                $notInstalledModules[] = $module;
            }
        }

        return $notInstalledModules;
    }

    public function compileFullModuleName($vendor, $moduleName)
    {
        if (empty($vendor) || empty($moduleName)) {
            throw new \Exception("Vendor And Name cannot be empty");
        }

        return $vendor.'/'.$moduleName;
    }

    private function installedFullModuleNames()
    {
        $installedModules = $this->cols(['vendor', 'module_name'])->find();

        $installedFullModuleNames = [];
        foreach($installedModules as $module) {
            $installedFullModuleNames[] = $this->compileFullModuleName($module->vendor, $module->module_name);
        }

        return $installedFullModuleNames;
    }

    private function isNotDir($dir)
    {
        $catalogNames = explode('/', $dir);
        if (!is_dir($dir) || end($catalogNames) === '.' || end($catalogNames) === '..') {
            return true;
        }

        return false;
    }
}