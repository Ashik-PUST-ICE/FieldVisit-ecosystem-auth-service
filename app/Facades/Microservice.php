<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array call(string $serviceName, string $url, string $cacheKey, ?callable $tokenCallback = null, int $cacheTtl = 600, int $circuitTtl = 30)
 * @method static array callFromConfig(string $serviceName, string $endpoint)
 */
class Microservice extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'microservice.client';
    }
}
