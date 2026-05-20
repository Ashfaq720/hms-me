<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\User\Entities\UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private User $model
    ) {}

    public function find(int $id): ?UserEntity
    {
        $user = $this->model->find($id);
        return $user ? $this->toEntity($user) : null;
    }

    public function findByEmail(string $email): ?UserEntity
    {
        $user = $this->model->where('email', $email)->first();
        return $user ? $this->toEntity($user) : null;
    }

    public function all(): Collection
    {
        return $this->model->all()->map(fn($user) => $this->toEntity($user));
    }

    public function paginate(int $perPage = 15)
    {
        $paginated = $this->model->paginate($perPage);
        $paginated->setCollection(
            $paginated->getCollection()->map(fn($user) => $this->toEntity($user))
        );
        return $paginated;
    }

    public function create(array $data): UserEntity
    {
        $user = $this->model->create($data);
        return $this->toEntity($user);
    }

    public function update(UserEntity $entity, array $data): UserEntity
    {
        $user = $this->model->find($entity->id);
        $user->update($data);
        return $this->toEntity($user->fresh());
    }

    public function delete(UserEntity $entity): bool
    {
        $user = $this->model->find($entity->id);
        return $user ? $user->delete() : false;
    }

    public function getActiveUsers(): Collection
    {
        return $this->model->where('is_active', true)->get()
            ->map(fn($user) => $this->toEntity($user));
    }

    public function searchByName(string $name): Collection
    {
        return $this->model->where('name', 'like', "%{$name}%")->get()
            ->map(fn($user) => $this->toEntity($user));
    }

    /**
     * Convert Model to Entity
     */
    private function toEntity(User $model): UserEntity
    {
        return new UserEntity(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            isActive: $model->is_active ?? false,
            password: null, // Never expose password
            roles: $model->getRoleNames()->toArray(),
            permissions: $model->getAllPermissions()->pluck('name')->toArray(),
            emailVerifiedAt: $model->email_verified_at,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    /**
     * Get the underlying Eloquent model (for auth purposes)
     */
    public function getModel(int $id): ?User
    {
        return $this->model->find($id);
    }
}
