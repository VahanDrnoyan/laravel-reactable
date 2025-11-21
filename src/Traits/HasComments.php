<?php

namespace TrueFans\LaravelReactable\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use TrueFans\LaravelReactable\Models\Comment;

trait HasComments
{
    /**
     * Get all comments for the model.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Add a comment to the model.
     */
    public function addComment(string $content, $user = null): Comment
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            throw new \Exception('User must be authenticated to comment.');
        }

        return $this->comments()->create([
            'user_id' => $user->id,
            'content' => $content,
        ]);
    }

    /**
     * Remove a comment from the model.
     */
    public function removeComment(int $commentId, $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return false;
        }

        // Only allow users to delete their own comments
        return $this->comments()
            ->where('id', $commentId)
            ->where('user_id', $user->id)
            ->delete() > 0;
    }

    /**
     * Check if the user has commented on the model.
     */
    public function hasCommentedBy($user = null): bool
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return false;
        }

        return $this->comments()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Get total comments count.
     */
    public function getCommentsCount(): int
    {
        return $this->comments()->count();
    }

    /**
     * Scope to get models with comments.
     */
    public function scopeWithComments($query)
    {
        return $query->withCount('comments');
    }

    /**
     * Scope to get models ordered by comments count.
     */
    public function scopePopularByComments($query, string $direction = 'desc')
    {
        return $query->withCount('comments')
            ->orderBy('comments_count', $direction);
    }
}
