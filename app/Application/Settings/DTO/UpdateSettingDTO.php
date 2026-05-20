<?php

namespace App\Application\Settings\DTO;

class UpdateSettingDTO
{
    public function __construct(
        public readonly string $key,
        public readonly mixed $value,
        public readonly string $type = 'string',
        public readonly ?string $group = null,
        public readonly ?string $description = null,
        public readonly ?bool $isPublic = null,
        public readonly ?bool $isActive = null
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            key: $data['key'],
            value: $data['value'],
            type: $data['type'] ?? 'string',
            group: $data['group'] ?? null,
            description: $data['description'] ?? null,
            isPublic: isset($data['is_public']) ? (bool) $data['is_public'] : null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
            'group' => $this->group,
            'description' => $this->description,
            'is_public' => $this->isPublic,
            'is_active' => $this->isActive,
        ], fn($value) => $value !== null);
    }
}
