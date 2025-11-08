<div class="reactions-component" x-data="{ showPicker: false, showList: false }">
    <div class="flex flex-row-reverse items-center justify-between gap-4">
        <!-- Main Reaction Button (Facebook-style) -->
        <div class="relative">
            <!-- Primary Button -->
            <button
                x-ref="likeBtn"
                @keydown.escape="showPicker = false"
                @mouseenter="showPicker = true"
                @mouseleave="showPicker = false"
                @click="showPicker = !showPicker"
                @keydown.enter.space.prevent="showPicker = !showPicker"
                type="button"
                :aria-expanded="showPicker.toString()"
                aria-haspopup="menu"
                :aria-label="'{{$userReaction}}' ? 'Change your reaction' : 'Add a reaction'"
                class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-800
                    {{ $userReaction ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400' }}"
            >
                @if($userReaction && isset($reactionTypes[$userReaction]))
                    <span class="text-lg">{{ $reactionTypes[$userReaction]['icon'] }}</span>
                    <span class="text-sm font-medium">{{ $reactionTypes[$userReaction]['label'] }}</span>
                @else
                    <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                    </svg>
                    <span class="text-sm font-medium">Like</span>
                @endif
            </button>

            <!-- Reaction Picker (Alpine Anchor) -->
            <template x-teleport="body">
                <div
                    x-show="showPicker"
                    x-cloak
                    role="menu"
                    @keydown.right="$focus.wrap().next()"
                    @keydown.left="$focus.wrap().previous()"
                    @keydown.escape="showPicker = false; $refs.likeBtn.focus()"
                    x-trap.noscroll="showPicker"
                    x-anchor.top-end.offset.8="$refs.likeBtn"
                    x-transition
                    @mouseenter="showPicker = true"
                    @mouseleave="showPicker = false"
                    x-init="() => { if(showPicker) $nextTick(() => $el.querySelector('button').focus()) }"
                    class="z-50"
                >
                    <div class="bg-white dark:bg-gray-800 rounded-full shadow-xl border border-gray-200 dark:border-gray-700 px-3 py-2">
                        <div class="flex items-center gap-2">
                            @foreach($reactionTypes as $type => $config)
                                @if(method_exists($this->getModel(), 'canReact') && !$this->getModel()->canReact($type))
                                    @continue
                                @endif
                                <button
                                    @keydown.enter.space.prevent="react('{{ $type }}')"
                                    @keydown.escape="showPicker = false; $refs.likeBtn.focus()"
                                    wire:click="react('{{ $type }}')"
                                    type="button"
                                    role="menuitemradio"
                                    :aria-checked="'{{ $type }}' === '{{ $userReaction }}'"
                                    class="reaction-picker-btn relative transition-all duration-200 hover:scale-125 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-full p-1"
                                    :title="'{{ $config['label'] }}'"
                                    :aria-label="'{{ $config['label'] }}'"
                                    :tabindex="showPicker ? '0' : '-1'"
                                >
                                    <span class="text-3xl block">{{ $config['icon'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </template>

            <!-- Screen Reader Status -->
            <template x-if="{{ $userReaction ? 'true' : 'false' }}">
                <div
                    role="status"
                    aria-live="polite"
                    class="sr-only"
                    x-text="`You reacted with {{$userReaction}}`"
                ></div>
            </template>
        </div>

        <!-- Reaction Count Summary -->
        @if($this->totalReactions > 0)
            <div class="relative">
                <button
                    x-ref="countBtn"
                    @keydown.escape="showList = false; $wire.call('closeReactionsList')"
                    @click="showList = !showList; if(showList) $wire.call('toggleReactionsList')"
                    @keydown.enter.space.prevent="showList = !showList; if(showList) $wire.call('toggleReactionsList')"
                    type="button"
                    :aria-expanded="showList.toString()"
                    :aria-controls="'reactions-dialog-{{ $this->modelId }}'"
                    aria-haspopup="dialog"
                    aria-label="View all {{ $this->totalReactions }} reactions"
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
                            @if(isset($reactionTypes[$type]))
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white dark:bg-gray-800 border-2 border-white dark:border-gray-900 text-sm">
                                    {{ $reactionTypes[$type]['icon'] }}
                                </span>
                            @endif
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
                <template x-teleport="body">
                    <div
                        x-data="{ currentListType: null }"
                        x-show="showList"
                        x-trap.noscroll="showList"
                        x-cloak
                        @click.away="showList = false; $wire.call('closeReactionsList')"
                        id="reactions-dialog-{{ $this->modelId }}"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="reactions-dialog-title-{{ $this->modelId }}"
                        aria-describedby="reactions-dialog-description-{{ $this->modelId }}"
                        @keydown.right="$focus.wrap().next()"
                        @keydown.left="$focus.wrap().previous()"
                        @keydown.up="$focus.wrap().previous()"
                        @keydown.down="$focus.wrap().next()"

                        @keydown.escape="showList = false; $wire.call('closeReactionsList')"
                        x-anchor.bottom-start.offset.8="$refs.countBtn"
                        x-transition
                        class="z-50 min-w-[320px] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700"
                    >
                        <p id="reactions-dialog-title-{{ $this->modelId }}" class="sr-only">
                            {{ __('Reactions List') }}
                        </p>
                        <p id="reactions-dialog-description-{{ $this->modelId }}" class="sr-only">
                            {{ __('List of users who reacted and their reaction type') }}
                        </p>

                        @if($showReactionsList)
                            <!-- Tabs for filtering by reaction type -->
                            <div
                                role="group"
                                aria-label="{{ __('Filter reactions by type') }}"
                                class="flex items-center gap-1 p-2 border-b border-gray-200 dark:border-gray-700"
                            ><button
                                    wire:ignore.self
                                    wire:click="filterReactionsByType(null); currentListType = 'all'"
                                    class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
                                    {{ $selectedReactionFilter === null ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400' }}"
                                >
                                    <span>All</span>
                                    <span class="text-xs">{{ $this->totalReactions }}</span>
                                </button>

                                @foreach($reactionTypes as $type => $config)
                                    @if(method_exists($this->getModel(), 'canReact') && !$this->getModel()->canReact($type))
                                        @continue
                                    @endif
                                    @if($reactions[$type] > 0)
                                        <button
                                            type="button"
                                            wire:ignore.self
                                            wire:click="filterReactionsByType('{{ $type }}'); currentListType = '{{ $type }}'"
                                            class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400
                                            {{ $selectedReactionFilter === $type ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                                            aria-pressed="{{ $selectedReactionFilter === $type ? 'true' : 'false' }}"
                                            aria-label="{{ __('Show :reaction reactions', ['reaction' => $config['label']]) }}"
                                        >
                                            <span class="text-lg">{{ $config['icon'] }}</span>
                                            <span class="text-xs">{{ $reactions[$type] }}</span>
                                            <span class="sr-only">{{ $config['label'] }}</span>
                                        </button>
                                    @endif
                                @endforeach
                            </div>

                            <!-- Users list -->
                            <div
                                class="p-3 max-h-96 overflow-y-scroll no-scrollbar"
                                role="region"
                                aria-live="polite"
                                x-trap="currentListType ==='{{$selectedReactionFilter}}' || currentListType === 'all' "
                                x-cloak
                                :aria-busy="$wire.isLoadingReactions"
                                aria-label="{{ __('List of users who reacted with :reaction', ['reaction' => $reactionTypes[$selectedReactionFilter]['label'] ?? 'reactions']) }}"
                            >

                                @if(count($reactionUsers) > 0)

                                    <div class="space-y-2"
                                    >
                                        @foreach($reactionUsers as $reactionUser)
                                            @if(method_exists($this->getModel(), 'canReact') && !$this->getModel()->canReact($reactionUser['type']))
                                                @continue
                                            @endif


                                            @if(isset($reactionTypes[$reactionUser['type']]))

                                                <div
                                                    tabindex="0"
                                                    @keydown.escape.stop="currentListType = null"
                                                    class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                    <div
                                                        class="flex items-center gap-3 min-w-0 flex-1">
                                                        <div class="flex-shrink-0">

                                                            @php
                                                                $avatarUrl = $reactionUser['avatar_url'];
                                                                @endphp
                                                            @if($avatarUrl)
                                                            <img src="{{ $avatarUrl }}" alt="User {{$reactionUser['user_name']}}" class="rounded-full w-10 h-10">
                                                            @else
                                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold">
                                                                {{ substr($reactionUser['user_name'], 0, 1) }}
                                                            </div>
                                                                @endif
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
                                            @endif
                                        @endforeach

                                        <!-- Load more spinner -->
                                        <div wire:key="loadMore" class="max-w-6xl h-6 mx-auto" x-intersect.full="$wire.loadMore();">
                                            <div wire:loading wire:target="loadMore" role="status" aria-live="assertive" class="flex w-full justify-center py-6">
                                                <span class="sr-only">Loading more reactions...</span>
                                                <div class="flex items-center gap-3 px-6 py-3">
                                                    <svg aria-hidden="true" class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <span class="text-gray-700 font-medium">Loading more reactions...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No reactions</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </template>
            </div>
        @endif
    </div>

    <style>
        /* Hide scrollbar in Chrome, Safari, and Opera */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        /* Hide scrollbar in IE, Edge, and Firefox */
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</div>
