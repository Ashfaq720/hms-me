<?php

namespace App\Application\Module\Actions;

use App\Domain\Module\Services\ModuleService;
use App\Domain\Module\Entities\ModuleEntity;

class DeleteModuleAction
{
    public function __construct(
        private readonly ModuleService $moduleService
    ) {}

    public function execute(ModuleEntity $module): bool
    {
        try {
            return $this->moduleService->deleteModule($module->id);
        } catch (\Exception $e) {
            throw new \Exception("Failed to delete module '{$module->name}': {$e->getMessage()}");
        }
    }
}
