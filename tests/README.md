# Laravel Reactable - Test Suite

This directory contains comprehensive tests for the Laravel Reactable package.

## Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/pest tests/HasReactionsTraitTest.php

# Run with coverage
composer test-coverage

# Run tests in parallel
vendor/bin/pest --parallel
```

## Test Structure

### 1. HasReactionsTraitTest.php
Tests the `HasReactions` trait functionality that can be added to any Eloquent model.

**Coverage:**
- ✅ Reactions relationship (morphMany)
- ✅ Adding reactions (`react()` method)
- ✅ Removing reactions (`unreact()` method)
- ✅ Checking if user has reacted (`hasReactedBy()`)
- ✅ Getting user's reaction type (`getReactionBy()`)
- ✅ Replacing existing reactions
- ✅ Multiple users reacting to same model
- ✅ Reaction summaries and counts
- ✅ Query scopes (`withReactions`, `popular`)
- ✅ Authentication checks and edge cases

### 2. ReactionModelTest.php
Tests the `Reaction` model and its relationships.

**Coverage:**
- ✅ Model creation with fillable attributes
- ✅ User relationship (belongsTo)
- ✅ Reactable relationship (morphTo)
- ✅ Timestamps and casting
- ✅ Unique constraint (user + reactable)
- ✅ CRUD operations
- ✅ Multiple reaction types support

### 3. LivewireReactionsTest.php
Tests the Livewire `Reactions` component for real-time UI interactions.

**Coverage:**
- ✅ Component mounting and initialization
- ✅ Adding reactions (authenticated users)
- ✅ Removing reactions
- ✅ Changing reaction types
- ✅ Guest user restrictions
- ✅ Reaction picker UI (toggle, open, close)
- ✅ Reactions list UI (toggle, filter by type)
- ✅ Reaction counts display
- ✅ Event dispatching (reaction-added, reaction-removed)
- ✅ Locked properties (modelType, modelId)
- ✅ Eager loading support

### 4. Test Models
Helper models for testing located in `tests/Models/`:
- **User.php** - Simple user model for authentication
- **Post.php** - Example model using HasReactions trait

## Test Configuration

### TestCase.php
Base test case that sets up:
- SQLite in-memory database
- Application encryption key
- Test tables (users, posts, reactions)
- Livewire service provider
- Package service provider

### Pest.php
Pest configuration that applies TestCase to all tests.

## Writing New Tests

### Example: Testing a new method in HasReactions trait

```php
test('new method works correctly', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $post = Post::create([
        'user_id' => $user->id,
        'title' => 'Test Post',
        'content' => 'Test content',
        'published_at' => now(),
    ]);

    // Your test logic here
    $post->react('like', $user);
    
    expect($post->hasReactedBy($user))->toBeTrue();
});
```

### Example: Testing Livewire component

```php
test('component does something', function () {
    $user = User::create(['name' => 'Test', 'email' => 'test@example.com']);
    $post = Post::create([...]);
    
    $this->actingAs($user);

    Livewire::test(Reactions::class, ['model' => $post])
        ->call('someMethod')
        ->assertSet('someProperty', 'expectedValue')
        ->assertDispatched('some-event');
});
```

## Test Data

### Reaction Types Configuration
Tests use the following reaction types (configured in `beforeEach`):

```php
[
    'like' => ['icon' => '👍', 'label' => 'Like'],
    'love' => ['icon' => '❤️', 'label' => 'Love'],
    'laugh' => ['icon' => '😂', 'label' => 'Laugh'],
    'wow' => ['icon' => '😮', 'label' => 'Wow'],
    'sad' => ['icon' => '😢', 'label' => 'Sad'],
    'angry' => ['icon' => '😠', 'label' => 'Angry'],
]
```

## Continuous Integration

These tests are designed to run in CI/CD pipelines. They use:
- SQLite in-memory database (no external dependencies)
- Isolated test environment
- Fast execution (~2-3 seconds)

## Troubleshooting

### Tests failing with "No encryption key"
Ensure `TestCase.php` sets the app key:
```php
config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
```

### Tests failing with "Class not found"
Run composer autoload:
```bash
composer dump-autoload
```

### Livewire tests failing
Ensure Livewire is installed as dev dependency:
```bash
composer require --dev livewire/livewire
```

## Coverage Report

Current test coverage:
- **59 tests**
- **131 assertions**
- **100% pass rate**

Key areas covered:
- ✅ Trait functionality
- ✅ Model relationships
- ✅ Livewire components
- ✅ Authentication & authorization
- ✅ Edge cases & error handling
- ✅ UI interactions
- ✅ Event dispatching

## Best Practices

1. **Use `beforeEach` for setup** - Create test data in beforeEach hook
2. **Test one thing per test** - Keep tests focused and simple
3. **Use descriptive test names** - Make it clear what's being tested
4. **Test edge cases** - Include tests for null, empty, and invalid inputs
5. **Mock external dependencies** - Keep tests fast and isolated
6. **Use factories when available** - Create test data consistently

## Contributing

When adding new features:
1. Write tests first (TDD approach)
2. Ensure all tests pass
3. Maintain test coverage above 80%
4. Update this README if adding new test files
