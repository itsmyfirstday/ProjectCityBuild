<?php

namespace App\Routes\Api\Controllers;

use App\Modules\Bans\Services\BanCreationService;
use App\Modules\Bans\Repositories\GameBanRepository;
use App\Modules\Bans\Services\BanAuthorisationService;
use App\Modules\Bans\Transformers\BanResource;
use App\Modules\Servers\Repositories\ServerRepository;
use App\Modules\Servers\Transformers\ServerResource;
use App\Modules\Users\Repositories\UserAliasRepository;
use App\Modules\Users\Services\GameUserLookupService;
use App\Modules\Users\Transformers\UserAliasResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory as Validator;
use Carbon\Carbon;
use Illuminate\Database\Connection;
use App\Shared\Exceptions\BadRequestException;
use App\Modules\Users\UserAliasTypeEnum;

class BanController extends Controller {
    
    /**
     * @var GameUserLookupService
     */
    private $gameUserLookup;

    /**
     * @var BanCreationService
     */
    private $banCreationService;

    /**
     * @var BanAuthorisationService
     */
    private $banAuthService;

    /**
     * @var Illuminate\Database\Connection
     */
    private $connection;


    public function __construct(
        GameUserLookupService $gameUserLookup,
        BanCreationService $banCreationService,
        BanAuthorisationService $banAuthService,
        Connection $connection
    ) {
        $this->gameUserLookup = $gameUserLookup;
        $this->banCreationService = $banCreationService;
        $this->banAuthService = $banAuthService;
        $this->connection = $connection;
    }

