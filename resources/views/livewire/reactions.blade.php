<div class="reactions-component">
    <div class="flex flex-row-reverse items-center gap-4">
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
            <div class="absolute bottom-full left-0 mb-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10 pointer-events-none group-hover:pointer-events-auto">
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
            <div class="flex items-center gap-2">
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
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $this->totalReactions }}
                    </span>
                @endif
            </div>
        @endif
    </div>
</div>
