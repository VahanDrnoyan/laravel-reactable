<?php

use TrueFans\LaravelReactable\Models\Reaction;
use TrueFans\LaravelReactable\Tests\Models\Post;
use TrueFans\LaravelReactable\Tests\Models\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $this->post = Post::create([
        'user_id' => $this->user->id,
        'title' => 'Test Post',
        'content' => 'Test content',
        'published_at' => now(),
    ]);
});

test('reaction can be created with fillable attributes', function () {
    $reaction = Reaction::create([
        'user_id' => $this->user->id,
        'reactable_id' => $this->post->id,
        'reactable_type' => Post::class,
        'type' => 'like',
    ]);

    expect($reaction)->toBeInstanceOf(Reaction::class);
    expect($reaction->user_id)->toBe($this->user->id);
    expect($reaction->reactable_id)->toBe($this->post->id);
    expect($reaction->reactable_type)->toBe(Post::class);
    expect($reaction->type)->toBe('like');
});

test('reaction belongs to user', function () {
    $reaction = Reaction::create([
        'user_id' => $this->user->id,
        'reactable_id' => $this->post->id,
        'reactable_type' => Post::class,
        'type' => 'like',
    ]);

    expect($reaction->user)->not->toBeNull();
    expect($reaction->user->id)->toBe($this->user->id);
    expect($reaction->user->name)->toBe($this->user->name);
});

test('reaction belongs to reactable model', function () {
    $reaction = Reaction::create([
        'user_id' => $this->user->id,
        'reactable_id' => $this->post->id,
        'reactable_type' => Post::class,
        'type' => 'like',
    ]);

    expect($reaction->reactable)->toBeInstanceOf(Post::class);
    expect($reaction->reactable->id)->toBe($this->post->id);
});

test('reaction has timestamps', function () {
    $reaction = Reaction::create([
        'user_id' => $this->user->id,
        'reactable_id' => $this->post->id,
        'reactable_type' => Post::class,
        'type' => 'like',
    ]);

    expect($reaction->created_at)->not->toBeNull();
    expect($reaction->updated_at)->not->toBeNull();
    expect($reaction->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('reaction timestamps are cast to datetime', function () {
    $reaction = Reaction::create([
        'user_id' => $this->user->id,
        'reactable_id' => $this->post->id,
        'reactable_type' => Post::class,
        'type' => 'like',
    ]);

    $casts = $reaction->getCasts();

    expect($casts)->toHaveKey('created_at');
    expect($casts)->toHaveKey('updated_at');
    expect($casts['created_at'])->toBe('datetime');
    expect($casts['updated_at'])->toBe('datetime');
});

test('reaction type can be any string', function () {
    $types = ['like', 'love', 'laugh', 'wow', 'sad', 'angry', 'custom'];

    foreach ($types as $type) {
        $reaction = Reaction::create([
            'user_id' => $this->user->id,
            'reactable_id' => $this->post->id,
            'reactable_type' => Post::class,
            'type' => $type,
        ]);

        expect($reaction->type)->toBe($type);
        $reaction->delete();
    }
});

test('multiple reactions can exist for different users on same model', function () {
    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    Reaction::create([
        'user_id' => $this->user->id,
        'reactable_id' => $this->post->id,
        'reactable_type' => Post::class,
        'type' => 'like',
    ]);

    Reaction::create([
        'user_id' => $user2->id,
        'reactable_id' => $this->post->id,
        'reactable_type' => Post::class,
        'type' => 'love',
    ]);

    expect(Reaction::count())->toBe(2);
});

test('reaction enforces unique constraint per user per reactable', function () {
    Reaction::create([
        'user_id' => $this->user->id,
        'reactable_id' => $this->post->id,
        'reactable_type' => Post::class,
        'type' => 'like',
    ]);

    // Attempting to create duplicate should throw exception
    expect(fn () => Reaction::create([
        'user_id' => $this->user->id,
        'reactable_id' => $this->post->id,
        'reactable_type' => Post::class,
        'type' => 'love',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('reaction can be deleted', function () {
    $reaction = Reaction::create([
        'user_id' => $this->user->id,
        'reactable_id' => $this->post->id,
        'reactable_type' => Post::class,
        'type' => 'like',
    ]);

    expect(Reaction::count())->toBe(1);

    $reaction->delete();

    expect(Reaction::count())->toBe(0);
});

test('reaction can be updated', function () {
    $reaction = Reaction::create([
        'user_id' => $this->user->id,
        'reactable_id' => $this->post->id,
        'reactable_type' => Post::class,
        'type' => 'like',
    ]);

    $reaction->update(['type' => 'love']);

    expect($reaction->fresh()->type)->toBe('love');
});

test('reaction fillable attributes are protected', function () {
    $reaction = new Reaction;
    $fillable = $reaction->getFillable();

    expect($fillable)->toContain('user_id');
    expect($fillable)->toContain('reactable_id');
    expect($fillable)->toContain('reactable_type');
    expect($fillable)->toContain('type');
});
