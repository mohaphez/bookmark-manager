<?php

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserServiceInterface
{
    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find a user by ID
     */
    public function findById(int $id): ?User;

    /**
     * Get all users
     */
    public function getAllUsers(): Collection;

    /**
     * Authenticate a user with email and password
     */
    public function authenticate(string $email, string $password): ?User;

    /**
     * Create a new authentication token for a user
     */
    public function createToken(User $user, string $tokenName = 'auth_token'): string;

    /**
     * Revoke all tokens for a user
     */
    public function revokeTokens(User $user): void;
}
