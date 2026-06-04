<?php

namespace App\Application\Module\Actions;

use App\Application\Module\DTO\UpdateModuleDTO;
use App\Domain\Module\Services\ModuleService;
use App\Domain\Module\Entities\ModuleEntity;

class UpdateModuleAction
{
    public function __construct(
        private readonly ModuleService $moduleService
    ) {}

    public function execute(ModuleEntity $module, UpdateModuleDTO $dto): ModuleEntity
    {
        try {
            return $this->moduleService->updateModule($module->id, $dto->toArray());
        } catch (\Exception $e) {
            throw new \Exception("Failed to update module '{$module->name}': {$e->getMessage()}");
        }
    }
}
