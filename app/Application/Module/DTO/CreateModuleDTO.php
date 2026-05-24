<?php

namespace App\Application\Module\DTO;

class CreateModuleDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?string $icon = null,
        public readonly ?string $color = null,
        public readonly bool $isActive = true,
        public readonly bool $isSystem = false,
        public readonly ?int $sortOrder = null,
        public readonly array $permissions = []
    ) {
        $this->validate();
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            icon: $data['icon'] ?? null,
            color: $data['color'] ?? '#6c757d',
            isActive: (bool) ($data['is_active'] ?? true),
            isSystem: (bool) ($data['is_system'] ?? false),
            sortOrder: isset($data['sort_order']) ? (int) $data['sort_order'] : null,
            permissions: self::normalizePermissions($data)
        );
    }

    private static function normalizePermissions(array $data): array
    {
        $rows = $data['permissions'] ?? $data['modules'] ?? [];

        return collect($rows)
            ->map(fn($row) => [
                'id' => isset($row['id']) ? (int) $row['id'] : null,
                'name' => isset($row['name']) ? trim($row['name']) : '',
            ])
            ->filter(fn($row) => $row['name'] !== '')
            ->values()
            ->all();
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'is_active' => $this->isActive,
            'is_system' => $this->isSystem,
            'sort_order' => $this->sortOrder,
            'permissions' => $this->permissions,
        ];
    }

    private function validate(): void
    {
        if (empty(trim($this->name))) {
            throw new \InvalidArgumentException('Module name is required');
        }

        if (strlen($this->name) > 255) {
            throw new \InvalidArgumentException('Module name cannot exceed 255 characters');
        }

        if ($this->description !== null && strlen($this->description) > 1000) {
            throw new \InvalidArgumentException('Module description cannot exceed 1000 characters');
        }

        if ($this->icon !== null && strlen($this->icon) > 255) {
            throw new \InvalidArgumentException('Module icon cannot exceed 255 characters');
        }

        if ($this->color !== null && !preg_match('/^#[a-fA-F0-9]{6}$/', $this->color)) {
            throw new \InvalidArgumentException('Module color must be a valid hex color (e.g., #FF0000)');
        }

        if ($this->sortOrder !== null && ($this->sortOrder < 0 || $this->sortOrder > 999)) {
            throw new \InvalidArgumentException('Sort order must be between 0 and 999');
        }

        if (!is_array($this->permissions)) {
            throw new \InvalidArgumentException('Permissions must be an array');
        }
    }
}
