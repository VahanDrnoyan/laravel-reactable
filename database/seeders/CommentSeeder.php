<?php

namespace TrueFans\LaravelReactable\Database\Seeders;

use Illuminate\Database\Seeder;
use TrueFans\LaravelReactable\Models\Comment;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example: Seed comments for posts
        // Assumes you have a Post model and User model available
        
        $userModel = config('auth.providers.users.model');
        
        // Get some users (adjust based on your setup)
        $users = $userModel::take(5)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        // Example: If you have a Post model with HasComments trait
        // Uncomment and adjust the following code:
        
        /*
        $posts = \App\Models\Post::take(10)->get();
        
        if ($posts->isEmpty()) {
            $this->command->warn('No posts found. Please seed posts first.');
            return;
        }

        foreach ($posts as $post) {
            // Add 3-8 random comments per post
            $commentCount = rand(3, 8);
            
            for ($i = 0; $i < $commentCount; $i++) {
                $randomUser = $users->random();
                
                Comment::factory()->create([
                    'user_id' => $randomUser->id,
                    'commentable_id' => $post->id,
                    'commentable_type' => get_class($post),
                ]);
            }
        }

        $this->command->info('Comments seeded successfully!');
        */
        
        $this->command->info('CommentSeeder ready. Uncomment and adjust code based on your models.');
    }
}
