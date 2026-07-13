<?php

namespace App\Domain\User\Entities;

class UserEntity
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $email,
        public bool $isActive,
        public ?string $password = null,
        public ?array $roles = [],
        public ?array $permissions = [],
        public ?\DateTimeInterface $emailVerifiedAt = null,
        public ?\DateTimeInterface $createdAt = null,
        public ?\DateTimeInterface $updatedAt = null,
    ) {}

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Activate the user
     */
    public function activate(): self
    {
        $this->isActive = true;
        return $this;
    }

    /**
     * Deactivate the user
     */
    public function deactivate(): self
    {
        $this->isActive = false;
        return $this;
    }

    /**
     * Check if user has verified email
     */
    public function hasVerifiedEmail(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles ?? []);
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Get user display name
     */
    public function getDisplayName(): string
    {
        return $this->name;
    }

    /**
     * Convert entity to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->isActive,
            'email_verified_at' => $this->emailVerifiedAt?->format('Y-m-d H:i:s'),
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
            email: $data['email'],
            isActive: $data['is_active'] ?? true,
            password: $data['password'] ?? null,
            roles: $data['roles'] ?? [],
            permissions: $data['permissions'] ?? [],
            emailVerifiedAt: isset($data['email_verified_at']) 
                ? new \DateTime($data['email_verified_at']) 
                : null,
            createdAt: isset($data['created_at']) 
                ? new \DateTime($data['created_at']) 
                : null,
            updatedAt: isset($data['updated_at']) 
                ? new \DateTime($data['updated_at']) 
                : null,
        );
    }
}
