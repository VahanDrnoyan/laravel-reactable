<div class="comments-component" x-data="{ showComments: @entangle('showComments') }">
    <!-- Comments Header/Toggle Button -->
    <div class="flex items-center justify-between py-2">
        <button
            @click="$wire.toggleComments()"
            type="button"
            class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400"
            aria-label="{{ $commentsCount > 0 ? __('View :count comments', ['count' => $commentsCount]) : __('Add a comment') }}"
        >
            <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span class="text-sm font-medium">
                @if($commentsCount > 0)
                    {{ $commentsCount }} {{ Str::plural('Comment', $commentsCount) }}
                @else
                    Comment
                @endif
            </span>
        </button>
    </div>

    <!-- Comments Section -->
    <div x-show="showComments" x-collapse x-cloak>
        <!-- Add Comment Form -->
        <div class="mb-4">
            <form wire:submit.prevent="addComment" class="flex gap-2">
                <div class="flex-1">
                    <textarea
                        wire:model="newComment"
                        placeholder="{{ auth()->check() ? __('Write a comment...') : __('Please login to comment') }}"
                        rows="2"
                        {{ !auth()->check() ? 'disabled' : '' }}
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-800 dark:text-white resize-none"
                        aria-label="{{ __('Comment text') }}"
                    ></textarea>
                    @error('newComment')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                @if(auth()->check())
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="self-start px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        aria-label="{{ __('Post comment') }}"
                    >
                        <span wire:loading.remove wire:target="addComment">Post</span>
                        <span wire:loading wire:target="addComment">
                            <svg aria-hidden="true" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                @endif
            </form>
        </div>

        <!-- Comments List -->
        <div class="space-y-3" role="region" aria-label="{{ __('Comments list') }}">
            @if(count($comments) > 0)
                @foreach($this->commentsWithModels as $comment)
                    <div 
                        wire:key="comment-{{ $comment['id'] }}"
                        class="flex gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                    >
                        <!-- User Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
                                {{ substr($comment['user_name'], 0, 1) }}
                            </div>
                        </div>

                        <!-- Comment Content -->
                        <div class="flex-1 min-w-0">
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-3 py-2">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $comment['user_name'] }}
                                </p>
                                <p class="text-sm text-gray-800 dark:text-gray-200 mt-1 break-words">
                                    {{ $comment['content'] }}
                                </p>
                            </div>
                            
                            <!-- Comment Meta -->
                            <div class="flex items-center gap-3 mt-1 px-3">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $comment['created_at'] }}
                                </span>
                                
                                @if($comment['can_delete'])
                                    <button
                                        wire:click="deleteComment({{ $comment['id'] }})"
                                        wire:confirm="Are you sure you want to delete this comment?"
                                        type="button"
                                        class="text-xs text-red-600 dark:text-red-400 hover:underline"
                                        aria-label="{{ __('Delete comment') }}"
                                    >
                                        Delete
                                    </button>
                                @endif
                            </div>

                            <!-- Comment Reactions (if enabled) -->
                            @if(config('reactable.comments.enable_reactions', true) && isset($comment['model']))
                                <div class="mt-2 px-3">
                                    <livewire:reactions :model="$comment['model']" :key="'comment-reaction-'.$comment['id']" />
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                <!-- Load More Button -->
                @if($hasMoreComments)
                    <div class="text-center pt-2">
                        <button
                            wire:click="loadMore"
                            type="button"
                            class="text-sm text-blue-600 dark:text-blue-400 hover:underline font-medium"
                            aria-label="{{ __('Load more comments') }}"
                        >
                            <span wire:loading.remove wire:target="loadMore">Load more comments</span>
                            <span wire:loading wire:target="loadMore">Loading...</span>
                        </button>
                    </div>
                @endif
            @elseif($showComments)
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                    {{ __('No comments yet. Be the first to comment!') }}
                </p>
            @endif
        </div>
    </div>
</div>
