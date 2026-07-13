<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Role\Entities\RoleEntity;
use App\Domain\Role\Repositories\RoleRepositoryInterface;
use App\Models\Role;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        private Role $model
    ) {}

    public function all(): Collection
    {
        return $this->model->all()->map(fn($role) => $this->toEntity($role));
    }

    public function find(int $id): ?RoleEntity
    {
        $role = $this->model->find($id);
        return $role ? $this->toEntity($role) : null;
    }

    public function create(array $data): RoleEntity
    {
        $role = $this->model->create($data);
        return $this->toEntity($role);
    }

    public function update(int $id, array $data): RoleEntity
    {
        $role = $this->model->find($id);

        $permissions = $data['permissions'] ?? null;
        unset($data['permissions']);

        $role->update($data);

        if (is_array($permissions)) {
            $permissionModels = Permission::whereIn('id', array_map('intval', $permissions))->get();
            $role->syncPermissions($permissionModels);
        }

        return $this->toEntity($role->fresh('permissions'));
    }

    public function delete(int $id): bool
    {
        $role = $this->model->find($id);
        return $role ? $role->delete() : false;
    }

    public function findByName(string $name): ?RoleEntity
    {
        $role = $this->model->where('name', $name)->first();
        return $role ? $this->toEntity($role) : null;
    }

    public function findActiveRoles(): Collection
    {
        return $this->model->withCount('users')
            ->where('is_active', true)
            // ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get()
            ->map(fn($role) => $this->toEntity($role));
    }

    public function findNonSystemRoles(): Collection
    {
        return $this->model->where('is_system', false)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get()
            ->map(fn($role) => $this->toEntity($role));
    }

    public function findByPriorityRange(int $minPriority, int $maxPriority): Collection
    {
        return $this->model->where('is_active', true)
            ->whereBetween('priority', [$minPriority, $maxPriority])
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get()
            ->map(fn($role) => $this->toEntity($role));
    }

    public function findWithPermissions(int $roleId): ?RoleEntity
    {
        $role = $this->model->with(['permissions'])->find($roleId);
        return $role ? $this->toEntity($role) : null;
    }

    public function findWithUsers(int $roleId): ?RoleEntity
    {
        $role = $this->model->with(['users'])->find($roleId);
        return $role ? $this->toEntity($role) : null;
    }

    public function findWithPermissionsAndUsers(): Collection
    {
        return $this->model->with(['permissions', 'users'])
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get()
            ->map(fn($role) => $this->toEntity($role));
    }

    public function searchByName(string $name): Collection
    {
        return $this->model->where('name', 'LIKE', "%{$name}%")
            ->orWhere('display_name', 'LIKE', "%{$name}%")
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get()
            ->map(fn($role) => $this->toEntity($role));
    }

    public function findDeletableRoles(): Collection
    {
        return $this->model->where('is_system', false)
            ->whereDoesntHave('users')
            ->orderBy('name')
            ->get()
            ->map(fn($role) => $this->toEntity($role));
    }

    public function findHigherPriorityRoles(int $priority): Collection
    {
        return $this->model->where('priority', '>', $priority)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get()
            ->map(fn($role) => $this->toEntity($role));
    }

    public function countByPriority(): array
    {
        return $this->model->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->orderBy('priority')
            ->pluck('count', 'priority')
            ->toArray();
    }

    public function findWhere(array $criteria): Collection
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            $query->where($key, $value);
        }

        return $query->get()->map(fn($role) => $this->toEntity($role));
    }

    public function findWhereFirst(array $criteria): ?RoleEntity
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            $query->where($key, $value);
        }

        $role = $query->first();
        return $role ? $this->toEntity($role) : null;
    }

    public function paginate(int $perPage = 15, array $criteria = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            $query->where($key, $value);
        }

        $paginated = $query->paginate($perPage);
        $paginated->setCollection(
            $paginated->getCollection()->map(fn($role) => $this->toEntity($role))
        );
        return $paginated;
    }

    public function exists(): bool
    {
        return $this->model->exists();
    }

    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Convert Role to RoleEntity
     */
    private function toEntity(Role $model): RoleEntity
    {
        return new RoleEntity(
            id: $model->id,
            name: $model->name,
            guardName: $model->guard_name ?? 'web',
            displayName: $model->display_name,
            description: $model->description,
            isActive: $model->is_active ?? true,
            isSystem: $model->is_system ?? false,
            priority: $model->priority ?? 0,
            permissions: $model->permissions?->pluck('name')->toArray() ?? [],
            usersCount: $model->users_count ?? $model->users?->count() ?? 0,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}
