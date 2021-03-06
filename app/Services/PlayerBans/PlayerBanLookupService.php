<?php

namespace App\Services\PlayerBans;

use App\Entities\Bans\Repositories\GameBanRepository;
use App\Entities\GamePlayerType;
use App\Services\PlayerLookup\PlayerLookupService;

final class PlayerBanLookupService
{
    /**
     * @var GameBanRepository
     */
    private $gameBanRepository;

    /**
     * @var PlayerLookupService
     */
    private $playerLookupService;

    public function __construct(
        GameBanRepository $gameBanRepository,
        PlayerLookupService $playerLookupService
    ) {
        $this->gameBanRepository = $gameBanRepository;
        $this->playerLookupService = $playerLookupService;
    }

    public function getStatus(GamePlayerType $playerType, string $identifier)
    {
        if ($playerType->valueOf() === GamePlayerType::Minecraft) {
            // Strip hyphens from Minecraft UUIDs
            $identifier = str_replace('-', '', $identifier);
        }

        $player = $this->playerLookupService->getOrCreatePlayer($playerType, $identifier);

        return $this->gameBanRepository->getActiveBanByGameUserId($player->getKey(), $playerType);
    }

    public function getHistory(GamePlayerType $playerType, string $identifier)
    {
    }
}
