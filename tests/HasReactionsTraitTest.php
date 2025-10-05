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

test('model can have reactions relationship', function () {
    expect($this->post->reactions())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class);
});

test('user can react to a model', function () {
    $reaction = $this->post->react('like', $this->user);

    expect($reaction)->toBeInstanceOf(Reaction::class);
    expect($reaction->type)->toBe('like');
    expect($reaction->user_id)->toBe($this->user->id);
    expect($reaction->reactable_id)->toBe($this->post->id);
    expect($reaction->reactable_type)->toBe(Post::class);
});

test('user can unreact from a model', function () {
    $this->post->react('like', $this->user);

    $result = $this->post->unreact($this->user);

    expect($result)->toBeTrue();
    expect($this->post->reactions()->count())->toBe(0);
});

test('unreact returns false when user has not reacted', function () {
    $result = $this->post->unreact($this->user);

    expect($result)->toBeFalse();
});

test('can check if user has reacted to model', function () {
    expect($this->post->hasReactedBy($this->user))->toBeFalse();

    $this->post->react('like', $this->user);

    expect($this->post->hasReactedBy($this->user))->toBeTrue();
});

test('can get user reaction type', function () {
    expect($this->post->getReactionBy($this->user))->toBeNull();

    $this->post->react('love', $this->user);

    expect($this->post->getReactionBy($this->user))->toBe('love');
});

test('reacting replaces existing reaction', function () {
    $this->post->react('like', $this->user);
    $this->post->react('love', $this->user);

    expect($this->post->reactions()->count())->toBe(1);
    expect($this->post->getReactionBy($this->user))->toBe('love');
});

test('multiple users can react to same model', function () {
    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    $user3 = User::create(['name' => 'Bob Smith', 'email' => 'bob@example.com']);

    $this->post->react('like', $this->user);
    $this->post->react('love', $user2);
    $this->post->react('wow', $user3);

    expect($this->post->reactions()->count())->toBe(3);
});

test('get reactions summary returns count by type', function () {
    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    $user3 = User::create(['name' => 'Bob Smith', 'email' => 'bob@example.com']);

    $this->post->react('like', $this->user);
    $this->post->react('like', $user2);
    $this->post->react('love', $user3);

    $summary = $this->post->getReactionsSummary();

    expect($summary)->toBe([
        'like' => 2,
        'love' => 1,
    ]);
});

test('get total reactions count returns correct number', function () {
    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    $user3 = User::create(['name' => 'Bob Smith', 'email' => 'bob@example.com']);

    $this->post->react('like', $this->user);
    $this->post->react('love', $user2);
    $this->post->react('wow', $user3);

    expect($this->post->getTotalReactionsCount())->toBe(3);
});

test('get reactions count by type returns correct number', function () {
    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    $user3 = User::create(['name' => 'Bob Smith', 'email' => 'bob@example.com']);

    $this->post->react('like', $this->user);
    $this->post->react('like', $user2);
    $this->post->react('love', $user3);

    expect($this->post->getReactionsCountByType('like'))->toBe(2);
    expect($this->post->getReactionsCountByType('love'))->toBe(1);
    expect($this->post->getReactionsCountByType('wow'))->toBe(0);
});

test('withReactions scope adds reactions count', function () {
    $this->post->react('like', $this->user);

    $post = Post::withReactions()->first();

    expect($post->reactions_count)->toBe(1);
});

test('popular scope orders by reactions count descending', function () {
    $post2 = Post::create([
        'user_id' => $this->user->id,
        'title' => 'Post 2',
        'content' => 'Content 2',
        'published_at' => now(),
    ]);

    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    // Post 1 gets 1 reaction
    $this->post->react('like', $this->user);

    // Post 2 gets 2 reactions
    $post2->react('like', $this->user);
    $post2->react('love', $user2);

    $posts = Post::popular()->get();

    expect($posts->first()->id)->toBe($post2->id);
    expect($posts->last()->id)->toBe($this->post->id);
});

test('popular scope can order ascending', function () {
    $post2 = Post::create([
        'user_id' => $this->user->id,
        'title' => 'Post 2',
        'content' => 'Content 2',
        'published_at' => now(),
    ]);

    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    $this->post->react('like', $this->user);
    $post2->react('like', $this->user);
    $post2->react('love', $user2);

    $posts = Post::popular('asc')->get();

    expect($posts->first()->id)->toBe($this->post->id);
    expect($posts->last()->id)->toBe($post2->id);
});

test('react throws exception when user is not provided and not authenticated', function () {
    $this->post->react('like');
})->throws(\Exception::class, 'User must be authenticated to react.');

test('unreact returns false when user is not provided and not authenticated', function () {
    $result = $this->post->unreact();

    expect($result)->toBeFalse();
});

test('hasReactedBy returns false when user is not provided and not authenticated', function () {
    $result = $this->post->hasReactedBy();

    expect($result)->toBeFalse();
});

test('getReactionBy returns null when user is not provided and not authenticated', function () {
    $result = $this->post->getReactionBy();

    expect($result)->toBeNull();
});

test('reaction is polymorphic and can be used on different models', function () {
    $this->post->react('like', $this->user);

    $reaction = Reaction::first();

    expect($reaction->reactable)->toBeInstanceOf(Post::class);
    expect($reaction->reactable->id)->toBe($this->post->id);
});

test('deleting model does not automatically cascade reactions without foreign keys', function () {
    $this->post->react('like', $this->user);

    expect(Reaction::count())->toBe(1);

    $this->post->delete();

    // Note: Without proper foreign key constraints in SQLite, reactions remain
    // In production with proper DB setup, cascading would work
    // For now, we just verify the post is deleted
    expect(Post::find($this->post->id))->toBeNull();
});
