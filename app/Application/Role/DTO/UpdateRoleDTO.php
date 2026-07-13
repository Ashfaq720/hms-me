<?php

namespace App\Application\Role\DTO;

class UpdateRoleDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $displayName = null,
        public readonly ?string $description = null,
        public readonly ?bool $isActive = null,
        public readonly ?int $priority = null,
        public readonly ?array $permissions = null
    ) {
        $this->validate();
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            displayName: $data['display_name'] ?? null,
            description: $data['description'] ?? null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : null,
            priority: isset($data['priority']) ? (int) $data['priority'] : null,
            permissions: $data['permissions'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'display_name' => $this->displayName,
            'description' => $this->description,
            'is_active' => $this->isActive,
            'priority' => $this->priority,
            'permissions' => $this->permissions,
        ], fn($value) => $value !== null);
    }

    private function validate(): void
    {
        if ($this->name !== null) {
            if (empty(trim($this->name))) {
                throw new \InvalidArgumentException('Role name cannot be empty');
            }

            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $this->name)) {
                throw new \InvalidArgumentException('Role name can only contain letters, numbers, hyphens, and underscores');
            }
        }

        if ($this->priority !== null && ($this->priority < 0 || $this->priority > 100)) {
            throw new \InvalidArgumentException('Priority must be between 0 and 100');
        }

        if ($this->permissions !== null && !is_array($this->permissions)) {
            throw new \InvalidArgumentException('Permissions must be an array');
        }
    }
}
