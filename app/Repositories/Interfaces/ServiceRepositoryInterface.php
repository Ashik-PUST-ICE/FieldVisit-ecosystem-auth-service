<?php

namespace App\Repositories\Interfaces;

interface ServiceRepositoryInterface
{
    public function generateToken(array $credentials): mixed;

    public function getServiceByName(string $name): mixed;
}
