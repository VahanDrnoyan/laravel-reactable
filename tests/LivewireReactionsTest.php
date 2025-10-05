<?php

use Livewire\Livewire;
use TrueFans\LaravelReactable\Livewire\Reactions;
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

    config()->set('reactable.reaction_types', [
        'like' => ['icon' => '👍', 'label' => 'Like'],
        'love' => ['icon' => '❤️', 'label' => 'Love'],
        'laugh' => ['icon' => '😂', 'label' => 'Laugh'],
        'wow' => ['icon' => '😮', 'label' => 'Wow'],
        'sad' => ['icon' => '😢', 'label' => 'Sad'],
        'angry' => ['icon' => '😠', 'label' => 'Angry'],
    ]);
});

test('reactions component can be mounted', function () {
    Livewire::test(Reactions::class, ['model' => $this->post])
        ->assertSet('modelType', Post::class)
        ->assertSet('modelId', $this->post->id)
        ->assertSet('showPicker', false)
        ->assertSet('showReactionsList', false)
        ->assertStatus(200);
});

test('reactions component loads reaction types from config', function () {
    Livewire::test(Reactions::class, ['model' => $this->post])
        ->assertSet('reactionTypes', [
            'like' => ['icon' => '👍', 'label' => 'Like'],
            'love' => ['icon' => '❤️', 'label' => 'Love'],
            'laugh' => ['icon' => '😂', 'label' => 'Laugh'],
            'wow' => ['icon' => '😮', 'label' => 'Wow'],
            'sad' => ['icon' => '😢', 'label' => 'Sad'],
            'angry' => ['icon' => '😠', 'label' => 'Angry'],
        ]);
});

