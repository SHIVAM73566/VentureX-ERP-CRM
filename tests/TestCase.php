<?php

namespace Tests;

use App\Http\Middleware\InstallerGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        InstallerGuard::markInstalled();
    }
}
