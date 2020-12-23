<?php

namespace App\Console\Commands;

use App\Entities\Accounts\Models\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class CleanupUnactivatedAccountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected string $signature = 'cleanup:unactivated-accounts';

    /**
     * The console command description.
     */
    protected string $description = 'Deletes any accounts not activated within a threshold';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $deletionThreshold = config('auth.unactivated_cleanup_days');
        $thresholdDate = now()->subDays($deletionThreshold);

        Log::info('[Unactivated Accounts] Deleting unactivated accounts unedited since '.$thresholdDate);

        Account::where('activated', false)
            ->whereDate('updated_at', '<', $thresholdDate)
            ->delete();

        Log::info('[Unactivated Accounts] Done');
    }
}
