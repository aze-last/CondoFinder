<?php

use App\Models\Listing;
use App\Models\Inquiry;
use App\Models\ViewingRequest;
use Livewire\Volt\Component;

new class extends Component {
    public Listing $listing;
    
    // Forms State
    public string $activeTab = 'inquiry';
    
    // Inquiry Form
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $message = '';
    
    // Viewing Request Form
    public string $preferred_date = '';
    public string $preferred_time = '';

    public function mount(Listing $listing): void
    {
        $this->listing = $listing->load('owner', 'categories');
        
        $this->listing->increment('views_count');
    }

    public function sendInquiry(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $inquiry = Inquiry::create([
            'listing_id' => $this->listing->id,
            'owner_id' => $this->listing->owner_id,
            'customer_name' => $this->name,
            'customer_email' => $this->email,
            'customer_phone' => $this->phone,
            'message' => $this->message,
        ]);

        $this->listing->owner->notify(new \App\Notifications\InquiryReceived($inquiry));

        $this->reset(['name', 'email', 'phone', 'message']);
        
        \Flux::toast(__('Your inquiry has been sent to the owner.'));
    }

    public function requestViewing(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'preferred_date' => ['required', 'date', 'after:yesterday'],
            'preferred_time' => ['required'],
        ]);

        $request = ViewingRequest::create([
            'listing_id' => $this->listing->id,
            'owner_id' => $this->listing->owner_id,
            'customer_name' => $this->name,
            'customer_email' => $this->email,
            'customer_phone' => $this->phone,
            'preferred_datetime' => $this->preferred_date . ' ' . $this->preferred_time,
            'status' => 'PENDING',
        ]);

        $this->listing->owner->notify(new \App\Notifications\ViewingRequestReceived($request));

        $this->reset(['name', 'email', 'phone', 'preferred_date', 'preferred_time']);
        
        \Flux::toast(__('Request sent. Wait for the Condo Admin to send a message.'));
    }

    public function rendering($view, $data)
    {
        $view->layout('components.layouts.showroom');
    }
}; ?>

