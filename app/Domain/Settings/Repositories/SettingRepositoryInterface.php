<?php

namespace App\Domain\Settings\Repositories;

use App\Domain\Settings\Entities\SettingEntity;
use Illuminate\Support\Collection;

interface SettingRepositoryInterface
{
    /**
     * Get all settings
     */
    public function all(): Collection;

    /**
     * Find setting by ID
     */
    public function find(int $id): ?SettingEntity;

    /**
     * Create a new setting
     */
    public function create(array $data): SettingEntity;

    /**
     * Update an existing setting
     */
    public function update(int $id, array $data): SettingEntity;

    /**
     * Delete a setting
     */
    public function delete(int $id): bool;

    /**
     * Find setting by key
     */
    public function findByKey(string $key): ?SettingEntity;

    /**
     * Find active setting by key
     */
    public function findActiveByKey(string $key): ?SettingEntity;

    /**
     * Find settings by group
     */
    public function findByGroup(string $group): Collection;

    /**
     * Find public settings
     */
    public function findPublicSettings(): Collection;

    /**
     * Find all groups
     */
    public function findAllGroups(): Collection;

    /**
     * Update setting by key
     */
    public function updateByKey(string $key, array $data): SettingEntity;

    /**
     * Delete setting by key
     */
    public function deleteByKey(string $key): bool;

    /**
     * Search settings by keyword
     */
    public function searchByKeyword(string $keyword): Collection;

    /**
     * Find settings by criteria
     */
    public function findWhere(array $criteria): Collection;

    /**
     * Find first setting by criteria
     */
    public function findWhereFirst(array $criteria): ?SettingEntity;

    /**
     * Paginate settings
     */
    public function paginate(int $perPage = 15, array $criteria = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    /**
     * Check if setting exists
     */
    public function exists(): bool;

    /**
     * Get setting count
     */
    public function count(): int;
}

