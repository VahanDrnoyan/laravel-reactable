<?php

namespace TrueFans\LaravelReactable\Livewire;

use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Component;
use TrueFans\LaravelReactable\Models\Comment;

class Comments extends Component
{
    #[Locked]
    public string $modelType;

    public $model;

    #[Locked]
    public int $modelId;

    public string $newComment = '';

    public array $comments = [];

    public int $commentsCount = 0;

    public bool $showComments = false;

    public int $perPage = 10;

    public bool $hasMoreComments = false;

    public ?int $editingCommentId = null;

    public string $editedContent = '';

    public ?int $deletingCommentId = null;

    public function mount(Model $model): void
    {
        $this->model = $model;
        $this->modelType = get_class($model);
        $this->modelId = $model->id;

        $this->loadCommentsCount();
    }

    protected function getModel(): Model
    {
        return $this->model;
    }

    public function loadCommentsCount(): void
    {
        $model = $this->getModel();

        // Use the eager-loaded count if available (from withCount('comments')), otherwise query
        // This prevents N+1 issues when multiple instances of this component are rendered
        $this->commentsCount = $model->comments_count ?? $model->comments()->count();
    }

    public function toggleComments(): void
    {
        $this->showComments = ! $this->showComments;

        if ($this->showComments && empty($this->comments)) {
            $this->loadComments();
        }
    }

    public function loadComments(): void
    {
        $model = $this->getModel();

        $commentsQuery = $model->comments()
            ->with(['user', 'reactions']) // Eager load reactions to prevent N+1
            ->latest()
            ->take($this->perPage);

        $allComments = $commentsQuery->get();

        // Store only necessary data to avoid Livewire serialization N+1 issues
        $this->comments = $allComments->map(fn ($comment) => [
            'id' => $comment->id,
            'content' => $comment->content,
            'user_name' => $comment->user->name,
            'user_id' => $comment->user_id,
            'created_at' => $comment->created_at->diffForHumans(),
            'can_delete' => auth()->check() && auth()->id() === $comment->user_id,
        ])->toArray();

        $this->hasMoreComments = $model->comments()->count() > $this->perPage;
    }

    public function loadMore(): void
    {
        $this->perPage += 10;
        $this->loadComments();
    }

    public function addComment(): void
    {
        if (! auth()->check()) {
            $this->dispatch('show-login-modal');

            return;
        }

        $this->validate([
            'newComment' => [
                'required',
                'string',
                'max:1000',
                'min:1',
                // Prevent script tags and other dangerous HTML
                function ($attribute, $value, $fail) {
                    $stripped = strip_tags($value);
                    if ($stripped !== $value) {
                        $fail('The comment contains invalid characters or HTML tags.');
                    }
                },
            ],
        ]);

        // Additional sanitization: strip tags and trim
        $sanitizedContent = strip_tags(trim($this->newComment));

        $model = $this->getModel();
        $comment = $model->addComment($sanitizedContent, auth()->user());

        // Add new comment to the beginning of the list
        array_unshift($this->comments, [
            'id' => $comment->id,
            'content' => $comment->content,
            'user_name' => auth()->user()->name,
            'user_id' => auth()->id(),
            'created_at' => 'just now',
            'can_delete' => true,
        ]);

        $this->commentsCount++;
        $this->newComment = '';
        $this->showComments = true;

        $this->dispatch('comment-added', [
            'modelType' => $this->modelType,
            'modelId' => $this->modelId,
        ]);
    }

    public function deleteComment(int $commentId): void
    {
        // Set the comment ID to show the confirmation modal
        $this->deletingCommentId = $commentId;
    }

    public function confirmDelete(): void
    {
        if (!auth()->check() || !$this->deletingCommentId) {
            return;
        }

        $model = $this->getModel();
        $deleted = $model->removeComment($this->deletingCommentId, auth()->user());

        if ($deleted) {
            $this->comments = array_filter(
                $this->comments,
                fn ($comment) => $comment['id'] !== $this->deletingCommentId
            );
            $this->comments = array_values($this->comments); // Re-index array
            $this->commentsCount--;

            $this->dispatch('comment-deleted', [
                'modelType' => $this->modelType,
                'modelId' => $this->modelId,
                'commentId' => $this->deletingCommentId,
            ]);
        }

        // Close the modal
        $this->deletingCommentId = null;
    }

    public function cancelDelete(): void
    {
        $this->deletingCommentId = null;
    }

    /**
     * Get comments with their models for rendering.
     * Uses eager loading to prevent N+1 queries.
     */
    public function getCommentsWithModelsProperty()
    {
        if (empty($this->comments)) {
            return [];
        }

        $commentIds = array_column($this->comments, 'id');

        // Eager load comments with reactions in a single query
        $commentModels = Comment::with(['reactions', 'user'])
            ->whereIn('id', $commentIds)
            ->get()
            ->keyBy('id');

        // Merge models with comment data
        return collect($this->comments)->map(function ($comment) use ($commentModels) {
            $comment['model'] = $commentModels->get($comment['id']);

            return $comment;
        })->toArray();
    }

    public function editComment(int $commentId): void
    {
        if (!auth()->check()) {
            return;
        }

        // Find the comment in the array
        $comment = collect($this->comments)->firstWhere('id', $commentId);

        // Check if user can edit (must be the comment author)
        if (!$comment || $comment['user_id'] !== auth()->id()) {
            return;
        }

        $this->editingCommentId = $commentId;
        $this->editedContent = $comment['content'];
    }

    public function updateComment(): void
    {
        if (!auth()->check() || !$this->editingCommentId) {
            return;
        }

        $this->validate([
            'editedContent' => [
                'required',
                'string',
                'max:1000',
                'min:1',
                // Prevent script tags and other dangerous HTML
                function ($attribute, $value, $fail) {
                    $stripped = strip_tags($value);
                    if ($stripped !== $value) {
                        $fail('The comment contains invalid characters or HTML tags.');
                    }
                },
            ],
        ]);

        // Additional sanitization
        $sanitizedContent = strip_tags(trim($this->editedContent));

        // Find and update the comment in database
        $comment = Comment::find($this->editingCommentId);

        if (!$comment || $comment->user_id !== auth()->id()) {
            return;
        }

        $comment->content = $sanitizedContent;
        $comment->save();

        // Update the comment in the local array
        $this->comments = collect($this->comments)->map(function ($c) use ($sanitizedContent) {
            if ($c['id'] === $this->editingCommentId) {
                $c['content'] = $sanitizedContent;
            }
            return $c;
        })->toArray();

        // Reset edit state
        $this->editingCommentId = null;
        $this->editedContent = '';

        $this->dispatch('comment-updated', [
            'modelType' => $this->modelType,
            'modelId' => $this->modelId,
            'commentId' => $comment->id,
        ]);
    }

    public function cancelEdit(): void
    {
        $this->editingCommentId = null;
        $this->editedContent = '';
        $this->resetErrorBag('editedContent');
    }

    public function render()
    {
        return view('reactable::livewire.tflr_comments');
    }
}
