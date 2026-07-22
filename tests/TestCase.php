<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Sanctum only starts a session for requests it recognizes as coming
        // from the SPA frontend (matched against SANCTUM_STATEFUL_DOMAINS).
        // Every test request needs this Origin header to exercise that path.
        $this->withHeader('Origin', env('FRONTEND_URL', 'http://localhost:5173'));
    }
}
