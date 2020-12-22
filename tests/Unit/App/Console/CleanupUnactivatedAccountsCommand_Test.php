<?php

namespace Tests;

use App\Entities\Accounts\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class CleanupUnactivatedAccountsCommand_Test extends TestCase
{
    use RefreshDatabase;

    public function testDeletesUnactivatedAccount()
    {
        $elapsedDaysToDelete = 14;

        $accountToBeDeleted = factory(Account::class)->states('unactivated')->create([
            'updated_at' => now()->subDays($elapsedDaysToDelete + 1),
        ]);
        $accountJustCreated = factory(Account::class)->states('unactivated')->create([
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('accounts', [
            'account_id' => $accountToBeDeleted->getKey(),
        ]);
        $this->assertDatabaseHas('accounts', [
            'account_id' => $accountJustCreated->getKey(),
        ]);

        $this->artisan('cleanup:unactivated-accounts --days='.$elapsedDaysToDelete)
            ->assertExitCode(0);

        $this->assertDatabaseMissing('accounts', [
            'account_id' => $accountToBeDeleted->getKey(),
        ]);
        $this->assertDatabaseHas('accounts', [
            'account_id' => $accountJustCreated->getKey(),
        ]);
    }
}
