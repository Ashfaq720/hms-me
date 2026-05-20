<?php

namespace App\Domain\Role\Repositories;

use App\Domain\Role\Entities\RoleEntity;
use Illuminate\Support\Collection;

interface RoleRepositoryInterface
{
    /**
     * Get all roles
     */
    public function all(): Collection;

    /**
     * Find RoleEntity by ID
     */
    public function find(int $id): ?RoleEntity;

    /**
     * Create a new RoleEntity
     */
    public function create(array $data): RoleEntity;

    /**
     * Update an existing RoleEntity
     */
    public function update(int $id, array $data): RoleEntity;

    /**
     * Delete a RoleEntity
     */
    public function delete(int $id): bool;

    /**
     * Find RoleEntity by name
     */
    public function findByName(string $name): ?RoleEntity;

    /**
     * Find all active roles
     */
    public function findActiveRoles(): Collection;

    /**
     * Find non-system roles
     */
    public function findNonSystemRoles(): Collection;

    /**
     * Find roles by priority range
     */
    public function findByPriorityRange(int $minPriority, int $maxPriority): Collection;

    /**
     * Find RoleEntity with permissions
     */
    public function findWithPermissions(int $roleId): ?RoleEntity;

    /**
     * Find RoleEntity with users
     */
    public function findWithUsers(int $roleId): ?RoleEntity;

    /**
     * Find roles with permissions and users
     */
    public function findWithPermissionsAndUsers(): Collection;

    /**
     * Search roles by name
     */
    public function searchByName(string $name): Collection;

    /**
     * Find roles that can be deleted
     */
    public function findDeletableRoles(): Collection;

    /**
     * Find roles with higher priority
     */
    public function findHigherPriorityRoles(int $priority): Collection;

    /**
     * Count roles by priority
     */
    public function countByPriority(): array;

    /**
     * Find roles by criteria
     */
    public function findWhere(array $criteria): Collection;

    /**
     * Find first RoleEntity by criteria
     */
    public function findWhereFirst(array $criteria): ?RoleEntity;

    /**
     * Paginate roles
     */
    public function paginate(int $perPage = 15, array $criteria = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    /**
     * Check if RoleEntity exists
     */
    public function exists(): bool;

    /**
     * Get RoleEntity count
     */
    public function count(): int;
}


