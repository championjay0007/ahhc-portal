<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication()
    {
        // Force environment variables so the application boots with sqlite in-memory for tests
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        putenv('APP_DEBUG=1');
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_ENV['APP_DEBUG'] = '1';

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}
