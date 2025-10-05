# Testing Guide - Laravel Reactable

Quick reference guide for testing the Laravel Reactable package.

## Quick Start

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Run specific test file
vendor/bin/pest tests/HasReactionsTraitTest.php

# Run tests with coverage
composer test-coverage

# Run tests in watch mode (auto-rerun on file changes)
vendor/bin/pest --watch
```

## Common Testing Patterns

### 1. Testing Trait Methods

```php
use TrueFans\LaravelReactable\Tests\Models\{User, Post};

test('user can react to a post', function () {
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $post = Post::create([
        'user_id' => $user->id,
        'title' => 'Test Post',
        'content' => 'Content here',
        'published_at' => now(),
    ]);

    // Test the react method
    $reaction = $post->react('like', $user);

    expect($reaction->type)->toBe('like');
    expect($post->hasReactedBy($user))->toBeTrue();
});
```

### 2. Testing Multiple Users

```php
test('multiple users can react differently', function () {
    $user1 = User::create(['name' => 'User 1', 'email' => 'user1@test.com']);
    $user2 = User::create(['name' => 'User 2', 'email' => 'user2@test.com']);
    $post = Post::create([...]);

    $post->react('like', $user1);
    $post->react('love', $user2);

    expect($post->getReactionBy($user1))->toBe('like');
    expect($post->getReactionBy($user2))->toBe('love');
    expect($post->getTotalReactionsCount())->toBe(2);
});
```

### 3. Testing Livewire Components

```php
use Livewire\Livewire;
use TrueFans\LaravelReactable\Livewire\Reactions;

test('authenticated user can add reaction via component', function () {
    $user = User::create([...]);
    $post = Post::create([...]);

    $this->actingAs($user);

    Livewire::test(Reactions::class, ['model' => $post])
        ->call('react', 'like')
        ->assertSet('userReaction', 'like')
        ->assertDispatched('reaction-added');

    expect($post->reactions()->count())->toBe(1);
});
```

### 4. Testing Guest Restrictions

```php
test('guest cannot add reactions', function () {
    $post = Post::create([...]);

    Livewire::test(Reactions::class, ['model' => $post])
        ->call('react', 'like')
        ->assertDispatched('show-login-modal');

    expect($post->reactions()->count())->toBe(0);
});
```

### 5. Testing Query Scopes

```php
test('popular scope orders by reactions count', function () {
    $post1 = Post::create(['title' => 'Post 1', ...]);
    $post2 = Post::create(['title' => 'Post 2', ...]);

    $user1 = User::create([...]);
    $user2 = User::create([...]);

    // Post 1 gets 1 reaction
    $post1->react('like', $user1);

    // Post 2 gets 2 reactions
    $post2->react('like', $user1);
    $post2->react('love', $user2);

    $posts = Post::popular()->get();

    expect($posts->first()->id)->toBe($post2->id);
    expect($posts->last()->id)->toBe($post1->id);
});
```

### 6. Testing Reaction Summaries

```php
test('get reactions summary returns correct counts', function () {
    $post = Post::create([...]);
    $user1 = User::create([...]);
    $user2 = User::create([...]);
    $user3 = User::create([...]);

    $post->react('like', $user1);
    $post->react('like', $user2);
    $post->react('love', $user3);

    $summary = $post->getReactionsSummary();

    expect($summary)->toBe([
        'like' => 2,
        'love' => 1,
    ]);
});
```

### 7. Testing Event Dispatching

```php
test('reaction added event is dispatched', function () {
    $user = User::create([...]);
    $post = Post::create([...]);

    $this->actingAs($user);

    Livewire::test(Reactions::class, ['model' => $post])
        ->call('react', 'love')
        ->assertDispatched('reaction-added', [
            'modelType' => Post::class,
            'modelId' => $post->id,
            'type' => 'love',
        ]);
});
```

### 8. Testing UI State

```php
test('reaction picker can be toggled', function () {
    $user = User::create([...]);
    $post = Post::create([...]);

    $this->actingAs($user);

    Livewire::test(Reactions::class, ['model' => $post])
        ->assertSet('showPicker', false)
        ->call('togglePicker')
        ->assertSet('showPicker', true)
        ->call('togglePicker')
        ->assertSet('showPicker', false);
});
```

### 9. Testing Reaction Changes

```php
test('user can change their reaction', function () {
    $user = User::create([...]);
    $post = Post::create([...]);

    // First reaction
    $post->react('like', $user);
    expect($post->getReactionBy($user))->toBe('like');

    // Change reaction
    $post->react('love', $user);
    expect($post->getReactionBy($user))->toBe('love');
    expect($post->reactions()->count())->toBe(1); // Only one reaction
});
```

### 10. Testing Error Cases

```php
test('react throws exception when user not authenticated', function () {
    $post = Post::create([...]);

    $post->react('like'); // No user provided
})->throws(\Exception::class, 'User must be authenticated to react.');
```

## Assertion Helpers

### Pest Expectations

```php
// Basic assertions
expect($value)->toBe($expected);
expect($value)->toBeTrue();
expect($value)->toBeFalse();
expect($value)->toBeNull();
expect($value)->not->toBeNull();

