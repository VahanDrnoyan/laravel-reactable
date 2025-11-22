<div class="comments-component">
    {{-- Comments Toggle Button (inline with reactions) --}}
    <button
        wire:click="toggleComments"
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

    {{-- Comments Section (full width, shown when expanded) --}}
    @if($showComments)
        <div class="mt-4">
            {{-- Add Comment Form --}}
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

            {{-- Comments List --}}
            <div class="space-y-3" role="region" aria-label="{{ __('Comments list') }}">
                @if(count($comments) > 0)
                    @foreach($this->commentsWithModels as $comment)
                        <div 
                            wire:key="comment-{{ $comment['id'] }}"
                            class="flex gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                        >
                            {{-- User Avatar --}}
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
                                    {{ substr($comment['user_name'], 0, 1) }}
                                </div>
                            </div>

                            {{-- Comment Content --}}
                            <div class="flex-1 min-w-0">
                                @if($editingCommentId === $comment['id'])
                                    {{-- Edit Mode --}}
                                    <div class="space-y-2">
                                        <textarea
                                            wire:model="editedContent"
                                            rows="3"
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-800 dark:text-white resize-none"
                                            aria-label="Edit comment"
                                        ></textarea>
                                        @error('editedContent')
                                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                        <div class="flex gap-2">
                                            <button
                                                wire:click="updateComment"
                                                type="button"
                                                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors"
                                            >
                                                Save
                                            </button>
                                            <button
                                                wire:click="cancelEdit"
                                                type="button"
                                                class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg transition-colors"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    {{-- View Mode --}}
                                    <div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-3 py-2">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $comment['user_name'] }}
                                        </p>
                                        <p class="text-sm text-gray-800 dark:text-gray-200 mt-1 break-words">
                                            {{ $comment['content'] }}
                                        </p>
                                    </div>
                                    
                                    {{-- Comment Meta --}}
                                    <div class="flex items-center gap-3 mt-1 px-3">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $comment['created_at'] }}
                                        </span>
                                        
                                        @if($comment['can_delete'])
                                            <button
                                                wire:click="editComment({{ $comment['id'] }})"
                                                type="button"
                                                class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                                aria-label="Edit comment"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                wire:click="deleteComment({{ $comment['id'] }})"
                                                type="button"
                                                class="text-xs text-red-600 dark:text-red-400 hover:underline"
                                                aria-label="{{ __('Delete comment') }}"
                                            >
                                                Delete
                                            </button>
                                        @endif
                                    </div>
                                @endif

                                {{-- Comment Reactions (if enabled) --}}
                                @if(config('reactable.comments.enable_reactions', true) && isset($comment['model']))
                                    <div class="mt-2 px-3">
                                        <livewire:reactions :model="$comment['model']" :key="'comment-reaction-'.$comment['id']" />
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    {{-- Load More Button --}}
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
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($deletingCommentId)
        <template x-teleport="body">
            <div 
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title" 
                role="dialog" 
                aria-modal="true"
                x-data
                x-trap.noscroll="true"
            >
                {{-- Backdrop --}}
                <div 
                    class="fixed inset-0 bg-gray-500/50 dark:bg-gray-900/50 transition-opacity"
                    aria-hidden="true"
                    wire:click="cancelDelete"
                ></div>

                {{-- Modal Content --}}
                <div class="flex min-h-full items-start justify-center p-4 pt-20">
                    <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                {{-- Icon --}}
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                </div>
                                
                                {{-- Content --}}
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                                        Delete Comment
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Are you sure you want to delete this comment? This action cannot be undone.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Actions --}}
                        <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                            <button 
                                type="button" 
                                wire:click="confirmDelete"
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:w-auto focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                            >
                                Delete
                            </button>
                            <button 
                                type="button" 
                                wire:click="cancelDelete"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
