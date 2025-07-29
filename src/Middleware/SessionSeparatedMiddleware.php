<?php

namespace App\Middleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Create new database for each session
 * @see SessionSeparatedConnectionDriver
 */
class SessionSeparatedMiddleware implements Middleware
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function wrap(Driver $driver): Driver
    {
        return new SessionSeparatedConnectionDriver($this->requestStack, $driver);
    }
}