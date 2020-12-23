<?php

namespace Tests;

use App\Console\Commands\DeactivateDonatorPerksCommand;
use App\Entities\Accounts\Models\Account;
use App\Entities\Donations\Models\DonationPerk;
use App\Entities\Groups\Models\Group;
use App\Http\Actions\SyncUserToDiscourse;
use App\Entities\Donations\Notifications\DonationEndedNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class DeactivateDonatorPerksCommand_Test extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Group
     */
    private $donatorGroup;

    /**
     * @var Group
     */
    private $memberGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->donatorGroup = Group::factory()->create(['name' => 'donator']);
        $this->memberGroup = Group::factory()->isDefault()->create(['name' => 'member']);
    }

    private function makeCommand(): DeactivateDonatorPerksCommand
    {
        $mockSyncAction = $this->getMockBuilder(SyncUserToDiscourse::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new DeactivateDonatorPerksCommand($mockSyncAction);
    }

    public function testDeactivatesExpiredPerk()
    {
        Notification::fake();

        $account = Account::factory()->create();

        $expectedPerk = DonationPerk::factory()->create([
            'is_active' => true,
            'is_lifetime_perks' => false,
            'expires_at' => now()->subDay(),
            'account_id' => $account->getKey()
        ]);

        $command = $this->makeCommand();
        $command->handle();

        $perk = DonationPerk::find($expectedPerk->getKey());

        $this->assertFalse($perk->is_active);

        Notification::assertSentTo($account, DonationEndedNotification::class);
    }

    public function testDoesNotDeactivateUnexpiredPerk()
    {
        $expectedPerk = DonationPerk::factory()->create([
            'is_active' => true,
            'is_lifetime_perks' => false,
            'expires_at' => now()->addDay(),
            'account_id' => Account::factory()->create()->getKey(),
        ]);

        $command = $this->makeCommand();
        $command->handle();

        $perk = DonationPerk::find($expectedPerk->getKey());

        $this->assertTrue($perk->is_active);
    }

    public function testDoesNotDeactivateLifetimePerk()
    {
        $expectedPerk = DonationPerk::factory()->create([
            'is_active' => true,
            'is_lifetime_perks' => true,
            'expires_at' => null,
            'account_id' => Account::factory()->create()->getKey(),
        ]);

        $command = $this->makeCommand();
        $command->handle();

        $perk = DonationPerk::find($expectedPerk->getKey());

        $this->assertTrue($perk->is_active);
    }

    public function testRemovesDonatorGroupWhenExpired()
    {
        $expectedAccount = Account::factory()->create();
        $expectedAccount->groups()->attach($this->donatorGroup->getKey());

        $expectedPerk = DonationPerk::factory()->create([
            'account_id' => $expectedAccount->getKey(),
            'is_active' => true,
            'is_lifetime_perks' => false,
            'expires_at' => now()->subDay(),
        ]);

        $this->assertTrue($expectedPerk->account->groups->contains($this->donatorGroup->getKey()));

        $command = $this->makeCommand();
        $command->handle();

        $account = Account::find($expectedAccount->getKey());
        $this->assertFalse($account->groups->contains($this->donatorGroup->getKey()));
    }

    public function testDoesNotRemoveDonatorGroupIfMultiplePerks()
    {
        $expectedAccount = Account::factory()->create();
        $expectedAccount->groups()->attach($this->donatorGroup->getKey());

        DonationPerk::factory()->create([
            'account_id' => $expectedAccount->getKey(),
            'is_active' => true,
            'is_lifetime_perks' => false,
            'expires_at' => now()->subDay(),
        ]);

        DonationPerk::factory()->create([
            'account_id' => $expectedAccount->getKey(),
            'is_active' => true,
            'is_lifetime_perks' => false,
            'expires_at' => now()->addDay(),
        ]);

        $expectedAccount = Account::find($expectedAccount->getKey());
        $this->assertTrue($expectedAccount->groups->contains($this->donatorGroup->getKey()));

        $command = $this->makeCommand();
        $command->handle();

        $account = Account::find($expectedAccount->getKey());
        $this->assertTrue($account->groups->contains($this->donatorGroup->getKey()));
    }

    public function testDoesNotRemoveDonatorGroupIfOtherLifetimePerk()
    {
        $expectedAccount = Account::factory()->create();
        $expectedAccount->groups()->attach($this->donatorGroup->getKey());

        DonationPerk::factory()->create([
            'account_id' => $expectedAccount->getKey(),
            'is_active' => true,
            'is_lifetime_perks' => false,
            'expires_at' => now()->subDay(),
        ]);

        DonationPerk::factory()->create([
            'account_id' => $expectedAccount->getKey(),
            'is_active' => true,
            'is_lifetime_perks' => true,
            'expires_at' => null,
        ]);

        $expectedAccount = Account::find($expectedAccount->getKey());
        $this->assertTrue($expectedAccount->groups->contains($this->donatorGroup->getKey()));

        $command = $this->makeCommand();
        $command->handle();

        $account = Account::find($expectedAccount->getKey());
        $this->assertTrue($account->groups->contains($this->donatorGroup->getKey()));
    }

    public function testAssignsExpiredDonatorToMemberGroupIfNoGroup()
    {
        $account = Account::factory()->create();
        $account->groups()->attach($this->donatorGroup->getKey());

        DonationPerk::factory()->create([
            'account_id' => $account->getKey(),
            'is_active' => true,
            'is_lifetime_perks' => false,
            'expires_at' => now()->subDay(),
        ]);

        $account = Account::find($account->getKey());
        $this->assertTrue($account->groups->contains($this->donatorGroup->getKey()));

        $command = $this->makeCommand();
        $command->handle();

        $account = Account::find($account->getKey());
        $this->assertTrue($account->groups->contains($this->memberGroup->getKey()));
        $this->assertEquals(1, count($account->groups));
    }
}
