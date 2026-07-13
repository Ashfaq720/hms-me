<?php

namespace App\Domain\Module\Entities;

class ModuleEntity
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?string $icon = null,
        public ?string $color = null,
        public int $sortOrder = 0,
        public ?string $version = null,
        public bool $isActive = true,
        public bool $isSystem = false,
        public ?array $permissions = [],
        public ?\DateTimeInterface $createdAt = null,
        public ?\DateTimeInterface $updatedAt = null,
    ) {}

    /**
     * Check if module is active
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Check if module is a system module (cannot be deleted)
     */
    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    /**
     * Activate the module
     */
    public function activate(): self
    {
        $this->isActive = true;
        return $this;
    }

    /**
     * Deactivate the module
     */
    public function deactivate(): self
    {
        $this->isActive = false;
        return $this;
    }

    /**
     * Get display name
     */
    public function getDisplayName(): string
    {
        return $this->name;
    }

    /**
     * Get permissions count
     */
    public function getPermissionsCount(): int
    {
        return count($this->permissions ?? []);
    }

    /**
     * Check if module can be deleted
     */
    public function canBeDeleted(): bool
    {
        return !$this->isSystem() && $this->getPermissionsCount() === 0;
    }

    /**
     * Get module hierarchy level based on sort_order
     */
    public function getHierarchyLevel(): int
    {
        return $this->sortOrder;
    }

    /**
     * Check if module has higher hierarchy than another module
     */
    public function hasHigherHierarchyThan(ModuleEntity $otherModule): bool
    {
        return $this->getHierarchyLevel() > $otherModule->getHierarchyLevel();
    }

    /**
     * Get the module's icon with fallback
     */
    public function getIcon(): string
    {
        return $this->icon ?? 'fi fi-rr-apps';
    }

    /**
     * Get the module's color with fallback
     */
    public function getColor(): string
    {
        return $this->color ?? '#6c757d';
    }

    /**
     * Check if module has permissions
     */
    public function hasPermissions(): bool
    {
        return $this->getPermissionsCount() > 0;
    }

    /**
     * Check if module has a specific permission
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
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'sort_order' => $this->sortOrder,
            'version' => $this->version,
            'is_active' => $this->isActive,
            'is_system' => $this->isSystem,
            'permissions_count' => $this->getPermissionsCount(),
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
            slug: $data['slug'] ?? \Str::slug($data['name']),
            description: $data['description'] ?? null,
            icon: $data['icon'] ?? null,
            color: $data['color'] ?? null,
            sortOrder: $data['sort_order'] ?? 0,
            version: $data['version'] ?? null,
            isActive: $data['is_active'] ?? true,
            isSystem: $data['is_system'] ?? false,
            permissions: $data['permissions'] ?? [],
            createdAt: isset($data['created_at']) 
                ? new \DateTime($data['created_at']) 
                : null,
            updatedAt: isset($data['updated_at']) 
                ? new \DateTime($data['updated_at']) 
                : null,
        );
    }
}
