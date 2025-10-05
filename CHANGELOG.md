# Changelog

All notable changes to `laravel-reactable` will be documented in this file.

## [1.0.0] - 2025-10-05

### 🎉 Initial Release

First stable release of Laravel Reactable - A beautiful, Facebook-style reactions system for Laravel with Livewire.

### ✨ Features

#### Core Functionality
- **Polymorphic Reactions System** - Add reactions to any Eloquent model with a single trait
- **Facebook-Style UI** - Beautiful reaction picker with hover/click interactions
- **Livewire Powered** - Real-time reactions without page refresh
- **Multiple Reaction Types** - Like, Love, Laugh, Wow, Sad, Angry (fully customizable)
- **User Reactions List** - See who reacted with filterable tabs by reaction type
- **Dark Mode Support** - Beautiful UI in both light and dark themes

#### Technical Features
- **HasReactions Trait** - Easy integration with any model
- **Optimized Queries** - Efficient database queries with proper indexing
- **Unique Reactions** - One reaction per user per item (configurable)
- **Query Scopes** - `withReactions()` and `popular()` scopes for easy querying
- **Event Dispatching** - `reaction-added` and `reaction-removed` events
- **Smart Positioning** - Intelligent dropdown placement with Alpine.js Anchor plugin
- **Responsive Design** - Works perfectly on mobile and desktop

#### Developer Experience
- **Comprehensive Tests** - 59 tests with 100% coverage
- **Full Documentation** - Complete API documentation and usage examples
- **CI/CD Ready** - GitHub Actions workflow included
- **Type Safety** - Full PHP 8.4 type hints
- **Laravel 11 & 12** - Support for latest Laravel versions

### 📦 Package Contents

#### Models & Traits
- `Reaction` model with polymorphic relationships
- `HasReactions` trait for reactable models
- Proper fillable attributes and casts

#### Livewire Components
- `Reactions` component with full UI functionality
- Reaction picker with hover interactions
- Reactions list with filtering
- Real-time updates

#### Database
- Migration for reactions table
- Proper indexes and foreign keys
- Unique constraint per user/reactable

#### Views
- Beautiful Blade templates with Tailwind CSS
- Dark mode support
- Responsive design
- Alpine.js interactions

#### Configuration
- Customizable reaction types
- Icon and label configuration
- Flexible settings

### 🧪 Testing

- **59 tests** covering all functionality
- **131 assertions** ensuring correctness
- **100% pass rate**
- Fast execution (~2.6 seconds)
- Comprehensive documentation

### 📚 Documentation

- Complete README with examples
- API documentation
- Testing guide
- Configuration guide
- Troubleshooting section

### 🔧 Requirements

- PHP 8.4+
- Laravel 11.0+ or 12.0+
- Livewire 3.0+
- Alpine.js (included with Livewire)

### 🚀 Installation

```bash
composer require truefans/laravel-reactable
php artisan vendor:publish --tag="reactable-migrations"
php artisan migrate
php artisan vendor:publish --tag="reactable-config"
```

### 📖 Usage

```php
use TrueFans\LaravelReactable\Traits\HasReactions;

class Post extends Model
{
    use HasReactions;
}

// Add reaction
$post->react('like', $user);

// Remove reaction
$post->unreact($user);

// Check if user reacted
$post->hasReactedBy($user);

// Get user's reaction
$post->getReactionBy($user);
```

### 🎯 What's Next

Future releases will include:
- Additional reaction types
- Reaction animations
- More customization options
- Performance optimizations
- Additional query helpers

---

For more information, see the [README](README.md) and [documentation](tests/README.md).
