<?php

namespace TrueFans\LaravelReactable\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use TrueFans\LaravelReactable\Models\Reaction;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ReactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Reaction::class;
    public function definition(): array
    {
        return [
            //
        ];
    }
}
