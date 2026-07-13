<?php

namespace App\Domain\Settings\Entities;

class SettingEntity
{
    public function __construct(
        public ?int $id,
        public string $key,
        public mixed $value,
        public string $type = 'string',
        public ?string $group = null,
        public ?string $description = null,
        public bool $isPublic = false,
        public bool $isActive = true,
        public ?\DateTimeInterface $createdAt = null,
        public ?\DateTimeInterface $updatedAt = null,
    ) {}

    /**
     * Get the setting value based on its type
     */
    public function getValue(): mixed
    {
        return match ($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json' => is_string($this->value) ? json_decode($this->value, true) : $this->value,
            'array' => is_string($this->value) ? json_decode($this->value, true) : $this->value,
            default => $this->value,
        };
    }

    /**
     * Set the setting value
     */
    public function setValue(mixed $value): self
    {
        $this->value = match ($this->type) {
            'json', 'array' => is_array($value) ? json_encode($value) : $value,
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            default => $value,
        };
        return $this;
    }

    /**
     * Check if setting is active
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Check if setting is public
     */
    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    /**
     * Activate the setting
     */
    public function activate(): self
    {
        $this->isActive = true;
        return $this;
    }

    /**
     * Deactivate the setting
     */
    public function deactivate(): self
    {
        $this->isActive = false;
        return $this;
    }

    /**
     * Make the setting public
     */
    public function makePublic(): self
    {
        $this->isPublic = true;
        return $this;
    }

    /**
     * Make the setting private
     */
    public function makePrivate(): self
    {
        $this->isPublic = false;
        return $this;
    }

    /**
     * Check if value is of expected type
     */
    public function isValidType(): bool
    {
        return match ($this->type) {
            'boolean' => is_bool($this->getValue()),
            'integer' => is_int($this->getValue()),
            'float' => is_float($this->getValue()),
            'json', 'array' => is_array($this->getValue()),
            'string' => is_string($this->value),
            default => true,
        };
    }

    /**
     * Convert entity to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'value' => $this->getValue(),
            'type' => $this->type,
            'group' => $this->group,
            'description' => $this->description,
            'is_public' => $this->isPublic,
            'is_active' => $this->isActive,
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
            key: $data['key'],
            value: $data['value'],
            type: $data['type'] ?? 'string',
            group: $data['group'] ?? null,
            description: $data['description'] ?? null,
            isPublic: $data['is_public'] ?? false,
            isActive: $data['is_active'] ?? true,
            createdAt: isset($data['created_at']) 
                ? new \DateTime($data['created_at']) 
                : null,
            updatedAt: isset($data['updated_at']) 
                ? new \DateTime($data['updated_at']) 
                : null,
        );
    }
}
