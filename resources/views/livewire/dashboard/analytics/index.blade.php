<?php

use App\Models\Listing;
use App\Models\Inquiry;
use App\Models\ViewingRequest;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public function with(): array
    {
        $userId = auth()->id();
        
        $totalListings = Listing::where('owner_id', $userId)->count();
        $totalViews = Listing::where('owner_id', $userId)->sum('views_count');
        $totalInquiries = Inquiry::where('owner_id', $userId)->count();
        $totalViewingRequests = ViewingRequest::where('owner_id', $userId)->count();

        $topListings = Listing::where('owner_id', $userId)
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get();

        $recentInquiries = Inquiry::where('owner_id', $userId)
            ->with('listing')
            ->latest()
            ->take(5)
            ->get();

        return [
            'stats' => [
                ['label' => __('Total Listings'), 'value' => $totalListings, 'icon' => 'building-office'],
                ['label' => __('Total Views'), 'value' => number_format($totalViews), 'icon' => 'eye'],
                ['label' => __('Inquiries'), 'value' => $totalInquiries, 'icon' => 'chat-bubble-left-right'],
                ['label' => __('Waitlist'), 'value' => $totalViewingRequests, 'icon' => 'calendar'],
            ],
            'topListings' => $topListings,
            'recentInquiries' => $recentInquiries,
        ];
    }
}; ?>

<section class="space-y-8">
    <div>
        <h1 class="text-xl font-semibold text-neutral-100">{{ __('Analytics & Insights') }}</h1>
        <p class="text-sm text-neutral-400">{{ __('Monitor how your units are performing and track customer interest.') }}</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="rounded-xl border border-neutral-800/80 bg-neutral-900/70 p-6 shadow-lg shadow-black/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-neutral-400">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-3xl font-bold text-neutral-100">{{ $stat['value'] }}</p>
                    </div>
                    <div class="rounded-lg bg-neutral-800 p-3">
                        <flux:icon :name="$stat['icon']" class="h-6 w-6 text-primary-500" />
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-8 lg:grid-cols-2">
        <!-- Top performing listings -->
        <div class="rounded-xl border border-neutral-800/80 bg-neutral-900/70 shadow-lg shadow-black/20">
            <div class="border-b border-neutral-800 p-6">
                <h3 class="text-lg font-semibold text-neutral-100">{{ __('Top Performing Listings') }}</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse ($topListings as $listing)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="{{ $listing->getFirstMediaUrl('listings', 'thumb') ?: asset('images/placeholder-listing.jpg') }}" class="h-10 w-10 rounded object-cover border border-neutral-800">
                                <div>
                                    <p class="text-sm font-medium text-neutral-100">{{ $listing->title }}</p>
                                    <p class="text-xs text-neutral-500">{{ $listing->location_text }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-neutral-100">{{ number_format($listing->views_count) }}</p>
                                <p class="text-xs text-neutral-500">{{ __('Views') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 italic">{{ __('No listing data available yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Conversion overview (placeholder for now) -->
         <div class="rounded-xl border border-neutral-800/80 bg-neutral-900/70 shadow-lg shadow-black/20">
            <div class="border-b border-neutral-800 p-6">
                <h3 class="text-lg font-semibold text-neutral-100">{{ __('Recent Inquiries') }}</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse ($recentInquiries as $inquiry)
                        <div class="flex items-start gap-3">
                            <div class="mt-1 flex-shrink-0">
                                <flux:icon.chat-bubble-bottom-center-text class="h-5 w-5 text-neutral-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-neutral-100 truncate">
                                    {{ $inquiry->customer_name }} {{ __('on') }} {{ $inquiry->listing->title }}
                                </p>
                                <p class="text-xs text-neutral-500 line-clamp-1 italic">
                                    "{{ $inquiry->message }}"
                                </p>
                            </div>
                            <div class="ms-auto whitespace-nowrap text-xs text-neutral-600">
                                {{ $inquiry->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                         <p class="text-sm text-neutral-500 italic">{{ __('No inquiries yet.') }}</p>
                    @endforelse
                </div>
                <div class="mt-6">
                    <flux:button variant="ghost" size="sm" class="w-full" href="{{ route('dashboard.inquiries.index') }}" wire:navigate>
                        {{ __('View all inquiries') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</section>
