<?php

namespace Tests;

use App\Entities\Environment;
use App\Http\Middleware\MfaGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Session;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Use SQLite for tests only in local development
//        if (Environment::isDev()) {
//            putenv('APP_ENV=testing');
//            putenv('DB_CONNECTION=sqlite');
//        }
    }

    protected function disableReauthMiddleware(): TestCase
    {
        Session::put('auth.password_confirmed_at', time());

        return $this;
    }

    protected function flagNeedsMfa(): TestCase
    {
        Session::put(MfaGate::NEEDS_MFA_KEY, 'true');

        return $this;
    }
}
