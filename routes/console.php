<?php

use App\Services\PasswordReset\PasswordResetCleanupService;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('cleanup:password-resets', function (): void {
    $cleanupService = resolve(PasswordResetCleanupService::class);
    $cleanupService->cleanup();
})->describe('Delete old password reset requests');
