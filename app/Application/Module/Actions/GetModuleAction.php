<?php

namespace App\Application\Module\Actions;

use App\Domain\Module\Services\ModuleService;
use App\Domain\Module\Entities\ModuleEntity;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class GetModuleAction
{
    public function __construct(
        private readonly ModuleService $moduleService
    ) {}

    public function execute(int $id): ModuleEntity
    {
        try {
            return $this->moduleService->getModuleById($id);
        } catch (\Exception $e) {
            throw new \Exception("Failed to retrieve module with ID {$id}: {$e->getMessage()}");
        }
    }

    public function getAll(array $filters = []): Collection
    {
        try {
            return $this->moduleService->getAllModules($filters);
        } catch (\Exception $e) {
            throw new \Exception("Failed to retrieve modules: {$e->getMessage()}");
        }
    }

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        try {
            return $this->moduleService->getPaginatedModules($perPage, $filters);
        } catch (\Exception $e) {
            throw new \Exception("Failed to retrieve paginated modules: {$e->getMessage()}");
        }
    }

    public function getActiveModules(): Collection
    {
        try {
            return $this->moduleService->getActiveModules();
        } catch (\Exception $e) {
            throw new \Exception("Failed to retrieve active modules: {$e->getMessage()}");
        }
    }
}
