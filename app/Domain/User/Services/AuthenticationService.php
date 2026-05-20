<?php

namespace App\Domain\User\Services;

use Illuminate\Support\Facades\Hash;
use App\Domain\User\Entities\UserEntity;
use App\Models\User;
use App\Domain\User\Repositories\UserRepositoryInterface;

class AuthenticationService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function attemptLogin(string $email, string $password, bool $remember = false): ?UserEntity
    {
        // First get the Eloquent model to check password
        $eloquentUser = User::where('email', $email)->first();

        if (!$eloquentUser || !Hash::check($password, $eloquentUser->password)) {
            return null;
        }
        
        if (!$eloquentUser->is_active) {
            throw new \Exception('Your account has been deactivated.');
        }

        // Now get the entity for the domain layer
        $userRepository = app(UserRepositoryInterface::class);
        $user = $userRepository->findByEmail($email);

        // Login with the Eloquent model
        auth()->login($eloquentUser, $remember);

        return $user;
    }

    public function logout(): void
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    public function sendPasswordResetLink(string $email): string
    {
        $status = \Illuminate\Support\Facades\Password::sendResetLink(['email' => $email]);

        return $status;
    }

    public function resetPassword(string $email, string $password, string $token): bool
    {
        $status = \Illuminate\Support\Facades\Password::reset(
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
                'token' => $token,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET;
    }
}
