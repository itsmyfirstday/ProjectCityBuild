<?php

namespace Database\Factories;

use App\Entities\Bans\Models\GameBan;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameBanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GameBan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'banned_player_type' => 'minecraft_player',
            'banned_alias_at_time' => $this->faker->name(),
            'staff_player_type' => 'minecraft_player',
            'reason' => $this->faker->sentence,
            'is_active' => $this->faker->boolean,
            'is_global_ban' => $this->faker->boolean,
            'expires_at' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-5 years', 'now'),
        ];
    }

    /**
     * Disables the ban
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    /**
     * Indicates that this ban has already expired
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'expires_at' => now()->subDay(),
            ];
        });
    }

    /**
     * Indicates that this ban will never expire
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function permanent()
    {
        return $this->state(function (array $attributes) {
            return [
                'expires_at' => null,
            ];
        });
    }
}
