<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Settings\Entities\SettingEntity;
use App\Domain\Settings\Repositories\SettingRepositoryInterface;
use App\Models\Setting;
use Illuminate\Support\Collection;

class EloquentSettingRepository implements SettingRepositoryInterface
{
    public function __construct(
        private Setting $model
    ) {}

    public function all(): Collection
    {
        return $this->model->all()->map(fn($setting) => $this->toEntity($setting));
    }

    public function find(int $id): ?SettingEntity
    {
        $setting = $this->model->find($id);
        return $setting ? $this->toEntity($setting) : null;
    }

    public function create(array $data): SettingEntity
    {
        $setting = $this->model->create($data);
        return $this->toEntity($setting);
    }

    public function update(int $id, array $data): SettingEntity
    {
        $setting = $this->model->find($id);
        $setting->update($data);
        return $this->toEntity($setting->fresh());
    }

    public function delete(int $id): bool
    {
        $setting = $this->model->find($id);
        return $setting ? $setting->delete() : false;
    }

    public function findByKey(string $key): ?SettingEntity
    {
        $setting = $this->model->where('key', $key)->first();
        return $setting ? $this->toEntity($setting) : null;
    }

    public function findActiveByKey(string $key): ?SettingEntity
    {
        $setting = $this->model->where('key', $key)
            ->where('is_active', true)
            ->first();
        return $setting ? $this->toEntity($setting) : null;
    }

    public function findByGroup(string $group): Collection
    {
        return $this->model->where('group', $group)
            ->where('is_active', true)
            ->orderBy('key')
            ->get()
            ->map(fn($setting) => $this->toEntity($setting));
    }

    public function findPublicSettings(): Collection
    {
        return $this->model->where('is_public', true)
            ->where('is_active', true)
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->map(fn($setting) => $this->toEntity($setting));
    }

    public function findAllGroups(): Collection
    {
        return $this->model->where('is_active', true)
            ->select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group');
    }

    public function updateByKey(string $key, array $data): SettingEntity
    {
        $setting = $this->model->where('key', $key)->first();

        if ($setting) {
            $setting->update($data);
            return $this->toEntity($setting->fresh());
        }

        return $this->create(array_merge($data, ['key' => $key]));
    }

    public function deleteByKey(string $key): bool
    {
        $setting = $this->model->where('key', $key)->first();

        if ($setting) {
            return $setting->delete();
        }

        return false;
    }

    public function searchByKeyword(string $keyword): Collection
    {
        return $this->model->where('key', 'LIKE', "%{$keyword}%")
            ->orWhere('description', 'LIKE', "%{$keyword}%")
            ->where('is_active', true)
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->map(fn($setting) => $this->toEntity($setting));
    }

    public function findWhere(array $criteria): Collection
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            $query->where($key, $value);
        }

        return $query->get()->map(fn($setting) => $this->toEntity($setting));
    }

    public function findWhereFirst(array $criteria): ?SettingEntity
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            $query->where($key, $value);
        }

        $setting = $query->first();
        return $setting ? $this->toEntity($setting) : null;
    }

    public function paginate(int $perPage = 15, array $criteria = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            $query->where($key, $value);
        }

        $paginated = $query->paginate($perPage);
        $paginated->setCollection(
            $paginated->getCollection()->map(fn($setting) => $this->toEntity($setting))
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
     * Convert Setting to SettingEntity
     */
    private function toEntity(Setting $model): SettingEntity
    {
        return new SettingEntity(
            id: $model->id,
            key: $model->key,
            value: $model->value,
            type: $model->type ?? 'string',
            group: $model->group,
            description: $model->description,
            isPublic: $model->is_public ?? false,
            isActive: $model->is_active ?? true,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}
