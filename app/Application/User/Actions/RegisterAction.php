<?php

namespace App\Application\User\Actions;

use App\Application\User\DTO\RegisterDTO;
use App\Application\User\UseCases\RegisterUserUseCase;

class RegisterAction
{
    public function __construct(
        private readonly RegisterUserUseCase $registerUseCase
    ) {}

    public function execute(RegisterDTO $dto)
    {
        return $this->registerUseCase->execute($dto);
    }
}
