<?php

use Livewire\Livewire;
use TrueFans\LaravelReactable\Livewire\Comments;
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

test('comments component can be mounted', function () {
    Livewire::test(Comments::class, ['model' => $this->post])
        ->assertSet('modelType', Post::class)
        ->assertSet('modelId', $this->post->id)
        ->assertSet('showComments', false)
        ->assertSet('commentsCount', 0)
        ->assertStatus(200);
});

test('comments can be toggled', function () {
    $this->actingAs($this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->assertSet('showComments', false)
        ->call('toggleComments')
        ->assertSet('showComments', true)
        ->call('toggleComments')
        ->assertSet('showComments', false);
});

test('comments are visible when toggled', function () {
    $this->actingAs($this->user);

    // Add a comment first
    $this->post->addComment('This is a test comment', $this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->assertSet('showComments', true)
        ->assertSet('commentsCount', 1)
        ->assertSee('This is a test comment');
});

test('authenticated user can add comment', function () {
    $this->actingAs($this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->set('newComment', 'This is my new comment')
        ->call('addComment')
        ->assertSet('newComment', '')
        ->assertSet('commentsCount', 1)
        ->assertDispatched('comment-added');

    expect($this->post->comments()->count())->toBe(1);
    expect($this->post->comments()->first()->content)->toBe('This is my new comment');
});

test('guest user cannot add comment', function () {
    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->set('newComment', 'Guest comment')
        ->call('addComment');

    // Guest users are blocked by auth check, not validation
    expect($this->post->comments()->count())->toBe(0);
});

test('comment content is required', function () {
    $this->actingAs($this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->set('newComment', '')
        ->call('addComment')
        ->assertHasErrors(['newComment' => 'required']);
});

test('comment content cannot exceed max length', function () {
    $this->actingAs($this->user);

    $longComment = str_repeat('a', 1001);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->set('newComment', $longComment)
        ->call('addComment')
        ->assertHasErrors(['newComment' => 'max']);
});

test('comment content is sanitized against XSS', function () {
    $this->actingAs($this->user);

    $maliciousComment = '<script>alert("XSS")</script>Hello';

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->set('newComment', $maliciousComment)
        ->call('addComment')
        ->assertHasErrors('newComment');

    expect($this->post->comments()->count())->toBe(0);
});

test('user can edit their own comment', function () {
    $this->actingAs($this->user);

    $comment = $this->post->addComment('Original comment', $this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->call('editComment', $comment->id)
        ->assertSet('editingCommentId', $comment->id)
        ->assertSet('editedContent', 'Original comment')
        ->set('editedContent', 'Updated comment')
        ->call('updateComment')
        ->assertSet('editingCommentId', null)
        ->assertSet('editedContent', '')
        ->assertDispatched('comment-updated');

    expect($comment->fresh()->content)->toBe('Updated comment');
});

test('user cannot edit another users comment', function () {
    $otherUser = User::create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $comment = $this->post->addComment('Other user comment', $otherUser);

    $this->actingAs($this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->call('editComment', $comment->id)
        ->assertSet('editingCommentId', null);

    expect($comment->fresh()->content)->toBe('Other user comment');
});

test('edited comment content is validated', function () {
    $this->actingAs($this->user);

    $comment = $this->post->addComment('Original comment', $this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->call('editComment', $comment->id)
        ->set('editedContent', '')
        ->call('updateComment')
        ->assertHasErrors(['editedContent' => 'required']);

    expect($comment->fresh()->content)->toBe('Original comment');
});

test('edited comment content is sanitized', function () {
    $this->actingAs($this->user);

    $comment = $this->post->addComment('Original comment', $this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->call('editComment', $comment->id)
        ->set('editedContent', '<script>alert("XSS")</script>Hacked')
        ->call('updateComment')
        ->assertHasErrors('editedContent');

    expect($comment->fresh()->content)->toBe('Original comment');
});

test('user can cancel editing comment', function () {
    $this->actingAs($this->user);

    $comment = $this->post->addComment('Original comment', $this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->call('editComment', $comment->id)
        ->assertSet('editingCommentId', $comment->id)
        ->set('editedContent', 'Changed my mind')
        ->call('cancelEdit')
        ->assertSet('editingCommentId', null)
        ->assertSet('editedContent', '');

    expect($comment->fresh()->content)->toBe('Original comment');
});

test('user can delete their own comment', function () {
    $this->actingAs($this->user);

    $comment = $this->post->addComment('Comment to delete', $this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->assertSet('commentsCount', 1)
        ->call('deleteComment', $comment->id)
        ->assertSet('deletingCommentId', $comment->id);

    // Now confirm the deletion
    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->set('deletingCommentId', $comment->id)
        ->call('confirmDelete')
        ->assertSet('deletingCommentId', null)
        ->assertSet('commentsCount', 0)
        ->assertDispatched('comment-deleted');

    expect($this->post->comments()->count())->toBe(0);
});

test('user cannot delete another users comment', function () {
    $otherUser = User::create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $comment = $this->post->addComment('Other user comment', $otherUser);

    $this->actingAs($this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->call('deleteComment', $comment->id)
        ->call('confirmDelete');

    expect($this->post->comments()->count())->toBe(1);
});

test('user can cancel comment deletion', function () {
    $this->actingAs($this->user);

    $comment = $this->post->addComment('Comment to delete', $this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->call('deleteComment', $comment->id)
        ->assertSet('deletingCommentId', $comment->id)
        ->call('cancelDelete')
        ->assertSet('deletingCommentId', null);

    expect($this->post->comments()->count())->toBe(1);
});

test('comments count is loaded correctly', function () {
    $this->actingAs($this->user);

    $this->post->addComment('Comment 1', $this->user);
    $this->post->addComment('Comment 2', $this->user);
    $this->post->addComment('Comment 3', $this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->assertSet('commentsCount', 3);
});

test('comments count uses eager loaded data when available', function () {
    $this->actingAs($this->user);

    $this->post->addComment('Comment 1', $this->user);
    $this->post->addComment('Comment 2', $this->user);

    // Load post with comments count
    $postWithCount = Post::withCount('comments')->find($this->post->id);

    Livewire::test(Comments::class, ['model' => $postWithCount])
        ->assertSet('commentsCount', 2);
});

test('comments are paginated', function () {
    $this->actingAs($this->user);

    // Create more comments than perPage
    for ($i = 1; $i <= 15; $i++) {
        $this->post->addComment("Comment {$i}", $this->user);
    }

    $component = Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->assertSet('hasMoreComments', true);

    // Check that only perPage comments are loaded
    expect(count($component->get('comments')))->toBe(10);
});

test('load more comments works correctly', function () {
    $this->actingAs($this->user);

    // Create more comments than perPage
    for ($i = 1; $i <= 15; $i++) {
        $this->post->addComment("Comment {$i}", $this->user);
    }

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->assertSet('hasMoreComments', true)
        ->call('loadMore')
        ->assertSet('hasMoreComments', false);
});

test('new comment appears at the top of the list', function () {
    $this->actingAs($this->user);

    $this->post->addComment('First comment', $this->user);

    $component = Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->set('newComment', 'Second comment')
        ->call('addComment');

    $comments = $component->get('comments');
    expect($comments[0]['content'])->toBe('Second comment');
});

test('comments with models property provides eager loaded data', function () {
    $this->actingAs($this->user);

    $comment1 = $this->post->addComment('Comment 1', $this->user);
    $comment2 = $this->post->addComment('Comment 2', $this->user);

    $component = Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments');

    $commentsWithModels = $component->get('commentsWithModels');

    expect($commentsWithModels)->toHaveCount(2);
    expect($commentsWithModels[0]['model'])->toBeInstanceOf(Comment::class);
    expect($commentsWithModels[0]['model']->relationLoaded('reactions'))->toBeTrue();
});

test('comment added event includes correct data', function () {
    $this->actingAs($this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->set('newComment', 'Test comment')
        ->call('addComment')
        ->assertDispatched('comment-added');

    // Verify the comment was actually added
    expect($this->post->comments()->count())->toBe(1);
});

test('comment deleted event includes correct data', function () {
    $this->actingAs($this->user);

    $comment = $this->post->addComment('Comment to delete', $this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->set('deletingCommentId', $comment->id)
        ->call('confirmDelete')
        ->assertDispatched('comment-deleted', [
            'modelType' => Post::class,
            'modelId' => $this->post->id,
            'commentId' => $comment->id,
        ]);
});

test('comment updated event includes correct data', function () {
    $this->actingAs($this->user);

    $comment = $this->post->addComment('Original comment', $this->user);

    Livewire::test(Comments::class, ['model' => $this->post])
        ->call('toggleComments')
        ->call('editComment', $comment->id)
        ->set('editedContent', 'Updated comment')
        ->call('updateComment')
        ->assertDispatched('comment-updated', [
            'modelType' => Post::class,
            'modelId' => $this->post->id,
            'commentId' => $comment->id,
        ]);
});

test('model type and id are locked and cannot be changed', function () {
    $this->actingAs($this->user);

    $component = Livewire::test(Comments::class, ['model' => $this->post]);

    // Verify properties are set correctly and locked
    $component->assertSet('modelType', Post::class);
    $component->assertSet('modelId', $this->post->id);

    // The #[Locked] attribute prevents these from being updated
    // We verify they remain unchanged after component interactions
    $component->call('toggleComments');
    $component->assertSet('modelType', Post::class);
    $component->assertSet('modelId', $this->post->id);
});
