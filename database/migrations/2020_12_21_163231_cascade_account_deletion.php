<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CascadeAccountDeletion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $isForeignKeysSupported = env('DB_CONNECTION') === 'sqlite';

        Schema::table('groups_accounts', function (Blueprint $table) use($isForeignKeysSupported) {
            if ($isForeignKeysSupported) {
                $table->dropForeign('groups_accounts_account_id_foreign');
            }
            $table->foreign('account_id')->references('account_id')->on('accounts')->onDelete('cascade');
        });
        Schema::table('account_email_changes', function (Blueprint $table) use($isForeignKeysSupported) {
            if ($isForeignKeysSupported) {
                $table->dropForeign('account_email_changes_account_id_foreign');
            }
            $table->foreign('account_id')->references('account_id')->on('accounts')->onDelete('cascade');
        });
        Schema::table('account_payments', function (Blueprint $table) use($isForeignKeysSupported) {
            if ($isForeignKeysSupported) {
                $table->dropForeign('account_payments_account_id_foreign');
            }
            $table->foreign('account_id')->references('account_id')->on('accounts')->onDelete('cascade');
        });
        Schema::table('donations', function (Blueprint $table) use($isForeignKeysSupported) {
            if ($isForeignKeysSupported) {
                $table->dropForeign('donations_account_id_foreign');
            }
            $table->foreign('account_id')->references('account_id')->on('accounts')->onDelete('cascade');
        });
        Schema::table('donation_perks', function (Blueprint $table) use($isForeignKeysSupported) {
            if ($isForeignKeysSupported) {
                $table->dropForeign('donation_perks_account_id_foreign');
            }
            $table->foreign('account_id')->references('account_id')->on('accounts')->onDelete('cascade');
        });
        Schema::table('players_minecraft', function (Blueprint $table) use($isForeignKeysSupported) {
            if ($isForeignKeysSupported) {
                $table->dropForeign('players_minecraft_account_id_foreign');
            }
            $table->foreign('account_id')->references('account_id')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $isForeignKeysSupported = env('DB_CONNECTION') === 'sqlite';

        Schema::table('players_minecraft', function (Blueprint $table) use($isForeignKeysSupported) {
            if ($isForeignKeysSupported) {
                $table->dropForeign('players_minecraft_account_id_foreign');
            }
            $table->foreign('account_id')->references('account_id')->on('accounts');
        });
        Schema::table('donation_perks', function (Blueprint $table) use($isForeignKeysSupported) {
            if ($isForeignKeysSupported) {
                $table->dropForeign('donation_perks_account_id_foreign');
            }
            $table->foreign('account_id')->references('account_id')->on('accounts');
        });
        Schema::table('donations', function (Blueprint $table) use($isForeignKeysSupported) {
            if ($isForeignKeysSupported) {
                $table->dropForeign('donations_account_id_foreign');
            }
            $table->foreign('account_id')->references('account_id')->on('accounts');
        });
        Schema::table('account_payments', function (Blueprint $table) use($isForeignKeysSupported) {
            if ($isForeignKeysSupported) {
                $table->dropForeign('account_payments_account_id_foreign');
            }
            $table->foreign('account_id')->references('account_id')->on('accounts');
        });
        Schema::table('account_email_changes', function (Blueprint $table) use($isForeignKeysSupported) {
            if ($isForeignKeysSupported) {
                $table->dropForeign('account_email_changes_account_id_foreign');
            }
            $table->foreign('account_id')->references('account_id')->on('accounts');
        });
        Schema::table('groups_accounts', function (Blueprint $table) use($isForeignKeysSupported) {
            if ($isForeignKeysSupported) {
                $table->dropForeign('groups_accounts_account_id_foreign');
            }
            $table->foreign('account_id')->references('account_id')->on('accounts');
        });
    }
}
