<?php

namespace App\Application\User\DTO;

class ForgotPasswordDTO
{
    public function __construct(
        public readonly string $email
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            email: $data['email']
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
        ];
    }
}
