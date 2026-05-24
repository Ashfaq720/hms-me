<?php

namespace App\Application\User\UseCases;

use App\Domain\User\Entities\UserEntity;
use App\Application\User\DTO\RegisterDTO;
use App\Domain\User\Services\UserService;
use App\Models\User;

class RegisterUserUseCase
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function execute(RegisterDTO $dto): UserEntity
    {
        $existingUser = $this->userService->findByEmail($dto->email);

        if ($existingUser) {
            throw new \Exception('Email already exists.');
        }

        $user = $this->userService->createUser($dto->toArray());

        // For authentication, we need to get the actual Eloquent model
        $eloquentUser = User::where('email', $dto->email)->first();
        if ($eloquentUser) {
            auth()->login($eloquentUser);
        }

        return $user;
    }
}
