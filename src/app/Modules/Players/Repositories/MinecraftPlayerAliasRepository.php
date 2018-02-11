<?php
namespace App\Modules\Players\Repositories;

use App\Modules\Players\Models\MinecraftPlayerAlias;
use App\Shared\Repository;
use Carbon\Carbon;

class MinecraftPlayerAliasRepository extends Repository {

    protected $model = MinecraftPlayerAlias::class;

    /**
     * Creates a new MinecraftPlayerAlias tied to a
     * MinecraftPlayer id
     *
     * @param int $userId
     * @return GameUser
     */
    public function store(
        string $minecraftPlayerId,
        string $alias,
        Carbon $registeredAt = null
    ) : MinecraftPlayerAlias {

        return $this->getModel()->create([
            'player_minecraft_id'   => $minecraftPlayerId,
            'alias'                 => $alias,
            'registered_at'         => $registeredAt,
        ]);
    }

    public function getByAlias(string $alias) : ?MinecraftPlayerAlias {
        return $this->getModel()
            ->where('alias', $alias)
            ->first();
    }

}