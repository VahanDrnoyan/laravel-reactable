<?php

namespace TrueFans\LaravelReactable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use TrueFans\LaravelReactable\Database\Factories\CommentFactory;
use TrueFans\LaravelReactable\Traits\HasReactions;

class Comment extends Model
{
    use HasFactory;
    use HasReactions;

    protected static function newFactory()
    {
        return CommentFactory::new();
    }

    protected $fillable = [
        'user_id',
        'commentable_id',
        'commentable_type',
        'content',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Set the content attribute with sanitization.
     * Strips HTML tags and trims whitespace for security.
     */
    public function setContentAttribute(string $value): void
    {
        // Strip all HTML tags and trim whitespace
        $this->attributes['content'] = strip_tags(trim($value));
    }

    /**
     * Get the user who made the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Get the parent commentable model (Post, Media, Article, etc.).
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
