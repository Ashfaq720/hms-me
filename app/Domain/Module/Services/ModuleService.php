<?php

namespace App\Domain\Module\Services;

use App\Domain\Module\Entities\ModuleEntity;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class ModuleService
{
    protected ModuleRepositoryInterface $moduleRepository;

    public function __construct(ModuleRepositoryInterface $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * Get all modules with optional filtering
     */
    public function getAllModules(array $filters = []): Collection
    {
        return $this->moduleRepository->getAll($filters);
    }

    /**
     * Get paginated modules
     */
    public function getPaginatedModules(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->moduleRepository->getPaginated($perPage, $filters);
    }

    /**
     * Get a specific module by ID
     */
    public function getModuleById(int $id): ModuleEntity
    {
        $module = $this->moduleRepository->findById($id);

        if (!$module) {
            throw new ModelNotFoundException("Module with ID {$id} not found");
        }

        return $module;
    }

    /**
     * Create a new module
     */
    public function createModule(array $data): ModuleEntity
    {
        // Validate module data
        $this->validateModuleData($data);

        // Check if module name already exists
        if ($this->moduleRepository->existsByName($data['name'])) {
            throw new Exception("Module with name '{$data['name']}' already exists");
        }

        // Set default values
        $data['slug'] = $data['slug'] ?? \Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_system'] = $data['is_system'] ?? false;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // Create module
        return $this->moduleRepository->create($data);
    }

    /**
     * Update an existing module
     */
    public function updateModule(int $id, array $data): ModuleEntity
    {
        $module = $this->getModuleById($id);

        // Validate module data
        $this->validateModuleData($data, $id);

        // Check if module name already exists (excluding current module)
        if ($this->moduleRepository->existsByName($data['name'], $id)) {
            throw new Exception("Module with name '{$data['name']}' already exists");
        }

        // Set slug if name is being updated
        if (isset($data['name'])) {
            $data['slug'] = \Str::slug($data['name']);
        }

        // Update module
        return $this->moduleRepository->update($id, $data);
    }

    /**
     * Delete a module
     */
    public function deleteModule(int $id): bool
    {
        $module = $this->getModuleById($id);

        // Check if module can be deleted
        if ($module->isSystem || $module->getPermissionsCount() > 0) {
            throw new Exception("Cannot delete system module or module with assigned permissions");
        }

        return $this->moduleRepository->delete($id);
    }

    /**
     * Toggle module active status
     */
    public function toggleModuleStatus(int $id): ModuleEntity
    {
        $module = $this->getModuleById($id);

        $newStatus = !$module->isActive;

        return $this->moduleRepository->update($id, ['is_active' => $newStatus]);
    }

    /**
     * Get active modules only
     */
    public function getActiveModules(): Collection
    {
        return $this->moduleRepository->getActive();
    }

    /**
     * Get modules with their permissions count
     */
    public function getModulesWithPermissionsCount(): Collection
    {
        return $this->moduleRepository->getWithPermissionsCount();
    }

    /**
     * Search modules by name or description
     */
    public function searchModules(string $query): Collection
    {
        return $this->moduleRepository->search($query);
    }

    /**
     * Activate module
     */
    public function activateModule(int $id): ModuleEntity
    {
        return $this->moduleRepository->update($id, ['is_active' => true]);
    }

    /**
     * Deactivate module
     */
    public function deactivateModule(int $id): ModuleEntity
    {
        return $this->moduleRepository->update($id, ['is_active' => false]);
    }

    /**
     * Validate module data
     */
    protected function validateModuleData(array $data, int $excludeId = null): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ];

        $validator = validator($data, $rules);

        if ($validator->fails()) {
            throw new Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }
    }
}
