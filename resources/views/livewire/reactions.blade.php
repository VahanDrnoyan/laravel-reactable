<div class="reactions-component">
    <div class="flex flex-row-reverse items-center justify-between gap-4">
        <!-- Main Reaction Button (Facebook-style) -->
        <div class="relative group">
            <!-- Primary Button -->
            <button
                wire:click="toggleReaction"
                type="button"
                class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-800
                    {{ $userReaction ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400' }}"
            >
                @if($userReaction && isset($reactionTypes[$userReaction]))
                    <span class="text-lg">{{ $reactionTypes[$userReaction]['icon'] }}</span>
                    <span class="text-sm font-medium">{{ $reactionTypes[$userReaction]['label'] }}</span>
                @else
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                    </svg>
                    <span class="text-sm font-medium">Like</span>
                @endif
            </button>

            <!-- Reaction Picker (appears on hover) - Facebook Style -->
            <div class="absolute bottom-full right-0 mb-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10 pointer-events-none group-hover:pointer-events-auto">
                <div class="bg-white dark:bg-gray-800 rounded-full shadow-xl border border-gray-200 dark:border-gray-700 px-3 py-2">
                    <div class="flex items-center gap-2">
                        @foreach($reactionTypes as $type => $config)
                            <button
                                wire:click="react('{{ $type }}')"
                                type="button"
                                class="reaction-picker-btn group/btn relative transition-all duration-200 hover:scale-150 hover:-translate-y-1 focus:outline-none"
                                title="{{ $config['label'] }}"
                            >
                                <span class="text-3xl block">{{ $config['icon'] }}</span>

                                @if(config('reactable.display.show_tooltips', true))
                                    <!-- Tooltip -->
                                    <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover/btn:opacity-100 transition-opacity">
                                        {{ $config['label'] }}
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Reaction Count Summary -->
        @if($this->totalReactions > 0)
            <div class="relative" x-data="{ open: @entangle('showReactionsList') }" @click.away="$wire.closeReactionsList()">
                <button
                    wire:click="toggleReactionsList"
                    type="button"
                    class="flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg px-2 py-1 transition-colors cursor-pointer"
                >
                    <!-- Reaction Icons Summary -->
                    <div class="flex items-center -space-x-1">
                        @php
                            $displayedReactions = collect($reactions)
                                ->filter(fn($count) => $count > 0)
                                ->sortDesc()
                                ->take(3);
                        @endphp

                        @foreach($displayedReactions as $type => $count)
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white dark:bg-gray-800 border-2 border-white dark:border-gray-900 text-sm">
                                {{ $reactionTypes[$type]['icon'] }}
                            </span>
                        @endforeach
                    </div>

                    <!-- Total Count -->
                    @if(config('reactable.display.show_total', true))
                        <span class="text-sm text-gray-600 dark:text-gray-400 font-medium">
                            {{ $this->totalReactions }}
                        </span>
                    @endif
                </button>

                <!-- Reactions List Dropdown -->
                @if($showReactionsList)
                    <div class="absolute bottom-full left-0 mb-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50">
                        <!-- Tabs for filtering by reaction type -->
                        <div class="flex items-center gap-1 p-2 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                            <button
                                wire:click="filterReactionsByType(null)"
                                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
                                    {{ $selectedReactionFilter === null ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                            >
                                <span>All</span>
                                <span class="text-xs">{{ $this->totalReactions }}</span>
                            </button>

                            @foreach($reactionTypes as $type => $config)
                                @if($reactions[$type] > 0)
                                    <button
                                        wire:click="filterReactionsByType('{{ $type }}')"
                                        class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
                                            {{ $selectedReactionFilter === $type ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                                    >
                                        <span class="text-lg">{{ $config['icon'] }}</span>
                                        <span class="text-xs">{{ $reactions[$type] }}</span>
                                    </button>
                                @endif
                            @endforeach
                        </div>

                        <!-- Users list -->
                        <div class="p-3 max-h-80 overflow-y-auto">
                            @if(count($reactionUsers) > 0)
                                <div class="space-y-2">
                                    @foreach($reactionUsers as $reactionUser)
                                        <div class="flex items-center justify-between py-2 px-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                                                    {{ substr($reactionUser['user_name'], 0, 1) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                        {{ $reactionUser['user_name'] }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $reactionUser['created_at'] }}
                                                    </p>
                                                </div>
                                            </div>
                                            <span class="text-2xl flex-shrink-0 ml-2">
                                                {{ $reactionTypes[$reactionUser['type']]['icon'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                    No reactions
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
