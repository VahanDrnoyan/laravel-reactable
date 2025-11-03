<?php

namespace TrueFans\LaravelReactable\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use TrueFans\LaravelReactable\Models\Reaction;

trait HasReactions
{
    /**
     * Get all reactions for the model.
     */
    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    /**
     * Add a reaction to the model.
     */
    public function react(string $type, $user = null): Reaction
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            throw new \Exception('User must be authenticated to react.');
        }

        // Remove existing reaction if any
        $this->unreact($user);

        // Create new reaction
        return $this->reactions()->create([
            'user_id' => $user->id,
            'type' => $type,
        ]);
    }

    /**
     * Remove a reaction from the model.
     */
    public function unreact($user = null): bool
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return false;
        }

        return $this->reactions()
            ->where('user_id', $user->id)
            ->delete() > 0;
    }

    /**
     * Check if the user has reacted to the model.
     */
    public function hasReactedBy($user = null): bool
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return false;
        }

        return $this->reactions()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Get the user's reaction type.
     */
    public function getReactionBy($user = null): ?string
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return null;
        }

        $reaction = $this->reactions()
            ->where('user_id', $user->id)
            ->first();

        return $reaction?->type;
    }

    /**
     * Get reactions summary (count by type).
     */
    public function getReactionsSummary(): array
    {
        return $this->reactions()
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    /**
     * Get total reactions count.
     */
    public function getTotalReactionsCount(): int
    {
        return $this->reactions()->count();
    }

    /**
     * Get reactions count for a specific type.
     */
    public function getReactionsCountByType(string $type): int
    {
        return $this->reactions()
            ->where('type', $type)
            ->count();
    }

    /**
     * Scope to get models with reactions.
     */
    public function scopeWithReactions($query)
    {
        return $query->withCount('reactions');
    }

    /**
     * Scope to get models ordered by reactions count.
     */
    public function scopePopular($query, string $direction = 'desc')
    {
        return $query->withCount('reactions')
            ->orderBy('reactions_count', $direction);
    }

    public function canReact(string $type): bool
    {
        //        if($type === 'love') {
        //            return false;
        //        }
        // Override this in your model if you want to restrict reactions
        return true;
    }
}
