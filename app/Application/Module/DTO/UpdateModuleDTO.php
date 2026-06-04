<?php

namespace App\Application\Module\DTO;

class UpdateModuleDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $icon = null,
        public readonly ?string $color = null,
        public readonly ?bool $isActive = null,
        public readonly ?int $sortOrder = null,
        public readonly ?array $permissions = null
    ) {
        $this->validate();
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            icon: $data['icon'] ?? null,
            color: $data['color'] ?? null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : null,
            sortOrder: isset($data['sort_order']) ? (int) $data['sort_order'] : null,
            permissions: self::normalizePermissions($data)
        );
    }

    private static function normalizePermissions(array $data): ?array
    {
        if (!array_key_exists('permissions', $data) && !array_key_exists('modules', $data)) {
            return null;
        }

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
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
            'permissions' => $this->permissions,
        ], fn($value) => $value !== null);
    }

    private function validate(): void
    {
        if ($this->name !== null) {
            if (empty(trim($this->name))) {
                throw new \InvalidArgumentException('Module name cannot be empty');
            }

            if (strlen($this->name) > 255) {
                throw new \InvalidArgumentException('Module name cannot exceed 255 characters');
            }
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

        if ($this->permissions !== null && !is_array($this->permissions)) {
            throw new \InvalidArgumentException('Permissions must be an array');
        }
    }
}
