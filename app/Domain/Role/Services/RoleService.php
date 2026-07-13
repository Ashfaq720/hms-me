<?php

namespace App\Domain\Role\Services;

use App\Domain\Role\Entities\RoleEntity;
use App\Domain\Role\Repositories\RoleRepositoryInterface;
use App\Domain\Role\ValueObjects\RolePriority;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class RoleService
{
    protected RoleRepositoryInterface $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * Create a new role
     */
    public function create(array $data): RoleEntity
    {
        DB::beginTransaction();

        try {
            $this->validateRoleData($data);

            if ($this->roleRepository->findByName($data['name'])) {
                throw new \Exception("Role with name '{$data['name']}' already exists");
            }

            $priority = isset($data['priority'])
                ? RolePriority::fromInt($data['priority'])
                : RolePriority::fromString('user');

            $role = $this->roleRepository->create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'web',
                'display_name' => $data['display_name'] ?? $data['name'],
                'description' => $data['description'] ?? '',
                'is_active' => $data['is_active'] ?? true,
                'is_system' => $data['is_system'] ?? false,
                'priority' => $priority->getValue(),
            ]);

            DB::commit();

            return $role;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Unable to create role: {$e->getMessage()}");
        }
    }

    /**
     * Update an existing role
     */
    public function update(RoleEntity $role, array $data): RoleEntity
    {
        DB::beginTransaction();

        try {
            if ($role->isSystem() && !auth()->user()->hasRole('super-admin')) {
                throw new \Exception("Cannot modify system role");
            }

            if (isset($data['name']) && $data['name'] !== $role->name) {
                if ($this->roleRepository->findByName($data['name'])) {
                    throw new \Exception("Role with name '{$data['name']}' already exists");
                }
            }

            if (isset($data['priority'])) {
                $priority = is_int($data['priority'])
                    ? RolePriority::fromInt($data['priority'])
                    : RolePriority::fromString($data['priority']);
                $data['priority'] = $priority->getValue();
            }

            $updatedRole = $this->roleRepository->update($role->id, array_filter($data, fn($value) => $value !== null));

            DB::commit();

            return $updatedRole;
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception("Unable to update role: {$e->getMessage()}");
        }
    }

    /**
     * Delete a role
     */
    public function delete(RoleEntity $role): bool
    {
        DB::beginTransaction();

        try {
            if ($role->isSystem()) {
                throw new \Exception("Cannot delete system role");
            }

            if ($role->usersCount > 0) {
                throw new \Exception("Cannot delete role with assigned users");
            }

            $deleted = $this->roleRepository->delete($role->id);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Unable to delete role: {$e->getMessage()}");
        }
    }

    /**
     * Get role with permissions
     */
    public function findWithPermissions(int $roleId): ?RoleEntity
    {
        try {
            return $this->roleRepository->findWithPermissions($roleId);
        } catch (\Exception $e) {
            throw new \Exception("Unable to retrieve role: {$e->getMessage()}");
        }
    }

    /**
     * Get all active roles
     */
    public function getActiveRoles(): Collection
    {
        try {
            return $this->roleRepository->findActiveRoles()
                ->filter(fn($role) => $role->name !== 'Super Admin');

        } catch (\Exception $e) {
            throw new \Exception("Unable to retrieve active roles");
        }
    }

    /**
     * Find role by ID
     */
    public function findById(int $id): ?RoleEntity
    {
        return $this->roleRepository->find($id);
    }

    /**
     * Find role by name
     */
    public function findByName(string $name): ?RoleEntity
    {
        return $this->roleRepository->findByName($name);
    }

    /**
     * Activate role
     */
    public function activate(RoleEntity $role): RoleEntity
    {
        return $this->roleRepository->update($role->id, ['is_active' => true]);
    }

    /**
     * Deactivate role
     */
    public function deactivate(RoleEntity $role): RoleEntity
    {
        return $this->roleRepository->update($role->id, ['is_active' => false]);
    }

    /**
     * Validate role data
     */
    private function validateRoleData(array $data): void
    {
        $required = ['name'];
        $missing = [];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new \Exception('Missing required fields: ' . implode(', ', $missing));
        }

        if (isset($data['name']) && !preg_match('/^[a-zA-Z0-9_-]+$/', $data['name'])) {
            throw new \Exception('Role name can only contain letters, numbers, hyphens, and underscores');
        }
    }
}
