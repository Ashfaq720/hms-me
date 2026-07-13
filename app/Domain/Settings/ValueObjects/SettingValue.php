<?php

namespace App\Domain\Settings\ValueObjects;

class SettingValue
{
    public function __construct(
        public readonly mixed $value,
        public readonly string $type
    ) {
        $this->validate();
    }

    public static function fromRaw($value, string $type): self
    {
        return new self($value, $type);
    }

    public function getValue(): mixed
    {
        return match ($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json' => is_string($this->value) ? json_decode($this->value, true) : $this->value,
            'array' => is_string($this->value) ? json_decode($this->value, true) : (array) $this->value,
            default => (string) $this->value,
        };
    }

    public function getStorageValue(): mixed
    {
        return match ($this->type) {
            'json', 'array' => json_encode($this->value),
            default => $this->value,
        };
    }

    public function toString(): string
    {
        return match ($this->type) {
            'boolean' => $this->value ? 'true' : 'false',
            'json', 'array' => json_encode($this->value),
            default => (string) $this->value,
        };
    }

    public function equals(SettingValue $other): bool
    {
        return $this->value === $other->value && $this->type === $other->type;
    }

    private function validate(): void
    {
        $allowedTypes = ['string', 'integer', 'float', 'boolean', 'json', 'array'];
        
        if (!in_array($this->type, $allowedTypes)) {
            throw new \InvalidArgumentException("Invalid setting type: {$this->type}");
        }

        if ($this->type === 'json' && is_string($this->value)) {
            $decoded = json_decode($this->value);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException("Invalid JSON value for setting");
            }
        }
    }
}
