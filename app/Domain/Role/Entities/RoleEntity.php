<?php

namespace App\Domain\Role\Entities;

class RoleEntity
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $guardName,
        public ?string $displayName = null,
        public ?string $description = null,
        public bool $isActive = true,
        public bool $isSystem = false,
        public int $priority = 0,
        public ?array $permissions = [],
        public int $usersCount = 0,
        public ?\DateTimeInterface $createdAt = null,
        public ?\DateTimeInterface $updatedAt = null,
    ) {}

    /**
     * Check if role is active
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Check if role is a system role (cannot be deleted)
     */
    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    /**
     * Activate the role
     */
    public function activate(): self
    {
        $this->isActive = true;
        return $this;
    }

    /**
     * Deactivate the role
     */
    public function deactivate(): self
    {
        $this->isActive = false;
        return $this;
    }

    /**
     * Get display name or name as fallback
     */
    public function getDisplayName(): string
    {
        return $this->displayName ?? $this->name;
    }

    /**
     * Get permissions count
     */
    public function getPermissionsCount(): int
    {
        return count($this->permissions ?? []);
    }

    /**
     * Get users count
     */
    public function getUsersCount(): int
    {
        return $this->usersCount;
    }

    /**
     * Check if role can be deleted
     */
    public function canBeDeleted(): bool
    {
        return !$this->isSystem() && $this->usersCount === 0;
    }

    /**
     * Get role hierarchy level
     */
    public function getHierarchyLevel(): int
    {
        return $this->priority;
    }

    /**
     * Check if role has higher priority than another role
     */
    public function hasHigherPriorityThan(RoleEntity $otherRole): bool
    {
        return $this->getHierarchyLevel() > $otherRole->getHierarchyLevel();
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Convert entity to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guardName,
            'display_name' => $this->displayName,
            'description' => $this->description,
            'is_active' => $this->isActive,
            'is_system' => $this->isSystem,
            'priority' => $this->priority,
            'permissions_count' => $this->getPermissionsCount(),
            'users_count' => $this->usersCount,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Create entity from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            guardName: $data['guard_name'] ?? 'web',
            displayName: $data['display_name'] ?? null,
            description: $data['description'] ?? null,
            isActive: $data['is_active'] ?? true,
            isSystem: $data['is_system'] ?? false,
            priority: $data['priority'] ?? 0,
            permissions: $data['permissions'] ?? [],
            usersCount: $data['users_count'] ?? 0,
            createdAt: isset($data['created_at']) 
                ? new \DateTime($data['created_at']) 
                : null,
            updatedAt: isset($data['updated_at']) 
                ? new \DateTime($data['updated_at']) 
                : null,
        );
    }
}
