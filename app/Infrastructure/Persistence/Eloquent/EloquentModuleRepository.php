<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Module\Entities\ModuleEntity;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class EloquentModuleRepository implements ModuleRepositoryInterface
{
    public function __construct(
        private Module $model
    ) {}

    /**
     * Get all modules with optional filtering
     */
    public function getAll(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        $this->applyFilters($query, $filters);

        return $query->orderBy('sort_order')->orderBy('name')->get()
            ->map(fn($module) => $this->toEntity($module));
    }

    /**
     * Get paginated modules
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        $this->applyFilters($query, $filters);

        $paginated = $query->with('permissions')
                    ->withCount('permissions')
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->paginate($perPage);

        $paginated->setCollection(
            $paginated->getCollection()->map(fn($module) => $this->toEntity($module))
        );
        return $paginated;
    }

    /**
     * Find module by ID
     */
    public function findById(int $id): ?ModuleEntity
    {
        $module = $this->model->with('permissions')->find($id);
        return $module ? $this->toEntity($module) : null;
    }

    /**
     * Create a new module
     */
    public function create(array $data): ModuleEntity
    {
        $permissions = $data['permissions'] ?? null;
        unset($data['permissions']);

        // Generate slug if not provided
        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = \Str::slug($data['name']);
        }

        // Set default sort_order if not provided
        if (!isset($data['sort_order'])) {
            $data['sort_order'] = $this->model->max('sort_order') + 1;
        }

        $module = $this->model->create($data);

        if (is_array($permissions)) {
            $this->syncPermissions($module, $permissions);
        }

        return $this->toEntity($module->fresh('permissions'));
    }

    /**
     * Update an existing module
     */
    public function update(int $id, array $data): ModuleEntity
    {
        $module = $this->model->find($id);

        if (!$module) {
            throw new \Exception("Module with ID {$id} not found");
        }

        $permissions = $data['permissions'] ?? null;
        unset($data['permissions']);

        // Generate slug if name is being updated and slug not provided
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = \Str::slug($data['name']);
        }

        $module->update($data);

        if (is_array($permissions)) {
            $this->syncPermissions($module, $permissions);
        }

        return $this->toEntity($module->fresh('permissions'));
    }

    /**
     * Sync the module's permissions: update existing rows by id, insert new ones,
     * and delete any of the module's existing permissions that are not in the list.
     *
     * @param array<int,array{id:?int,name:string}> $permissions
     */
    private function syncPermissions(Module $module, array $permissions): void
    {
        $guard = config('auth.defaults.guard', 'web');
        $keptIds = [];

        foreach ($permissions as $row) {
            $name = trim($row['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $existing = !empty($row['id'])
                ? Permission::where('id', $row['id'])->where('module_id', $module->id)->first()
                : null;

            if ($existing) {
                if ($existing->name !== $name) {
                    $existing->name = $name;
                    $existing->save();
                }
                $keptIds[] = $existing->id;
            } else {
                $created = Permission::create([
                    'module_id' => $module->id,
                    'name' => $name,
                    'guard_name' => $guard,
                ]);
                $keptIds[] = $created->id;
            }
        }

        Permission::where('module_id', $module->id)
            ->when(!empty($keptIds), fn($q) => $q->whereNotIn('id', $keptIds))
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Delete a module
     */
    public function delete(int $id): bool
    {
        $module = $this->model->find($id);
        return $module ? $module->delete() : false;
    }

    /**
     * Check if module exists by name
     */
    public function existsByName(string $name, int $excludeId = null): bool
    {
        $query = $this->model->where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get active modules only
     */
    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($module) => $this->toEntity($module));
    }

    /**
     * Get modules with permissions count
     */
    public function getWithPermissionsCount(): Collection
    {
        return $this->model->withCount('permissions')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($module) => $this->toEntity($module));
    }

    /**
     * Search modules by name or description
     */
    public function search(string $query): Collection
    {
        return $this->model->where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($module) => $this->toEntity($module));
    }

    /**
     * Apply filters to query
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'active':
                    $query->where('is_active', $value);
                    break;
                case 'system':
                    $query->where('is_system', $value);
                    break;
                case 'name':
                    $query->where('name', 'LIKE', "%{$value}%");
                    break;
                case 'slug':
                    $query->where('slug', $value);
                    break;
                default:
                    $query->where($key, $value);
                    break;
            }
        }
    }

    /**
     * Convert Module to ModuleEntity
     */
    private function toEntity(Module $model): ModuleEntity
    {
        return new ModuleEntity(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            icon: $model->icon,
            color: $model->color,
            sortOrder: $model->sort_order ?? 0,
            version: $model->version,
            isActive: $model->is_active ?? true,
            isSystem: $model->is_system ?? false,
            permissions: $model->permissions?->pluck('name')->toArray() ?? [],
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}
