<x-layouts.app :title="__('Platform Admin')">
    @php
        $statsCards = [
            [
                'label' => __('Total Owners'),
                'value' => number_format($stats['totalOwners']),
                'icon' => 'users',
                'color' => 'blue',
            ],
            [
                'label' => __('Total Listings'),
                'value' => number_format($stats['totalListings']),
                'icon' => 'building-office-2',
                'color' => 'emerald',
            ],
            [
                'label' => __('Inquiries (24h)'),
                'value' => number_format($stats['inquiries24h']),
                'hint' => __('Total inquiries in last 24h'),
                'icon' => 'chat-bubble-left-right',
                'color' => 'amber',
            ],
            [
                'label' => __('Pending Viewings'),
                'value' => number_format($stats['pendingViewings']),
                'icon' => 'calendar',
                'color' => 'red',
            ],
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-neutral-500">{{ __('Platform Overview') }}</p>
                <h1 class="text-2xl font-semibold text-neutral-100">{{ __('Super Admin Dashboard') }}</h1>
                <p class="text-sm text-neutral-400">{{ __('Global metrics and platform health monitoring.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button variant="primary" icon="user-plus" :href="route('admin.owners.index')" wire:navigate>
                    {{ __('Create Owner') }}
                </flux:button>
                <flux:button variant="ghost" icon="arrow-down-tray">
                    {{ __('Export Report') }}
                </flux:button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($statsCards as $stat)
                <div class="rounded-xl border border-neutral-800/80 bg-neutral-900/70 p-5 shadow-lg shadow-black/20 backdrop-blur transition-all hover:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-neutral-400">{{ $stat['label'] }}</p>
                        <flux:icon name="{{ $stat['icon'] }}" variant="mini" class="text-{{ $stat['color'] }}-500 h-5 w-5" />
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-4xl font-bold text-neutral-50">{{ $stat['value'] }}</span>
                    </div>
                    @if(isset($stat['hint']))
                        <p class="mt-1 text-xs text-neutral-500">{{ $stat['hint'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <!-- Needs Attention -->
            <div class="rounded-xl border border-neutral-800/80 bg-neutral-900/70 p-6 shadow-lg shadow-black/20 backdrop-blur">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-neutral-100">{{ __('Needs Attention') }}</h2>
                    <flux:badge color="red">{{ __('3 items') }}</flux:badge>
                </div>
                <div class="space-y-4">
                    <div class="flex items-start gap-3 rounded-lg border border-red-500/20 bg-red-500/5 p-3">
                        <flux:icon.exclamation-triangle class="mt-0.5 h-5 w-5 text-red-500" />
                        <div>
                            <p class="text-sm font-medium text-neutral-100">{{ __('Flagged Listing') }}</p>
                            <p class="text-xs text-neutral-400">{{ __('Listing "Coastal Villa" flagged for potential spam.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 rounded-lg border border-amber-500/20 bg-amber-500/5 p-3">
                        <flux:icon.credit-card class="mt-0.5 h-5 w-5 text-amber-500" />
                        <div>
                            <p class="text-sm font-medium text-neutral-100">{{ __('Pending Verification') }}</p>
                            <p class="text-xs text-neutral-400">{{ __('2 new owners awaiting identity verification.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Platform Pipeline -->
            <div class="rounded-xl border border-neutral-800/80 bg-neutral-900/70 p-6 shadow-lg shadow-black/20 backdrop-blur">
                <h2 class="mb-4 text-lg font-semibold text-neutral-100">{{ __('Growth Trend (Owners)') }}</h2>
                <div class="grid grid-cols-6 items-end gap-3 h-40">
                    @foreach ($monthlySeries as $point)
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-full rounded-t-lg bg-gradient-to-t from-blue-600 to-blue-400 shadow-[0_0_15px_rgba(59,130,246,0.5)] transition-all hover:scale-110" style="height: {{ max(20, $point['value'] * 15) }}px"></div>
                            <span class="text-[10px] text-neutral-500 uppercase">{{ $point['label'] }}</span>
                            <span class="text-[10px] font-bold text-neutral-300">{{ $point['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
            <div class="xl:col-span-2 rounded-xl border border-neutral-800/80 bg-neutral-900/70 p-6 shadow-lg shadow-black/20 backdrop-blur">
                <h2 class="mb-4 text-lg font-semibold text-neutral-100">{{ __('Recent Platform Activity') }}</h2>
                <div class="space-y-4">
                    <div class="flex items-center gap-4 py-2 border-b border-neutral-800 last:border-0 text-sm">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span class="text-neutral-400 w-24">2 mins ago</span>
                        <span class="text-neutral-100 font-medium">New owner registered:</span>
                        <span class="text-blue-400 italic">Ocean Breeze Realty</span>
                    </div>
                    <div class="flex items-center gap-4 py-2 border-b border-neutral-800 last:border-0 text-sm">
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        <span class="text-neutral-400 w-24">15 mins ago</span>
                        <span class="text-neutral-100 font-medium">New listing created:</span>
                        <span class="text-neutral-400 italic">"Mountain Escape...</span>
                    </div>
                    <div class="flex items-center gap-4 py-2 border-b border-neutral-800 last:border-0 text-sm">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        <span class="text-neutral-400 w-24">1 hour ago</span>
                        <span class="text-neutral-100 font-medium">Inquiry received for:</span>
                        <span class="text-neutral-400 italic">"Luxury Condo...</span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-800/80 bg-neutral-900/70 p-6 shadow-lg shadow-black/20 backdrop-blur">
                <h2 class="mb-4 text-lg font-semibold text-neutral-100">{{ __('Quick Shortcuts') }}</h2>
                <div class="grid gap-2">
                    <flux:button variant="ghost" class="justify-start text-neutral-300" icon="users" :href="route('admin.owners.index')" wire:navigate>{{ __('Manage All Owners') }}</flux:button>
                    <flux:button variant="ghost" class="justify-start text-neutral-300" icon="building-office-2" :href="route('admin.listings.index')" wire:navigate>{{ __('Global Listings') }}</flux:button>
                    <flux:button variant="ghost" class="justify-start text-neutral-300" icon="chat-bubble-left-right" :href="route('admin.inquiries.index')" wire:navigate>{{ __('Monitor Inquiries') }}</flux:button>
                    <flux:button variant="ghost" class="justify-start text-neutral-300" icon="cog" href="#">{{ __('Platform Settings') }}</flux:button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
