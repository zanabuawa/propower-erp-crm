<?php

namespace Tests\Feature;

use Tests\TestCase;

class EnvTest extends TestCase
{
    public function test_env(): void
    {
        dump('$_ENV[APP_ENV]: ' . ($_ENV['APP_ENV'] ?? 'not set'));
        dump('$_SERVER[APP_ENV]: ' . ($_SERVER['APP_ENV'] ?? 'not set'));
        dump('getenv(APP_ENV): ' . getenv('APP_ENV'));
        dump('config(app.env): ' . config('app.env'));
        dump('Running tests: ' . (app()->runningUnitTests() ? 'yes' : 'no'));
    }
}
