# Laravel Reactable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/truefans/laravel-reactable.svg?style=flat-square)](https://packagist.org/packages/truefans/laravel-reactable)
[![Total Downloads](https://img.shields.io/packagist/dt/truefans/laravel-reactable.svg?style=flat-square)](https://packagist.org/packages/truefans/laravel-reactable)

A beautiful, Facebook-style reactions system for Laravel with Livewire. Add customizable emoji reactions (like, love, laugh, wow, sad, angry) to any model in your Laravel application with a single trait.

![Laravel Reactable Demo](https://via.placeholder.com/800x400?text=Laravel+Reactable+Demo)

## ✨ Features

- 🎭 **Facebook-Style UI** - Beautiful reaction picker with hover/click interactions
- 🔥 **Livewire Powered** - Real-time reactions without page refresh
- 📦 **Polymorphic Relations** - React to Posts, Comments, Images, or any model
- 🎨 **Fully Customizable** - Configure reaction types, icons, colors via config
- 👥 **User Reactions List** - See who reacted with filterable tabs by reaction type
- 🌙 **Dark Mode Support** - Beautiful UI in both light and dark themes
- ⚡ **Optimized Queries** - Efficient database queries with proper indexing
- 🔒 **Unique Reactions** - One reaction per user per item (can be changed)
- 📱 **Responsive Design** - Works perfectly on mobile and desktop

---

## 📋 Requirements

- PHP 8.4+
- Laravel 11.0+ or 12.0+
- Livewire 3.0+
- Alpine.js (included with Livewire)

---

## 🚀 Installation

### Step 1: Install via Composer

```bash
composer require truefans/laravel-reactable
```

### Step 2: Publish Assets

Publish the migration, config, and views:

```bash
php artisan vendor:publish --provider="TrueFans\LaravelReactable\LaravelReactableServiceProvider"
```

Or publish individually:

```bash
# Publish migration
php artisan vendor:publish --tag="laravel-reactable-migrations"

# Publish config
php artisan vendor:publish --tag="laravel-reactable-config"

# Publish views (optional - for customization)
php artisan vendor:publish --tag="laravel-reactable-views"
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

This creates the `reactions` table with:
- User ID
- Polymorphic relationship (reactable_id, reactable_type)
- Reaction type
- Unique constraint (one reaction per user per item)
- Optimized indexes

---

## 📖 Usage

### Add Reactions to Your Models

Add the `HasReactions` trait to any model you want to be reactable:

```php
use TrueFans\LaravelReactable\Traits\HasReactions;

class Post extends Model
{
    use HasReactions;
    
    // Your model code...
}
```

### Display the Reactions Component

In your Blade views:

```blade
<livewire:reactions :model="$post" />
```

That's it! The component will display:
- A "Like" button (or current reaction if user has reacted)
- Reaction picker on hover/click with all available reactions
- Reaction count summary with top 3 reaction icons
- Clickable reaction count that shows who reacted (with filterable tabs)

---

## 🎨 Configuration

### Customize Reaction Types

Edit `config/reactable.php`:

```php
return [
    'reaction_types' => [
        'like' => [
            'icon' => '👍',
            'label' => 'Like',
            'color' => 'blue',
        ],
        'love' => [
            'icon' => '❤️',
            'label' => 'Love',
            'color' => 'red',
        ],
        'laugh' => [
            'icon' => '😂',
            'label' => 'Laugh',
            'color' => 'yellow',
        ],
        'wow' => [
            'icon' => '😮',
            'label' => 'Wow',
            'color' => 'purple',
        ],
        'sad' => [
            'icon' => '😢',
            'label' => 'Sad',
            'color' => 'gray',
        ],
        'angry' => [
            'icon' => '😠',
            'label' => 'Angry',
            'color' => 'orange',
        ],
        // Add your own custom reactions!
        'fire' => [
            'icon' => '🔥',
            'label' => 'Fire',
            'color' => 'orange',
        ],
    ],

    'display' => [
        'show_breakdown' => true,  // Show detailed reaction breakdown
        'show_total' => true,      // Show total reaction count
        'show_tooltips' => true,   // Show tooltips on hover
    ],
];
```

---

## 🔧 API Reference

### Trait Methods

The `HasReactions` trait provides these methods:

```php
// Get all reactions relationship
$post->reactions();

// Add a reaction
$post->react('like', $user);  // User defaults to auth()->user()

// Remove a reaction
$post->unreact($user);

// Check if user has reacted
$post->hasReactedBy($user);  // Returns bool

// Get user's reaction type
$post->getReactionBy($user);  // Returns 'like', 'love', etc. or null

// Get reactions summary
$post->getReactionsSummary();  // Returns ['like' => 5, 'love' => 3, ...]

// Get total reactions count
$post->getTotalReactionsCount();  // Returns int

// Get count for specific reaction type
$post->getReactionsCountByType('like');  // Returns int
```

### Facade Methods

Use the `LaravelReactable` facade for helper methods:

```php
use TrueFans\LaravelReactable\Facades\LaravelReactable;

// Get all reaction types
LaravelReactable::getReactionTypes();

// Get specific reaction config
LaravelReactable::getReactionConfig('like');

// Validate reaction type
LaravelReactable::isValidReaction('like');  // Returns bool

// Get reaction type keys
LaravelReactable::getReactionTypeKeys();  // Returns ['like', 'love', ...]

// Get display settings
LaravelReactable::getDisplaySettings();
```

---

## 🎯 Advanced Usage

### Custom Styling

Publish the views and customize the Blade templates:

```bash
php artisan vendor:publish --tag="laravel-reactable-views"
```

Views will be published to `resources/views/vendor/reactable/`.

### Events

The component dispatches Livewire events:

```javascript
// Listen for reaction events
Livewire.on('reaction-added', (data) => {
    console.log('Reaction added:', data.type);
});

Livewire.on('reaction-removed', (data) => {
    console.log('Reaction removed:', data.type);
});
```

### Database Queries

Get posts with specific reactions:

```php
// Get posts with specific reaction
$lovedPosts = Post::whereHas('reactions', function($query) {
    $query->where('type', 'love');
})->get();

// Count reactions for a post
$reactionCount = $post->reactions()->count();

// Get all users who reacted to a post
$users = $post->reactions()->with('user')->get()->pluck('user');
```

---

## 🧪 Testing

Create test data with the included seeder:

```php
use TrueFans\LaravelReactable\Models\Reaction;

// Create reactions programmatically
Reaction::create([
    'user_id' => $user->id,
    'reactable_type' => Post::class,
    'reactable_id' => $post->id,
    'type' => 'like',
]);
```

---

## 🎨 UI Components

### Reaction Picker
- Appears on hover/click above the Like button
- Shows all available reaction emojis in a horizontal row
- Smooth animations and hover effects
- Tooltips showing reaction labels

### Reaction Count Summary
- Shows top 3 reaction icons as overlapping circles
- Displays total reaction count
- Clickable to open detailed reactions list

### Reactions List Dropdown
- Filterable tabs by reaction type (All, 👍, ❤️, etc.)
- Shows user avatars, names, and reaction times
- Scrollable list for many reactions
- Click outside to close

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## 📝 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

## 👨‍💻 Credits

- [Vahan Drnoyan](https://github.com/VahanDrnoyan)
- [All Contributors](../../contributors)

---

## 🆘 Support

If you discover any issues or have questions, please [open an issue](https://github.com/truefans/laravel-reactable/issues).

---

**Made with ❤️ by TrueFans**
