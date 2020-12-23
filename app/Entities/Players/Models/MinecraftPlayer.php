<?php

namespace App\Entities\Players\Models;

use App\Entities\Accounts\Models\Account;
use App\Entities\Bans\BannableModelInterface;
use App\Entities\Bans\Models\GameBan;
use App\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class MinecraftPlayer extends Model implements BannableModelInterface
{
    use HasFactory;

    protected $table = 'players_minecraft';

    protected $primaryKey = 'player_minecraft_id';

    protected $fillable = [
        'uuid',
        'account_id',
        'playtime',
        'last_seen_at',
    ];

    protected $hidden = [];

    protected $dates = [
        'created_at',
        'updated_at',
        'last_seen_at',
    ];

    public function getBanIdentifier(): string
    {
        return $this->uuid;
    }

    public function getBanReadableName(): string
    {
        return $this->aliases->last()->alias;
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(MinecraftPlayerAlias::class, 'player_minecraft_id', 'player_minecraft_id');
    }

    public function gameBans()
    {
        return $this->morphMany(GameBan::class, 'banned_player');
    }

    public function isBanned()
    {
        return $this->gameBans()->where('is_active', true)->count() > 0;
    }

    /**
     * Update the last seen at time of the player to now
     *
     * @return bool
     */
    public function touchLastSeenAt(): bool
    {
        $this->last_seen_at = $this->freshTimestamp();

        return $this->save();
    }
}
