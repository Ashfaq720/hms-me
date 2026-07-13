<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\UserEntity;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    /**
     * Find user by ID
     */
    public function find(int $id): ?UserEntity;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?UserEntity;

    /**
     * Get all users
     */
    public function all(): Collection;

    /**
     * Paginate users
     */
    public function paginate(int $perPage = 15);

    /**
     * Create a new user
     */
    public function create(array $data): UserEntity;

    /**
     * Update an existing user
     */
    public function update(UserEntity $entity, array $data): UserEntity;

    /**
     * Delete a user
     */
    public function delete(UserEntity $entity): bool;

    /**
     * Get active users
     */
    public function getActiveUsers(): Collection;

    /**
     * Search users by name
     */
    public function searchByName(string $name): Collection;
}
