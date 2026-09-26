<?php

namespace App\Repositories\Interfaces;

interface AuthRepositoryInterface
{
    public function login(array $credentials): mixed;

    public function register(array $attributes): mixed;

    public function logout(): mixed;
}
