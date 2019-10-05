<?php

namespace Tests\Feature;

use App\Entities\Accounts\Models\Account;
use App\Entities\Donations\Models\Donation;
use App\Entities\Groups\Models\Group;
use App\Http\Actions\SyncUserToDiscourse;
use Mockery;
use Tests\TestCase;

class PanelDonationsListTest extends TestCase
{
    private $adminAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminAccount = factory(Account::class)->create();
        $adminGroup = Group::create([
            'name' => 'Administrator',
            'is_admin' => true
        ]);

        $this->adminAccount->groups()->attach($adminGroup->group_id);
    }

    public function testDonationShownInList()
    {
        $donation = factory(Donation::class)->create();

        $this->actingAs($this->adminAccount)
            ->get(route('front.panel.donations.index'))
            ->assertSee($donation->donation_id);
    }
}