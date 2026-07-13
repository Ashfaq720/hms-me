<?php

namespace App\Domain\Role\ValueObjects;

class RolePriority
{
    public const SUPER_ADMIN = 100;
    public const ADMIN = 80;
    public const MANAGER = 60;
    public const SUPERVISOR = 40;
    public const STAFF = 20;
    public const USER = 10;

    public function __construct(
        public readonly int $value
    ) {
        $this->validate();
    }

    public static function fromString(string $level): self
    {
        $value = match (strtolower($level)) {
            'super_admin', 'superadmin' => self::SUPER_ADMIN,
            'admin' => self::ADMIN,
            'manager' => self::MANAGER,
            'supervisor' => self::SUPERVISOR,
            'staff' => self::STAFF,
            'user' => self::USER,
            default => throw new \InvalidArgumentException("Invalid priority level: {$level}")
        };

        return new self($value);
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return match ($this->value) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::MANAGER => 'Manager',
            self::SUPERVISOR => 'Supervisor',
            self::STAFF => 'Staff',
            self::USER => 'User',
            default => 'Custom (' . $this->value . ')'
        };
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function isHigherThan(RolePriority $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLowerThan(RolePriority $other): bool
    {
        return $this->value < $other->value;
    }

    public function equals(RolePriority $other): bool
    {
        return $this->value === $other->value;
    }

    public function isSuperAdmin(): bool
    {
        return $this->value >= self::SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->value >= self::ADMIN;
    }

    public function canManageRole(RolePriority $targetRole): bool
    {
        return $this->value > $targetRole->value;
    }

    private function validate(): void
    {
        if ($this->value < 0 || $this->value > 100) {
            throw new \InvalidArgumentException("Priority value must be between 0 and 100");
        }
    }
}