test('authenticated user can add reaction', function () {
    $this->actingAs($this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('react', 'like')
        ->assertSet('userReaction', 'like')
        ->assertDispatched('reaction-added');

    expect($this->post->reactions()->count())->toBe(1);
});

test('authenticated user can remove reaction', function () {
    $this->actingAs($this->user);
    $this->post->react('like', $this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('removeReaction')
        ->assertSet('userReaction', null)
        ->assertDispatched('reaction-removed');

    expect($this->post->reactions()->count())->toBe(0);
});

test('authenticated user can change reaction type', function () {
    $this->actingAs($this->user);
    $this->post->react('like', $this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('react', 'love')
        ->assertSet('userReaction', 'love');

    expect($this->post->reactions()->count())->toBe(1);
    expect($this->post->getReactionBy($this->user))->toBe('love');
});

test('guest user cannot add reactions', function () {
    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('react', 'like')
        ->assertDispatched('show-login-modal');

    expect($this->post->reactions()->count())->toBe(0);
});

test('reaction picker can be toggled', function () {
    $this->actingAs($this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->assertSet('showPicker', false)
        ->call('togglePicker')
        ->assertSet('showPicker', true)
        ->call('togglePicker')
        ->assertSet('showPicker', false);
});

test('reaction picker can be closed', function () {
    $this->actingAs($this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('togglePicker')
        ->assertSet('showPicker', true)
        ->call('closePicker')
        ->assertSet('showPicker', false);
});

test('reactions list can be toggled', function () {
    $this->actingAs($this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->assertSet('showReactionsList', false)
        ->call('toggleReactionsList')
        ->assertSet('showReactionsList', true)
        ->call('toggleReactionsList')
        ->assertSet('showReactionsList', false);
});

test('reactions list can be closed', function () {
    $this->actingAs($this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('toggleReactionsList')
        ->assertSet('showReactionsList', true)
        ->call('closeReactionsList')
        ->assertSet('showReactionsList', false);
});

test('reaction counts are displayed correctly', function () {
    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    $user3 = User::create(['name' => 'Bob Smith', 'email' => 'bob@example.com']);

    $this->post->react('like', $this->user);
    $this->post->react('like', $user2);
    $this->post->react('love', $user3);

    $this->actingAs($this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->assertSet('reactions.like', 2)
        ->assertSet('reactions.love', 1);
});

test('total reactions property calculates correctly', function () {
    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    $user3 = User::create(['name' => 'Bob Smith', 'email' => 'bob@example.com']);

    $this->post->react('like', $this->user);
    $this->post->react('love', $user2);
    $this->post->react('wow', $user3);

    $this->actingAs($this->user);

    $component = Livewire::test(Reactions::class, ['model' => $this->post]);

    expect($component->get('totalReactions'))->toBe(3);
});

test('clicking same reaction type removes it', function () {
    $this->actingAs($this->user);
    $this->post->react('like', $this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('react', 'like')
        ->assertSet('userReaction', null);

    expect($this->post->reactions()->count())->toBe(0);
});

test('toggle reaction adds like when no reaction exists', function () {
    $this->actingAs($this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('toggleReaction')
        ->assertSet('userReaction', 'like');

    expect($this->post->reactions()->count())->toBe(1);
});

test('toggle reaction removes existing reaction', function () {
    $this->actingAs($this->user);
    $this->post->react('like', $this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('toggleReaction')
        ->assertSet('userReaction', null);

    expect($this->post->reactions()->count())->toBe(0);
});

test('invalid reaction type is ignored', function () {
    $this->actingAs($this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('react', 'invalid_type')
        ->assertSet('userReaction', null);

    expect($this->post->reactions()->count())->toBe(0);
});

test('picker closes after adding reaction', function () {
    $this->actingAs($this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('togglePicker')
        ->assertSet('showPicker', true)
        ->call('react', 'like')
        ->assertSet('showPicker', false);
});

test('opening picker closes reactions list', function () {
    $this->actingAs($this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('toggleReactionsList')
        ->assertSet('showReactionsList', true)
        ->call('togglePicker')
        ->assertSet('showReactionsList', false)
        ->assertSet('showPicker', true);
});

test('opening reactions list closes picker', function () {
    $this->actingAs($this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('togglePicker')
        ->assertSet('showPicker', true)
        ->call('toggleReactionsList')
        ->assertSet('showPicker', false)
        ->assertSet('showReactionsList', true);
});

test('reaction users list loads correctly', function () {
    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    $user3 = User::create(['name' => 'Bob Smith', 'email' => 'bob@example.com']);

    $this->post->react('like', $this->user);
    $this->post->react('love', $user2);
    $this->post->react('wow', $user3);

    $this->actingAs($this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('loadReactionUsers')
        ->assertSet('reactionUsers', function ($users) {
            return count($users) === 3;
        });
});

test('reaction users can be filtered by type', function () {
    $user2 = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    $user3 = User::create(['name' => 'Bob Smith', 'email' => 'bob@example.com']);

    $this->post->react('like', $this->user);
    $this->post->react('like', $user2);
    $this->post->react('love', $user3);

    $this->actingAs($this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('filterReactionsByType', 'like')
        ->assertSet('selectedReactionFilter', 'like')
        ->assertSet('reactionUsers', function ($users) {
            return count($users) === 2;
        });
});

test('component loads reactions with eager loaded data', function () {
    $this->actingAs($this->user);
    $this->post->react('like', $this->user);

    // Load post with reactions
    $postWithReactions = Post::with('reactions')->find($this->post->id);

    Livewire::test(Reactions::class, ['model' => $postWithReactions])
        ->assertSet('reactions.like', 1)
        ->assertSet('userReaction', 'like');
});

test('component handles model without eager loaded reactions', function () {
    $this->actingAs($this->user);
    $this->post->react('like', $this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->assertSet('reactions.like', 1)
        ->assertSet('userReaction', 'like');
});

test('reaction added event includes correct data', function () {
    $this->actingAs($this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('react', 'love')
        ->assertDispatched('reaction-added', [
            'modelType' => Post::class,
            'modelId' => $this->post->id,
            'type' => 'love',
        ]);
});

test('reaction removed event includes correct data', function () {
    $this->actingAs($this->user);
    $this->post->react('like', $this->user);

    Livewire::test(Reactions::class, ['model' => $this->post])
        ->call('removeReaction')
        ->assertDispatched('reaction-removed', [
            'modelType' => Post::class,
            'modelId' => $this->post->id,
            'type' => 'like',
        ]);
});

test('model type and id are locked and cannot be changed', function () {
    $this->actingAs($this->user);

    $component = Livewire::test(Reactions::class, ['model' => $this->post]);

    // Verify properties are set correctly and locked
    $component->assertSet('modelType', Post::class);
    $component->assertSet('modelId', $this->post->id);

    // The #[Locked] attribute prevents these from being updated
    // We verify they remain unchanged after component interactions
    $component->call('react', 'like');
    $component->assertSet('modelType', Post::class);
    $component->assertSet('modelId', $this->post->id);
});
