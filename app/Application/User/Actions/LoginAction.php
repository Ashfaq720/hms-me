<?php

namespace App\Application\User\Actions;

use App\Application\User\DTO\LoginDTO;
use App\Application\User\UseCases\LoginUserUseCase;

class LoginAction
{
    public function __construct(
        private readonly LoginUserUseCase $loginUseCase
    ) {}

    public function execute(LoginDTO $dto): void
    {
        $this->loginUseCase->execute($dto);
    }
}
