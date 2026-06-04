<?php

namespace App\Domain\User\Services;

use App\Domain\User\Entities\UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(array $data): UserEntity
    {
        return $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function updateUser(UserEntity $user, array $data): UserEntity
    {
        $updateData = [
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
        ];

        if (isset($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if (isset($data['is_active'])) {
            $updateData['is_active'] = $data['is_active'];
        }

        return $this->userRepository->update($user, $updateData);
    }

    public function deleteUser(UserEntity $user): bool
    {
        return $this->userRepository->delete($user);
    }

    public function findByEmail(string $email): ?UserEntity
    {
        return $this->userRepository->findByEmail($email);
    }

    public function findById(int $id): ?UserEntity
    {
        return $this->userRepository->find($id);
    }

    public function activateUser(UserEntity $user): UserEntity
    {
        return $this->userRepository->update($user, ['is_active' => true]);
    }

    public function deactivateUser(UserEntity $user): UserEntity
    {
        return $this->userRepository->update($user, ['is_active' => false]);
    }

    public function getActiveUsers(): \Illuminate\Support\Collection
    {
        return $this->userRepository->getActiveUsers();
    }

    public function searchUsersByName(string $name): \Illuminate\Support\Collection
    {
        return $this->userRepository->searchByName($name);
    }
}
