<?php

namespace App\Application\Module\Actions;

use App\Application\Module\DTO\CreateModuleDTO;
use App\Domain\Module\Services\ModuleService;
use App\Domain\Module\Entities\ModuleEntity;

class CreateModuleAction
{
    public function __construct(
        private readonly ModuleService $moduleService
    ) {}

    public function execute(CreateModuleDTO $dto): ModuleEntity
    {
        try {
            return $this->moduleService->createModule($dto->toArray());
        } catch (\Exception $e) {
            throw new \Exception("Failed to create module '{$dto->name}': {$e->getMessage()}");
        }
    }
}
