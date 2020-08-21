<?php

namespace App\Entities\Accounts\Models;

use App\Entities\Donations\Models\Donation;
use App\Entities\Donations\Models\DonationPerk;
use App\Model;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;
use App\Entities\Groups\Models\Group;
use Laravel\Scout\Searchable;

final class Account extends Authenticatable
{
    use Notifiable, Searchable;

    protected $table = 'accounts';

    protected $primaryKey = 'account_id';

    protected $fillable = [
        'email',
        'username',
        'password',
        'remember_token',
        'last_login_ip',
        'last_login_at',
    ];

    protected $hidden = [

    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'last_login_at',
    ];

    public function toSearchableArray()
    {
        $array = [
            'account_id' => $this->getKey(),
            'email' => $this->email,
            'username' => $this->username
        ];

        return $array;
    }

    public function minecraftAccount()
    {
        return $this->hasMany('App\Entities\Players\Models\MinecraftPlayer', 'account_id', 'account_id');
    }

    public function linkedSocialAccounts()
    {
        return $this->hasMany('App\Entities\Accounts\Models\AccountLink', 'account_id', 'account_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'groups_accounts', 'account_id', 'group_id');
    }

    public function donations()
    {
        return $this->hasMany(Donation::class, 'account_id');
    }

    public function donationPerks()
    {
        return $this->hasMany(DonationPerk::class, 'account_id', 'account_id');
    }

    public function emailChangeRequests()
    {
        return $this->hasMany(AccountEmailChange::class, 'account_id');
    }

    public function customer()
    {
        return $this->hasOne(AccountCustomer::class, 'account_id', 'account_id');
    }

    public function gameBans()
    {
        // TODO: there's probably a way to optimise this just using the DB
        $bans = collect();

        foreach ($this->minecraftAccount as $minecraftAccount) {
            $bans = $bans->concat($minecraftAccount->gameBans);
        }

        return $bans;
    }

    public function isBanned()
    {
        // TODO: there's probably a way to optimise this just using the DB
        foreach ($this->minecraftAccount as $account) {
            if ($account->isBanned()) return true;
        }

        return false;
    }

    public function inGroup(Group $group)
    {
        return $this->groups->contains($group);
    }

    public function isAdmin()
    {
        return $this->groups()->where('is_admin', true)->count() > 0;
    }

    public function canAccessPanel()
    {
        return $this->groups()->where('can_access_panel', true)->count() > 0;
    }

    public function discourseGroupString()
    {
        $groups = $this->groups->pluck("discourse_name");
        return implode(",", array_filter($groups->toArray()));
    }

    /**
     * Gets an URL to the 'email change verification'
     * route with a signed signature to prevent
     * tampering
     *
     * @return string
     */
    public function getEmailChangeVerificationUrl(string $newEmail) : string
    {
        return URL::temporarySignedRoute('front.account.settings.email.confirm', now()->addMinutes(15), [
            'old_email' => $this->email,
            'new_email' => $newEmail,
        ]);
    }

    /**
     * Gets an URL to the activation route with a
     * signed signature to prevent tampering
     *
     * @return string
     */
    public function getActivationUrl() : string
    {
        return URL::temporarySignedRoute('front.register.activate', now()->addDay(), [
            'email' => $this->email,
        ]);
    }

    public function updateLastLogin($ip)
    {
        $this->last_login_ip = $ip;
        $this->last_login_at = Carbon::now();
        $this->save();
    }
}
