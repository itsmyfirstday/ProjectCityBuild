<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('bans')->group(function () {
    Route::post('/', 'GameBanController@store');
    Route::get('show', 'GameBanController@show');
});

Route::prefix('unbans')->group(function () {
    Route::post('/', 'GameUnbanController@store');
});

Route::prefix('groups')->group(function () {
    Route::get('/', 'GroupApiController@getAll');
});

Route::prefix('servers')->group(function () {
    Route::get('all', 'ServerController@getAllServers');
});

Route::post('discord/sync', 'DiscordSyncController@getRank');

Route::post('minecraft/authenticate', 'TempMinecraftController@authenticate');

Route::post('deploy', 'DeployController@deploy');