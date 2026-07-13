<?php

namespace App\Domain\Module\Repositories;

use App\Domain\Module\Entities\ModuleEntity;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ModuleRepositoryInterface
{
    /**
     * Get all modules with optional filtering
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Get paginated modules
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Find module by ID
     */
    public function findById(int $id): ?ModuleEntity;

    /**
     * Create a new module
     */
    public function create(array $data): ModuleEntity;

    /**
     * Update an existing module
     */
    public function update(int $id, array $data): ModuleEntity;

    /**
     * Delete a module
     */
    public function delete(int $id): bool;

    /**
     * Check if module exists by name
     */
    public function existsByName(string $name, int $excludeId = null): bool;

    /**
     * Get active modules only
     */
    public function getActive(): Collection;

    /**
     * Get modules with permissions count
     */
    public function getWithPermissionsCount(): Collection;

    /**
     * Search modules by name or description
     */
    public function search(string $query): Collection;
}

