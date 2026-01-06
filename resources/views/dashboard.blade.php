<x-layouts.app :title="__('Dashboard')">
    @php
        $statsCards = [
            [
                'label' => __('Active listings'),
                'value' => number_format($stats['activeListings']),
                'hint' => __('of :total total', ['total' => number_format($stats['totalListings'])]),
            ],
            [
                'label' => __('Avg nightly rate'),
                'value' => '$' . number_format($stats['avgNightlyRate'], 2),
                'hint' => __('across your portfolio'),
            ],
            [
                'label' => __('Occupancy proxy'),
                'value' => $stats['occupancyPercent'] . '%',
                'hint' => __('unavailable vs total'),
            ],
            [
                'label' => __('Open viewing requests'),
                'value' => number_format($stats['openViewingRequests']),
                'hint' => __('pending approvals'),
            ],
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-neutral-500">{{ __('Condo Owner') }}</p>
                <h1 class="text-2xl font-semibold text-neutral-100">{{ __('Portfolio overview') }}</h1>
                <p class="text-sm text-neutral-400">{{ __('Live metrics from your listings, inquiries, and viewing requests.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button variant="primary" href="{{ route('dashboard.listings.create') }}" wire:navigate>
                    {{ __('Add listing') }}
                </flux:button>
                <flux:button variant="ghost" href="{{ route('dashboard.listings.index') }}" wire:navigate>
                    {{ __('Manage listings') }}
                </flux:button>
                <flux:button variant="ghost" href="{{ $shareLink }}" target="_blank">
                    {{ __('View public showroom') }}
                </flux:button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($statsCards as $stat)
                <div class="rounded-xl border border-neutral-800/80 bg-neutral-900/70 px-4 py-3 shadow-lg shadow-black/20 backdrop-blur">
                    <p class="text-sm text-neutral-400">{{ $stat['label'] }}</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-semibold text-neutral-50">{{ $stat['value'] }}</span>
                    </div>
                    <p class="text-xs text-neutral-500">{{ $stat['hint'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
            <div class="xl:col-span-2 rounded-xl border border-neutral-800/80 bg-neutral-900/70 p-4 shadow-lg shadow-black/20 backdrop-blur">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-neutral-100">{{ __('Upcoming viewings') }}</h2>
                    <flux:button size="xs" variant="ghost" href="{{ route('dashboard.viewing-requests.index') }}" wire:navigate>
                        {{ __('See all') }}
                    </flux:button>
                </div>
                <div class="mt-4 divide-y divide-neutral-800">
                    @forelse ($upcomingViewings as $viewing)
                        <div class="flex flex-col gap-1 py-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="font-medium text-neutral-50">{{ $viewing->customer_name }}</p>
                                <p class="text-sm text-neutral-400">{{ $viewing->listing?->title }}</p>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-neutral-300">
                                <flux:badge variant="ghost">{{ $viewing->status }}</flux:badge>
                                <span class="text-neutral-400">{{ $viewing->preferred_datetime->format('M d, Y h:i A') }}</span>
                                <flux:button size="xs" variant="ghost" href="{{ route('dashboard.viewing-requests.index', ['highlight' => $viewing->id]) }}" wire:navigate>
                                    {{ __('Details') }}
                                </flux:button>
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-sm text-neutral-400">{{ __('No upcoming viewings scheduled yet.') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-neutral-800/80 bg-neutral-900/70 p-4 shadow-lg shadow-black/20 backdrop-blur">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-neutral-100">{{ __('Recent inquiries') }}</h2>
                    <flux:button size="xs" variant="ghost" href="{{ route('dashboard.inquiries.index') }}" wire:navigate>
                        {{ __('See all') }}
                    </flux:button>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($recentInquiries as $inquiry)
                        <div class="rounded-lg border border-neutral-800 bg-neutral-900/80 p-3">
                            <p class="font-medium text-neutral-50">{{ $inquiry->customer_name }}</p>
                            <p class="text-sm text-neutral-400">{{ $inquiry->listing?->title }}</p>
                            <p class="text-xs text-neutral-500">{{ $inquiry->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="py-6 text-sm text-neutral-400">{{ __('No inquiries yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-xl border border-neutral-800/80 bg-neutral-900/70 p-4 shadow-lg shadow-black/20 backdrop-blur">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-neutral-100">{{ __('Listing trend') }}</h2>
                    <p class="text-sm text-neutral-400">{{ __('Last 6 months (created)') }}</p>
                </div>
                <div class="mt-4 grid grid-cols-6 items-end gap-3">
                    @foreach ($monthlySeries as $point)
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-full rounded-full bg-gradient-to-t from-neutral-800 to-emerald-500/80" style="height: {{ 16 + ($point['value'] * 8) }}px"></div>
                            <span class="text-xs text-neutral-400">{{ $point['label'] }}</span>
                            <span class="text-xs font-semibold text-neutral-100">{{ $point['value'] }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="mt-3 text-sm text-neutral-300">{{ __('Counts are pulled directly from your listings table.') }}</p>
            </div>

            <div class="rounded-xl border border-neutral-800/80 bg-neutral-900/70 p-4 shadow-lg shadow-black/20 backdrop-blur">
                <h2 class="text-lg font-semibold text-neutral-100">{{ __('Quick actions') }}</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-1">
                    <flux:button variant="ghost" class="justify-start" href="{{ route('dashboard.listings.create') }}" wire:navigate>
                        {{ __('Add a new listing') }}
                    </flux:button>
                    <flux:button variant="ghost" class="justify-start" href="{{ route('dashboard.listings.index') }}" wire:navigate>
                        {{ __('Manage listings') }}
                    </flux:button>
                    <flux:button variant="ghost" class="justify-start" href="{{ route('dashboard.viewing-requests.index') }}" wire:navigate>
                        {{ __('Review viewing requests') }}
                    </flux:button>
                    <flux:button variant="ghost" class="justify-start" href="{{ route('dashboard.inquiries.index') }}" wire:navigate>
                        {{ __('View inquiries') }}
                    </flux:button>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-800/80 bg-neutral-900/70 p-4 shadow-lg shadow-black/20 backdrop-blur">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-neutral-100">{{ __('Recent listings') }}</h2>
                <flux:button size="xs" variant="ghost" href="{{ route('dashboard.listings.index') }}" wire:navigate>
                    {{ __('View all') }}
                </flux:button>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-3">
                @forelse ($recentListings as $listing)
                    <div class="rounded-lg border border-neutral-800 bg-neutral-900/80 p-3">
                        <p class="font-medium text-neutral-50">{{ $listing->title }}</p>
                        <p class="text-sm text-neutral-400">${{ number_format($listing->price_per_night, 2) }} / {{ __('night') }}</p>
                        <p class="text-xs text-neutral-500">{{ $listing->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="py-6 text-sm text-neutral-400">{{ __('No listings yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
