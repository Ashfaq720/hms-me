<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTO\LoginDTO;
use App\Domain\User\Entities\UserEntity;
use App\Domain\User\Services\AuthenticationService;

class LoginUserUseCase
{
    public function __construct(
        private readonly AuthenticationService $authService
    ) {}

    public function execute(LoginDTO $dto): UserEntity
    {
        $user = $this->authService->attemptLogin(
            $dto->email,
            $dto->password,
            $dto->remember
        );

        if (!$user) {
            throw new \Exception('Invalid credentials provided.');
        }

        return $user;
    }
}
