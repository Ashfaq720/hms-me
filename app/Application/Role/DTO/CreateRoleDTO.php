<?php

namespace App\Application\Role\DTO;

class CreateRoleDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $guardName = 'web',
        public readonly ?string $displayName = null,
        public readonly ?string $description = null,
        public readonly bool $isActive = true,
        public readonly bool $isSystem = false,
        public readonly int $priority = 10,
        public readonly array $permissions = []
    ) {
        $this->validate();
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            guardName: $data['guard_name'] ?? 'web',
            displayName: $data['display_name'] ?? null,
            description: $data['description'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            isSystem: (bool) ($data['is_system'] ?? false),
            priority: (int) ($data['priority'] ?? 10),
            permissions: $data['permissions'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'guard_name' => $this->guardName,
            'display_name' => $this->displayName ?? $this->name,
            'description' => $this->description,
            'is_active' => $this->isActive,
            'is_system' => $this->isSystem,
            'priority' => $this->priority,
            'permissions' => $this->permissions,
        ];
    }

    private function validate(): void
    {
        if (empty(trim($this->name))) {
            throw new \InvalidArgumentException('Role name is required');
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $this->name)) {
            throw new \InvalidArgumentException('Role name can only contain letters, numbers, hyphens, and underscores');
        }

        if ($this->priority < 0 || $this->priority > 100) {
            throw new \InvalidArgumentException('Priority must be between 0 and 100');
        }

        if (!is_array($this->permissions)) {
            throw new \InvalidArgumentException('Permissions must be an array');
        }
    }
}
