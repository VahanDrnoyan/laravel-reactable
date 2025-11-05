<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use TrueFans\LaravelReactable\Livewire\Reactions;
use TrueFans\LaravelReactable\Tests\Models\Post;
use TrueFans\LaravelReactable\Tests\Models\User;

beforeEach(function () {
    // Clear Laravel config cache for the test runtime
    Artisan::call('config:clear');

    // Optional: also clear other caches
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    config(['auth.providers.users.model' => \TrueFans\LaravelReactable\Tests\Models\User::class]);
    // Use the *real* public disk (not faked), so the file physically exists
    Storage::disk('public')->makeDirectory('test-avatars');

    // Create a fake image and store it permanently
    $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);
    $path = $file->store('test-avatars', 'public');

    // Create a user
    $this->user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    // Create a post
    $this->post = Post::create([
        'user_id' => $this->user->id,
        'title' => 'Test Post',
        'content' => 'Test content',
        'published_at' => now(),
    ]);
    $this->post->reactions()->create([
        'type' => 'love',
        'user_id' => $this->user->id,
    ]);

    // Create a profile with a *real image URL*
    $this->user->profile()->create([
        'image_url' => asset('storage/'.$path), // public URL to stored file
    ]);
});
test('confirm profile instantitated', function () {
    expect($this->user->profile)->not->toBeNull()
        ->and($this->user->profile->image_url)->toContain('storage/');
});
test('user avatar is displayed', function () {
    Livewire::test(Reactions::class, ['model' => $this->post])
        ->set('showReactionsList', true)
        ->call('filterReactionsByType', 'love')
        ->assertSee('img src=');
});
test('user model getAvatarUrl is called instead if config is null', function () {
    config(['reactable.avatar_field' => null]);
    Livewire::test(Reactions::class, ['model' => $this->post])
        ->set('showReactionsList', true)
        ->call('filterReactionsByType', 'love')
        ->assertSee('test_url');
});
