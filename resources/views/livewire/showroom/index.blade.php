<?php

use App\Models\User;
use App\Models\Listing;
use Livewire\Volt\Component;

new class extends Component {
    public User $user;
    public string $search = '';
    public string $activeCategory = 'all';
    public string $sort = 'recommended';

    protected $queryString = [
        'search' => ['except' => ''],
        'activeCategory' => ['except' => 'all'],
        'sort' => ['except' => 'recommended'],
    ];

    public function mount(string $key): void
    {
        $this->user = User::where('public_key', $key)
            ->orWhere('public_slug', $key)
            ->firstOrFail()
            ->load('listings.categories');
    }

    public function with(): array
    {
        $query = $this->user->listings()->available();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('location_text', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->activeCategory !== 'all') {
            $query->whereHas('categories', function($q) {
                $q->where('slug', $this->activeCategory);
            });
        }

        match($this->sort) {
            'price_low' => $query->orderBy('price_per_night', 'asc'),
            'price_high' => $query->orderBy('price_per_night', 'desc'),
            'rating' => $query->orderBy('views_count', 'desc'), // Rating placeholder for now
            default => $query->latest(),
        };

        return [
            'listings' => $query->get(),
            'categories' => $this->user->categories()->get(),
        ];
    }

    public function rendering($view, $data)
    {
        $view->layout('components.layouts.showroom');
    }
}; ?>
<div class="space-y-8">
    <!-- Header/Context -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-neutral-100 pb-8 dark:border-neutral-800">
        <div class="flex items-center gap-6">
            <div class="relative flex-shrink-0">
                <img src="{{ $user->avatar_url ?? 'https://www.gravatar.com/avatar/' . md5($user->email) . '?s=200&d=mp' }}" class="h-24 w-24 rounded-full border-4 border-white shadow-xl dark:border-neutral-800">
                @if($user->is_active)
                    <span class="absolute bottom-1 right-1 h-5 w-5 rounded-full border-4 border-white bg-emerald-500 dark:border-neutral-950"></span>
                @endif
            </div>
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">{{ $user->name }}</h1>
                <p class="text-neutral-500 dark:text-neutral-400">{{ __('Professional Property Consultant') }}</p>
                <div class="mt-2 flex items-center gap-2">
                    <flux:badge variant="secondary" size="sm" class="rounded-full">{{ __('Top Rated') }}</flux:badge>
                    <flux:badge variant="ghost" size="sm" class="rounded-full">{{ __('Active now') }}</flux:badge>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
             <flux:button variant="outline" size="sm" class="rounded-full" icon="share">
                {{ __('Share') }}
            </flux:button>
             <flux:button variant="outline" size="sm" class="rounded-full" icon="adjustments-horizontal">
                {{ __('Filters') }}
            </flux:button>
        </div>
    </header>

    <!-- Category Bar -->
    <div class="sticky top-[65px] z-40 -mx-4 bg-white/90 px-4 py-3 backdrop-blur dark:bg-neutral-950/90 sm:mx-0 sm:px-0">
        <div class="flex items-center justify-between gap-4">
            <div class="flex flex-1 items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
                <button 
                    wire:click="$set('activeCategory', 'all')"
                    @class([
                        'whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium transition duration-200 border',
                        'bg-neutral-900 text-white border-neutral-900 dark:bg-white dark:text-black' => $activeCategory === 'all',
                        'bg-white text-neutral-600 border-neutral-200 hover:border-neutral-900 dark:bg-neutral-900 dark:text-neutral-400 dark:border-neutral-800' => $activeCategory !== 'all'
                    ])
                >
                    {{ __('All properties') }}
                </button>
                @foreach($categories as $category)
                    <button 
                        wire:click="$set('activeCategory', '{{ $category->slug }}')"
                        @class([
                            'whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium transition duration-200 border',
                            'bg-neutral-900 text-white border-neutral-900 dark:bg-white dark:text-black' => $activeCategory === $category->slug,
                            'bg-white text-neutral-600 border-neutral-200 hover:border-neutral-900 dark:bg-neutral-900 dark:text-neutral-400 dark:border-neutral-800' => $activeCategory !== $category->slug
                        ])
                    >
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>

            <div class="hidden md:block">
                <flux:select wire:model.live="sort" size="sm" class="w-48 rounded-full">
                    <flux:select.option value="recommended">{{ __('Recommended') }}</flux:select.option>
                    <flux:select.option value="rating">{{ __('Top Rated') }}</flux:select.option>
                    <flux:select.option value="price_low">{{ __('Price: Low to High') }}</flux:select.option>
                    <flux:select.option value="price_high">{{ __('Price: High to Low') }}</flux:select.option>
                </flux:select>
            </div>
        </div>
    </div>

    <!-- Listings Grid -->
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-neutral-900 dark:text-white">{{ __('Available Units') }}</h2>
            <p class="text-sm text-neutral-500">{{ __('Explore hand-picked condos from this showroom.') }}</p>
        </div>
        
        <div class="grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($listings as $listing)
                <div x-data="{ liked: false }" class="group relative flex flex-col gap-3">
                    <!-- Image Card -->
                    <div class="relative aspect-[4/3] w-full overflow-hidden rounded-[2rem] bg-neutral-100 dark:bg-neutral-800">
                        <a href="{{ route('listing.show', $listing) }}" wire:navigate class="block h-full w-full">
                            <img 
                                src="{{ $listing->getFirstMediaUrl('listings', 'large') ?: asset('images/placeholder-listing.jpg') }}" 
                                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                                onerror="this.src='{{ $listing->getFirstMediaUrl('listings') ?: asset('images/placeholder-listing.jpg') }}'"
                            >
                        </a>
                        
                        <!-- Top Badges/Overlay -->
                        <div class="absolute inset-x-0 top-0 flex items-center justify-between p-4 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                            <flux:badge variant="secondary" size="sm" class="rounded-full shadow-sm">{{ __('New') }}</flux:badge>
                            <button 
                                @click="liked = !liked"
                                class="rounded-full bg-white/80 p-2 backdrop-blur hover:bg-white transition shadow-sm dark:bg-neutral-900/80"
                            >
                                <flux:icon.heart x-bind:class="liked ? 'fill-red-500 text-red-500' : 'text-neutral-600 dark:text-neutral-300'" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="px-1 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-bold text-neutral-900 dark:text-white leading-tight">
                                    <a href="{{ route('listing.show', $listing) }}" wire:navigate class="hover:underline">{{ $listing->title }}</a>
                                </h3>
                                <p class="text-sm text-neutral-500 line-clamp-1">{{ $listing->location_text }}</p>
                            </div>
                            <div class="flex items-center gap-1 text-sm font-semibold">
                                <flux:icon.star variant="mini" class="h-3.5 w-3.5 text-yellow-500" />
                                <span>4.95</span>
                            </div>
                        </div>

                        <div class="text-sm">
                            <span class="font-bold text-neutral-900 dark:text-white">${{ number_format($listing->price_per_night) }}</span>
                            <span class="text-neutral-500">{{ __('night') }}</span>
                        </div>

                        <!-- CTA row (Airbnb inspired but functional for our app) -->
                        <div class="flex flex-wrap gap-2 pt-2">
                            <flux:button size="sm" variant="primary" class="rounded-full" href="{{ route('listing.show', $listing) }}" wire:navigate>
                                {{ __('View details') }}
                            </flux:button>
                            <flux:button size="sm" variant="ghost" class="rounded-full border border-neutral-200 dark:border-neutral-800" icon="chat-bubble-left-right">
                                {{ __('Inquire') }}
                            </flux:button>
                        </div>

                        <!-- Review/Context strip -->
                        <div class="pt-4 flex items-center justify-between text-[10px] uppercase tracking-wider font-bold text-neutral-400">
                             <span>{{ $listing->views_count }} {{ __('views this week') }}</span>
                             <div class="h-1 w-1 rounded-full bg-neutral-300"></div>
                             <span>{{ __('No login required') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-24 text-center">
                    <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-neutral-100 dark:bg-neutral-800">
                        <flux:icon.magnifying-glass class="h-8 w-8 text-neutral-300" />
                    </div>
                    <h3 class="mt-4 text-xl font-bold text-neutral-900 dark:text-white">{{ __('No listings found') }}</h3>
                    <p class="mt-2 text-neutral-500">{{ __('Try adjusting your filters or search keywords.') }}</p>
                    <flux:button variant="ghost" class="mt-6" wire:click="$set('search', '')">{{ __('Clear all filters') }}</flux:button>
                </div>
            @endforelse
        </div>
    </div>
</div>
