<?php

namespace App\Application\User\DTO;

class ResetPasswordDTO
{
    public function __construct(
        public readonly string $token,
        public readonly string $email,
        public readonly string $password
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            token: $data['token'],
            email: $data['email'],
            password: $data['password']
        );
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
