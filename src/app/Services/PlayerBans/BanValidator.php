<?php

namespace App\Services\PlayerBans;

use App\Entities\Bans\Models\GameBan;
use App\Entities\ServerKeys\Models\ServerKey;
use App\Exceptions\Http\UnauthorisedException;
use App\Exceptions\Http\BadRequestException;
use Illuminate\Support\Facades\Log;

final class BanValidator
{
    /**
     * Returns whether the given ServerKey has permission
     * to create a global/local ban.
     *
     * @param boolean $isGlobalBan
     * @param ServerKey $serverKey
     *
     * @return boolean
     */
    public function isAllowedToBan(bool $isGlobalBan, ServerKey $serverKey)
    {
        if (!isset($serverKey)) {
            // this shouldn't be triggered unless the middleware has
            // failed to check for a server key already
            Log::critical('Attempted ban authentication but no ServerKey provided', ['ban' => $ban]);
            throw new BadRequestException('no_server_key', 'No server key provided');
        }
        
        return $isGlobalBan
            ? $serverKey->can_global_ban
            : $serverKey->can_local_ban;
    }

    /**
     * Returns whether the given ServerKey has permission
     * to unban the given GameBan
     *
     * @param GameBan $ban
     * @param ServerKey $serverKey
     *
     * @return boolean
     */
    public function isAllowedToUnban(GameBan $ban, ServerKey $serverKey)
    {
        if (!isset($serverKey)) {
            // this shouldn't be triggered unless the middleware has
            // failed to check for a server key already
            Log::critical('Attempted unban authentication but no ServerKey provided', ['ban' => $ban]);
            throw new UnauthorisedException('no_server_key', 'No server key provided during unban authentication');
        }

        if (!isset($ban)) {
            Log::critical('Attempted unban authentication but no GameBan provided', ['key' => $serverKey]);
            throw new BadRequestException('not_banned', 'No ban provided for unban authentication');
        }

        // if the ban is global, a key must have 'global ban' permission
        // to remove the ban
        if ($ban->is_global_ban) {
            return $serverKey->can_global_ban;
        }

        // if the ban is local, the ServerKey's server id must match the id
        // of the server the player was banned on, otherwise the key needs
        // 'global ban' permission to undo another server's local ban
        if ($ban->server_id === $serverKey->server_id) {
            return $serverKey->can_local_ban;
        }
        
        return $serverKey->can_global_ban;
    }
}