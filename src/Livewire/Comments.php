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
        $this->commentsCount = $model->comments()->count();
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
            ->with('user')
            ->latest()
            ->take($this->perPage);

        $allComments = $commentsQuery->get();
        
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
        if (! auth()->check()) {
            return;
        }

        $model = $this->getModel();
        $deleted = $model->removeComment($commentId, auth()->user());

        if ($deleted) {
            $this->comments = array_filter(
                $this->comments,
                fn ($comment) => $comment['id'] !== $commentId
            );
            $this->comments = array_values($this->comments); // Re-index array
            $this->commentsCount--;

            $this->dispatch('comment-deleted', [
                'modelType' => $this->modelType,
                'modelId' => $this->modelId,
                'commentId' => $commentId,
            ]);
        }
    }

    public function render()
    {
        return view('reactable::livewire.tflr_comments');
    }
}