// Collection assertions
expect($collection)->toHaveCount(3);
expect($array)->toContain('value');

// Instance checks
expect($model)->toBeInstanceOf(Post::class);

// Numeric assertions
expect($count)->toBeGreaterThan(0);
expect($count)->toBeLessThan(10);
```

### Livewire Assertions

```php
Livewire::test(Component::class)
    ->assertSet('property', 'value')
    ->assertNotSet('property', 'wrong')
    ->assertDispatched('event-name')
    ->assertStatus(200)
    ->call('method', 'param')
    ->set('property', 'newValue');
```

## Test Organization

### File Structure
```
tests/
├── README.md                    # Test documentation
├── TESTING_GUIDE.md            # This file
├── Pest.php                    # Pest configuration
├── TestCase.php                # Base test case
├── Models/                     # Test models
│   ├── User.php
│   └── Post.php
├── HasReactionsTraitTest.php   # Trait tests
├── ReactionModelTest.php       # Model tests
└── LivewireReactionsTest.php   # Component tests
```

### Naming Conventions

- Test files: `*Test.php`
- Test methods: Use descriptive sentences
  ```php
  test('user can react to a post')
  test('guest cannot add reactions')
  test('reaction counts are displayed correctly')
  ```

## Debugging Tests

### Run Single Test

```bash
# By test name
vendor/bin/pest --filter="user can react"

# By file
vendor/bin/pest tests/HasReactionsTraitTest.php

# By file and filter
vendor/bin/pest tests/HasReactionsTraitTest.php --filter="multiple users"
```

### Verbose Output

```bash
vendor/bin/pest -v
vendor/bin/pest -vv
vendor/bin/pest -vvv
```

### Stop on Failure

```bash
vendor/bin/pest --stop-on-failure
```

### Debug with dd()

```php
test('debug test', function () {
    $post = Post::create([...]);
    
    dd($post->toArray()); // Dump and die
    
    expect($post)->not->toBeNull();
});
```

## Performance Tips

1. **Use in-memory SQLite** - Already configured in TestCase
2. **Minimize database queries** - Use eager loading
3. **Run tests in parallel** - `vendor/bin/pest --parallel`
4. **Use transactions** - Tests automatically rollback

## Common Issues

### Issue: Tests are slow
**Solution:** Use `--parallel` flag or optimize database queries

### Issue: Random test failures
**Solution:** Check for test isolation issues, ensure proper cleanup in `beforeEach`

### Issue: Livewire component not found
**Solution:** Ensure Livewire is installed and service provider is registered in TestCase

### Issue: Database errors
**Solution:** Check migration is loaded in TestCase `getEnvironmentSetUp` method

## Best Practices

1. ✅ **Test behavior, not implementation**
2. ✅ **Keep tests simple and focused**
3. ✅ **Use descriptive test names**
4. ✅ **Test edge cases and error conditions**
5. ✅ **Mock external dependencies**
6. ✅ **Keep tests fast (< 3 seconds total)**
7. ✅ **Use factories for test data**
8. ✅ **Clean up after tests (automatic with transactions)**

## Resources

- [Pest Documentation](https://pestphp.com)
- [Livewire Testing](https://livewire.laravel.com/docs/testing)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Orchestra Testbench](https://packages.tools/testbench)
