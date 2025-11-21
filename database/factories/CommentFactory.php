<?php

namespace TrueFans\LaravelReactable\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use TrueFans\LaravelReactable\Models\Comment;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\TrueFans\LaravelReactable\Models\Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => $this->faker->paragraph(),
        ];
    }

    /**
     * Create a short comment.
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->sentence(),
        ]);
    }

    /**
     * Create a long comment.
     */
    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->paragraphs(3, true),
        ]);
    }
}
