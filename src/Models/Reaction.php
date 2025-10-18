<?php

namespace TrueFans\LaravelReactable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use TrueFans\LaravelReactable\Database\Factories\ReactionFactory;
class Reaction extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ReactionFactory::new();
    }
    protected $fillable = [
        'user_id',
        'reactable_id',
        'reactable_type',
        'type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who made the reaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Get the parent reactable model (Post, Comment, Image, etc.).
     */
    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }
}
