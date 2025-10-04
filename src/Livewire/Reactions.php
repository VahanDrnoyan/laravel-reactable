<?php

namespace TrueFans\LaravelReactable\Livewire;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class Reactions extends Component
{
    public Model $model;

    public string $modelType;

    public int $modelId;

    public array $reactions = [];

    public ?string $userReaction = null;

    public array $reactionTypes = [];

    public bool $showPicker = false;

    public bool $showReactionsList = false;

    public array $reactionUsers = [];

    public ?string $selectedReactionFilter = null;

    public function mount(Model $model): void
    {
        $this->model = $model;
        $this->modelType = get_class($model);
        $this->modelId = $model->id;
        $this->reactionTypes = config('reactable.reaction_types', []);

        $this->loadReactions();
    }

    public function togglePicker(): void
    {
        $this->showPicker = ! $this->showPicker;
        $this->showReactionsList = false;
    }

    public function closePicker(): void
    {
        $this->showPicker = false;
    }

    public function toggleReactionsList(): void
    {
        $this->showReactionsList = ! $this->showReactionsList;
        $this->showPicker = false;

        if ($this->showReactionsList) {
            $this->selectedReactionFilter = null; // Reset filter
            $this->loadReactionUsers();
        }
    }

    public function closeReactionsList(): void
    {
        $this->showReactionsList = false;
    }

    public function filterReactionsByType(?string $type): void
    {
        $this->selectedReactionFilter = $type;
        $this->loadReactionUsers();
    }

    public function loadReactionUsers(): void
    {
        $query = $this->model->reactions()->with('user')->latest();

        // Apply filter if selected
        if ($this->selectedReactionFilter) {
            $query->where('type', $this->selectedReactionFilter);
        }

        $this->reactionUsers = $query->get()
            ->map(fn ($reaction) => [
                'user_name' => $reaction->user->name,
                'type' => $reaction->type,
                'created_at' => $reaction->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    public function loadReactions(): void
    {
        // Initialize reaction counts
        $this->reactions = array_fill_keys(array_keys($this->reactionTypes), 0);

        // Load reactions from database
        $reactionCounts = $this->model->reactions()
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $this->reactions = array_merge($this->reactions, $reactionCounts);

        // Check if current user has reacted
        if (auth()->check()) {
            $userReaction = $this->model->reactions()
                ->where('user_id', auth()->id())
                ->first();

            $this->userReaction = $userReaction?->type;
        }
    }

    public function toggleReaction(): void
    {
        if (! auth()->check()) {
            $this->dispatch('show-login-modal');

            return;
        }

        // If user has already reacted, remove it
        if ($this->userReaction !== null) {
            $this->removeReaction();
        } else {
            // Default to 'like' reaction
            $this->react('like');
        }
    }

    public function react(string $type): void
    {
        if (! auth()->check()) {
            $this->dispatch('show-login-modal');

            return;
        }

        if (! array_key_exists($type, $this->reactionTypes)) {
            return;
        }

        // If user already reacted with the same type, remove it
        if ($this->userReaction === $type) {
            $this->removeReaction();

            return;
        }

        // Remove previous reaction if exists
        if ($this->userReaction !== null) {
            $this->removeReaction();
        }

        // Add new reaction
        $this->model->reactions()->create([
            'user_id' => auth()->id(),
            'type' => $type,
        ]);

        $this->userReaction = $type;
        $this->reactions[$type]++;
        $this->showPicker = false;

        $this->dispatch('reaction-added', [
            'modelType' => $this->modelType,
            'modelId' => $this->modelId,
            'type' => $type,
        ]);
    }

    public function removeReaction(): void
    {
        if (! auth()->check() || $this->userReaction === null) {
            return;
        }

        $this->model->reactions()
            ->where('user_id', auth()->id())
            ->delete();

        $this->reactions[$this->userReaction]--;
        $previousReaction = $this->userReaction;
        $this->userReaction = null;

        $this->dispatch('reaction-removed', [
            'modelType' => $this->modelType,
            'modelId' => $this->modelId,
            'type' => $previousReaction,
        ]);
    }

    public function getTotalReactionsProperty(): int
    {
        return array_sum($this->reactions);
    }

    public function render()
    {
        return view('reactable::livewire.reactions');
    }
}