    /**
     * Creates a new player ban
     *
     * @param Request $request
     * @param Validator $validationFactory
     * @return void
     */
    public function storeBan(Request $request, Validator $validationFactory) {
        $aliasTypeWhitelist = implode(',', UserAliasTypeEnum::getKeys());

        $validator = $validationFactory->make($request->all(), [
            'player_id_type'    => 'required|in:'.$aliasTypeWhitelist,
            'player_id'         => 'required|max:60',
            'banner_id_type'    => 'required|in:'.$aliasTypeWhitelist,
            'banner_id'         => 'required|max:60',
            'reason'            => 'string',
            'expires_at'        => 'integer',
            'is_global_ban'     => 'boolean',
        ], [
            'in' => 'Invalid :attribute given',
        ]);

        if($validator->fails()) {
            throw new BadRequestException('bad_input', $validator->errors()->first());
        }

        $serverKey          = $request->get('key');
        $playerIdType       = $request->get('player_id_type');
        $playerId           = $request->get('player_id');
        $staffIdType        = $request->get('banner_id_type');
        $staffId            = $request->get('banner_id');
        $reason             = $request->get('reason');
        $expiryTimestamp    = $request->get('expires_at');
        $isGlobalBan        = $request->get('is_global_ban', false);

        $playerIdType       = UserAliasTypeEnum::toValue($playerIdType);
        $staffIdType        = UserAliasTypeEnum::toValue($staffIdType);

        // !!!
        // TODO: add ban server key authentication
        // !!!
        // $this->banAuthService->validateBan($isGlobalBan, $serverKey);

        // TODO: determine banned alias id (or create one)
        $bannedAliasId      = '';
        $aliasAtBan         = $request->get('player_alias');

        $playerGameUser = $this->gameUserLookup->getOrCreateGameUser($playerIdType, $playerId);
        $staffGameUser  = $this->gameUserLookup->getOrCreateGameUser($staffIdType, $staffId);

        $this->connection->beginTransaction();
        try {
            $ban = $this->banCreationService->storeBan(
                $serverKey->server_id,
                $playerGameUser->game_user_id,
                $staffGameUser->game_user_id,
                $bannedAliasId,
                $aliasAtBan,
                $reason,
                $expiryTimestamp,
                $isGlobalBan
            );

            // !!!
            // TODO: add ban logging
            // !!!
            
            $serverKey->touch();

            $this->connection->commit();

            // !!!
            // TODO: refactor this to a shared JSON-API output formatter
            // !!!
            return response()->json([
                'status_code' => 200,
                'data' => [
                    'ban' => $ban,
                ],
            ]);

        } catch(\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Creates a new player unban
     *
     * @param Request $request
     * @param Validator $validationFactory
     * @return void
     */
    public function storeUnban(Request $request, Validator $validationFactory) {
        $validator = $validationFactory->make($request->all(), [
            'player_id_type'    => 'required',
            'player_id'         => 'required',
            'banner_id_type'    => 'required',
            'banner_id'         => 'required',
        ])->validate();

        $serverKey          = $request->get('key');
        $playerIdType       = $request->get('player_id_type');
        $playerId           = $request->get('player_id');
        $staffIdType        = $request->get('banner_id_type');
        $staffId            = $request->get('banner_id');
        
        $playerGameUserId = $this->gameUserLookup->getOrCreateGameUser($playerIdType, $playerId)->game_user_id;
        $staffGameUserId  = $this->gameUserLookup->getOrCreateGameUser($staffIdType, $staffId)->game_user_id;

        $this->connection->beginTransaction();
        try {
            $unban = $this->banCreationService->storeUnban($serverKey, $playerGameUserId, $staffGameUserId);

            // !!!
            // TODO: add unban logging
            // !!!

            $serverKey->touch();

            $this->connection->commit();

            // !!!
            // TODO: refactor this to a shared JSON-API output formatter
            // !!!
            return response()->json([
                'status_code' => 200,
                'data' => [
                    'unban' => $unban,
                ],
            ]);

        } catch(\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Checks whether a player is currently banned on the server key's server
     *
     * @param Request $request
     * @param Validator $validationFactory
     * @return void
     */
    public function checkUserStatus(Request $request, Validator $validationFactory) {
        $validator = $validationFactory->make($request->all(), [
            'player_id_type'    => 'required',
            'player_id'         => 'required',
        ])->validate();

        $serverKey      = $request->get('key');
        $playerIdType   = $request->get('player_id_type');
        $playerId       = $request->get('player_id');

        $playerGameUserId = $this->gameUserLookup->getOrCreateGameUser($playerIdType, $playerId)->game_user_id;
        $activeBan = $this->banService->getActivePlayerBan($serverKey, $playerGameUserId);

        return response()->json([
            'status_code' => 200,
            'data' => [
                'is_banned' => isset($activeBan),
                'ban'       => $activeBan,
            ],
        ]);
    }

    /**
     * Gets the ban history of a player
     *
     * @param Request $request
     * @param Validator $validationFactory
     * @return void
     */
    public function getUserBanHistory(Request $request, Validator $validationFactory) {
        
    }


    public function getBanList(Request $request, GameBanRepository $banRepository, ServerRepository $serverRepository, UserAliasRepository $aliasRepository) {
        $page   = $request->input('page', 1);
        $take   = $request->input('take', 50);
        $offset = $request->input('offset', ($page - 1) * $take);

        $sort = [
            'field' => $request->input('sort_field', 'created_at'),
            'order' => $request->input('sort_direction', 'DESC'),
        ];

        $filter = [];
        if($playerAliasFilter = $request->input('player_alias_at_ban')) {
            $filter['player_alias_at_ban'] = $playerAliasFilter;
        }
        if($bannedAliasFilter = $request->input('banned_alias')) {
            $filter['banned_alias'] = $bannedAliasFilter;
        }
        

        $bans = $banRepository->getBans($take, $offset, $sort, $filter);
        $banCount = $banRepository->getBanCount();

        // normalize servers and users
        $serverIds = $bans->pluck('server_id')->unique()->toArray();
        $servers = $serverRepository->getServersByIds($serverIds);

        $bannedAliasIds = $bans->pluck('banned_alias_id')->unique()->toArray();
        $aliases = $aliasRepository->getAliasesByIds($bannedAliasIds);

        
        return response()->json([
            'status_code' => 200,
            'data' => BanResource::collection($bans),
            'relations' => [
                'servers' => ServerResource::collection($servers->keyBy('server_id')),
                'aliases' => UserAliasResource::collection($aliases->keyBy('user_alias_id')),
            ],
            'meta' => [
                'count' => $banCount,
                'start' => $offset,
                'end'   => min($offset + $take, $banCount),
            ],
        ]);
    }
}