<div class="space-y-10 pb-20">
    <!-- Breadcrumbs / Top Actions -->
    <div class="flex items-center justify-between">
        <flux:button variant="ghost" icon="chevron-left" href="{{ route('showroom.profile', ['key' => $listing->owner->public_slug ?: $listing->owner->public_key]) }}" wire:navigate>
            {{ __('Back to showroom') }}
        </flux:button>
        <div class="flex items-center gap-2">
            <flux:button variant="ghost" size="sm" icon="share" class="rounded-full">{{ __('Share') }}</flux:button>
            <flux:button variant="ghost" size="sm" icon="heart" class="rounded-full">{{ __('Save') }}</flux:button>
        </div>
    </div>

    <!-- Gallery Section -->
    <div class="grid grid-cols-1 md:grid-cols-4 md:grid-rows-2 gap-4 h-[50vh] min-h-[400px]">
        @php
            $media = $listing->getMedia('listings');
            $placeholder = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="800" height="450" viewBox=\"0 0 800 450\"><rect width=\"800\" height=\"450\" fill=\"%23252525\"/><text x=\"50%\" y=\"52%\" dominant-baseline=\"middle\" text-anchor=\"middle\" fill=\"%23b3b3b3\" font-family=\"Arial\" font-size=\"24\">No Image</text></svg>';
            $cover = $listing->getFirstMediaUrl('listings', 'large') ?: $placeholder;
        @endphp
        
        <div class="md:col-span-2 md:row-span-2 overflow-hidden rounded-3xl border border-neutral-100 dark:border-neutral-800 shadow-sm">
            <img src="{{ $cover }}" class="h-full w-full object-cover transition-transform duration-700 hover:scale-105" onerror="this.src='{{ $listing->getFirstMediaUrl('listings') ?: $placeholder }}'">
        </div>
        
        @foreach($media->skip(1)->take(4) as $index => $item)
            <div @class([
                'hidden md:block overflow-hidden border border-neutral-100 dark:border-neutral-800 shadow-sm transition-transform duration-700 hover:scale-105',
                'rounded-tr-3xl' => $index === 1,
                'rounded-br-3xl' => $index === 3,
                'rounded-none' => !in_array($index, [1, 3])
            ])>
                <img src="{{ $item->getUrl('large') }}" class="h-full w-full object-cover" onerror="this.src='{{ $item->getUrl() }}'">
            </div>
        @endforeach
        
        @php
            $remaining = 5 - $media->count();
            $mediaCount = $media->count();
        @endphp

        @if($remaining > 0)
            @for($i = 0; $i < $remaining; $i++)
                @php
                    $slotIndex = $mediaCount + $i;
                @endphp
                @if($slotIndex > 0) {{-- Skip the first slot as it's the cover --}}
                    <div @class([
                        'hidden md:flex items-center justify-center bg-neutral-100 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800',
                        'rounded-tr-3xl' => $slotIndex === 2,
                        'rounded-br-3xl' => $slotIndex === 4,
                    ])>
                        <svg class="h-8 w-8 text-neutral-300 dark:text-neutral-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
            @endfor
        @endif
    </div>

    <div class="grid gap-12 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-12">
            <!-- Header & Essential Info -->
            <div class="space-y-6">
                <div class="flex items-start justify-between">
                    <div class="space-y-2">
                        <h1 class="text-4xl font-bold tracking-tight text-neutral-900 dark:text-white">{{ $listing->title }}</h1>
                        <div class="flex flex-wrap items-center gap-3 text-sm text-neutral-500">
                             <div class="flex items-center gap-1 font-semibold text-neutral-900 dark:text-white">
                                <flux:icon.star variant="mini" class="h-4 w-4 text-yellow-500" />
                                <span>4.95</span>
                            </div>
                            <span>•</span>
                            <span class="underline font-medium hover:text-neutral-900 decoration-neutral-300">128 {{ __('reviews') }}</span>
                            <span>•</span>
                            <span class="font-medium underline decoration-neutral-300">{{ $listing->location_text }}</span>
                        </div>
                    </div>
                </div>

                <hr class="border-neutral-100 dark:border-neutral-800">

                <!-- Highlights -->
                <div class="space-y-8">
                    @foreach($listing->categories as $category)
                        <div class="flex items-start gap-4">
                            <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-neutral-50 text-neutral-600 dark:bg-neutral-900 dark:text-neutral-400">
                                <flux:icon.tag class="h-5 w-5" />
                            </div>
                            <div>
                                <h4 class="font-bold text-neutral-900 dark:text-white">{{ $category->name }}</h4>
                                <p class="text-sm text-neutral-500">{{ __('This property is categorized as') }} {{ strtolower($category->name) }}.</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <hr class="border-neutral-100 dark:border-neutral-800">
                
                <!-- Description -->
                <div class="prose prose-neutral dark:prose-invert max-w-none">
                    <h3 class="text-2xl font-bold">{{ __('About this home') }}</h3>
                    <p class="whitespace-pre-line text-neutral-600 dark:text-neutral-400 leading-relaxed">{{ $listing->description }}</p>
                </div>

                <hr class="border-neutral-100 dark:border-neutral-800">

                <!-- Location -->
                @if($listing->latitude && $listing->longitude)
                    <div class="space-y-6">
                        <div class="space-y-2">
                             <h3 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ __('Where you\'ll be') }}</h3>
                             <p class="text-neutral-500">{{ $listing->location_text }}</p>
                        </div>
                        <div class="h-[400px] w-full rounded-3xl overflow-hidden border border-neutral-100 shadow-inner dark:border-neutral-800">
                            <iframe
                                width="100%"
                                height="100%"
                                style="border:0"
                                loading="lazy"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade"
                                src="https://www.google.com/maps/embed/v1/place?key={{ config('services.google.maps_api_key') }}&q={{ $listing->latitude }},{{ $listing->longitude }}">
                            </iframe>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar Forms (Sticky) -->
        <div class="lg:col-span-1">
            <div class="sticky top-28 space-y-6">
                <!-- Main Booking Card -->
                <div class="rounded-3xl border border-neutral-100 bg-white p-8 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900/50">
                    <div class="flex items-baseline justify-between">
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-bold text-neutral-900 dark:text-white">${{ number_format($listing->price_per_night) }}</span>
                            <span class="text-neutral-500 text-sm">{{ __('night') }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-sm font-bold">
                            <flux:icon.star variant="mini" class="h-4 w-4 text-yellow-500" />
                            <span>4.95</span>
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="flex p-1 bg-neutral-100 dark:bg-neutral-800 rounded-xl mb-6">
                            <button 
                                wire:click="$set('activeTab', 'inquiry')" 
                                @class([
                                    'flex-1 flex items-center justify-center gap-2 py-2 px-4 rounded-lg text-sm font-medium transition duration-200',
                                    'bg-white shadow text-neutral-900 dark:bg-neutral-700 dark:text-white' => $activeTab === 'inquiry',
                                    'text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200' => $activeTab !== 'inquiry'
                                ])
                            >
                                <flux:icon.chat-bubble-left-right variant="mini" class="h-4 w-4" />
                                {{ __('Inquire') }}
                            </button>
                            <button 
                                wire:click="$set('activeTab', 'viewing')" 
                                @class([
                                    'flex-1 flex items-center justify-center gap-2 py-2 px-4 rounded-lg text-sm font-medium transition duration-200',
                                    'bg-white shadow text-neutral-900 dark:bg-neutral-700 dark:text-white' => $activeTab === 'viewing',
                                    'text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200' => $activeTab !== 'viewing'
                                ])
                            >
                                <flux:icon.calendar variant="mini" class="h-4 w-4" />
                                {{ __('Book Viewing') }}
                            </button>
                        </div>

                        <div class="space-y-4">
                            @if($activeTab === 'inquiry')
                                 @if (session('inquiry_status'))
                                    <div class="rounded-2xl bg-emerald-50/50 p-4 text-sm text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 italic">
                                        {{ session('inquiry_status') }}
                                    </div>
                                @else
                                    <form wire:submit="sendInquiry" class="space-y-4">
                                        <flux:input wire:model="name" :label="__('Your Name')" placeholder="John Doe" required />
                                        <flux:input wire:model="email" type="email" :label="__('Email address')" placeholder="john@example.com" required />
                                        <flux:input wire:model="phone" :label="__('Phone number')" placeholder="+63 9xx..." required />
                                        <flux:textarea wire:model="message" :label="__('How can we help?')" placeholder="I'm interested in this unit..." required rows="4" />
                                        <flux:button type="submit" variant="primary" class="w-full rounded-2xl h-12 text-lg font-bold shadow-lg shadow-primary-500/20">{{ __('Send Message') }}</flux:button>
                                    </form>
                                @endif
                                <p class="mt-4 text-center text-xs text-neutral-400">{{ __('You won\'t be charged yet') }}</p>
                            @else
                                 @if (session('viewing_status'))
                                    <div class="rounded-2xl bg-emerald-50/50 p-4 text-sm text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 italic">
                                        {{ session('viewing_status') }}
                                    </div>
                                @else
                                    <form wire:submit="requestViewing" class="space-y-4">
                                        <flux:input wire:model="name" :label="__('Your Name')" required />
                                        <flux:input wire:model="email" type="email" :label="__('Email')" required />
                                        <flux:input wire:model="phone" :label="__('Phone number')" placeholder="+63 9xx..." required />
                                        <div class="grid grid-cols-2 gap-4">
                                            <flux:input wire:model="preferred_date" type="date" :label="__('Date')" required />
                                            <flux:input wire:model="preferred_time" type="time" :label="__('Time')" required />
                                        </div>
                                        <flux:button type="submit" variant="primary" class="w-full rounded-2xl h-12 text-lg font-bold shadow-lg shadow-primary-500/20">{{ __('Request Schedule') }}</flux:button>
                                    </form>
                                @endif
                                <p class="mt-4 text-center text-xs text-neutral-400">{{ __('Owner will confirm your visit') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Owner Direct Card -->
                <div class="rounded-3xl border border-neutral-100 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900/50">
                    <div class="flex items-center gap-4">
                        <img src="{{ $listing->owner->avatar_url ?? 'https://www.gravatar.com/avatar/' . md5($listing->owner->email) }}" class="h-16 w-16 rounded-full shadow-sm">
                        <div class="flex-1">
                            <h4 class="font-bold text-neutral-900 dark:text-white leading-tight">{{ $listing->owner->name }}</h4>
                            <p class="text-xs text-neutral-500">{{ __('Professional Property Consultant') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

