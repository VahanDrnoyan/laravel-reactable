<?php

use TrueFans\LaravelReactable\Models\Comment;
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

test('model can have comments relationship', function () {
    expect($this->post->comments())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class);
});

test('user can add comment to a model', function () {
    $comment = $this->post->addComment('This is a great post!', $this->user);

    expect($comment)->toBeInstanceOf(Comment::class);
    expect($comment->content)->toBe('This is a great post!');
    expect($comment->user_id)->toBe($this->user->id);
    expect($comment->commentable_id)->toBe($this->post->id);
    expect($comment->commentable_type)->toBe(Post::class);
});

test('user can remove their own comment', function () {
    $comment = $this->post->addComment('My comment', $this->user);

    $result = $this->post->removeComment($comment->id, $this->user);

    expect($result)->toBeTrue();
    expect($this->post->comments()->count())->toBe(0);
});

test('user cannot remove another users comment', function () {
    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    
    $comment = $this->post->addComment('User 2 comment', $user2);

    $result = $this->post->removeComment($comment->id, $this->user);

    expect($result)->toBeFalse();
    expect($this->post->comments()->count())->toBe(1);
});

test('removeComment returns false when comment does not exist', function () {
    $result = $this->post->removeComment(999, $this->user);

    expect($result)->toBeFalse();
});

test('can check if user has commented on model', function () {
    expect($this->post->hasCommentedBy($this->user))->toBeFalse();

    $this->post->addComment('Test comment', $this->user);

    expect($this->post->hasCommentedBy($this->user))->toBeTrue();
});

test('can get comments count', function () {
    expect($this->post->getCommentsCount())->toBe(0);

    $this->post->addComment('Comment 1', $this->user);
    $this->post->addComment('Comment 2', $this->user);

    expect($this->post->getCommentsCount())->toBe(2);
});

test('multiple users can comment on same model', function () {
    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    $user3 = User::create(['name' => 'Bob Smith', 'email' => 'bob@example.com']);

    $this->post->addComment('Comment from user 1', $this->user);
    $this->post->addComment('Comment from user 2', $user2);
    $this->post->addComment('Comment from user 3', $user3);

    expect($this->post->comments()->count())->toBe(3);
});

test('withComments scope adds comments count', function () {
    $this->post->addComment('Test comment', $this->user);

    $post = Post::withComments()->first();

    expect($post->comments_count)->toBe(1);
});

test('popularByComments scope orders by comments count descending', function () {
    $post2 = Post::create([
        'user_id' => $this->user->id,
        'title' => 'Post 2',
        'content' => 'Content 2',
        'published_at' => now(),
    ]);

    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    // Post 1 gets 1 comment
    $this->post->addComment('Comment 1', $this->user);

    // Post 2 gets 3 comments
    $post2->addComment('Comment 1', $this->user);
    $post2->addComment('Comment 2', $user2);
    $post2->addComment('Comment 3', $this->user);

    $posts = Post::popularByComments()->get();

    expect($posts->first()->id)->toBe($post2->id);
    expect($posts->last()->id)->toBe($this->post->id);
});

test('popularByComments scope can order ascending', function () {
    $post2 = Post::create([
        'user_id' => $this->user->id,
        'title' => 'Post 2',
        'content' => 'Content 2',
        'published_at' => now(),
    ]);

    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    $this->post->addComment('Comment 1', $this->user);
    $post2->addComment('Comment 1', $this->user);
    $post2->addComment('Comment 2', $user2);

    $posts = Post::popularByComments('asc')->get();

    expect($posts->first()->id)->toBe($this->post->id);
    expect($posts->last()->id)->toBe($post2->id);
});

test('addComment throws exception when user is not provided and not authenticated', function () {
    $this->post->addComment('Test comment');
})->throws(\Exception::class, 'User must be authenticated to comment.');

test('removeComment returns false when user is not provided and not authenticated', function () {
    $result = $this->post->removeComment(1);

    expect($result)->toBeFalse();
});

test('hasCommentedBy returns false when user is not provided and not authenticated', function () {
    $result = $this->post->hasCommentedBy();

    expect($result)->toBeFalse();
});

test('comment is polymorphic and can be used on different models', function () {
    $this->post->addComment('Test comment', $this->user);

    $comment = Comment::first();

    expect($comment->commentable)->toBeInstanceOf(Post::class);
    expect($comment->commentable->id)->toBe($this->post->id);
});

test('comment belongs to user', function () {
    $comment = $this->post->addComment('Test comment', $this->user);

    expect($comment->user)->not->toBeNull();
    expect($comment->user->id)->toBe($this->user->id);
    expect($comment->user->email)->toBe($this->user->email);
});

test('deleting user cascades and removes their comments', function () {
    $this->post->addComment('Test comment', $this->user);

    expect(Comment::count())->toBe(1);

    $userId = $this->user->id;
    $this->user->delete();

    // Verify user is deleted
    expect(User::find($userId))->toBeNull();
    
    // In SQLite with proper foreign key constraints, comments should cascade
    // Note: This test verifies the relationship is set up correctly
    expect(Comment::count())->toBe(0);
});

test('user can add multiple comments to same model', function () {
    $this->post->addComment('First comment', $this->user);
    $this->post->addComment('Second comment', $this->user);
    $this->post->addComment('Third comment', $this->user);

    expect($this->post->comments()->count())->toBe(3);
    expect($this->post->hasCommentedBy($this->user))->toBeTrue();
});
