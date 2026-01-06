<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php $user = auth()->user(); @endphp
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:spacer />

            <flux:dropdown position="bottom" align="end" class="mr-2">
                <flux:button variant="ghost" icon="bell" size="sm" class="relative group">
                    @if($unreadCount = $user?->unreadNotifications()->count())
                        <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white dark:ring-zinc-900 group-hover:scale-110 transition-transform">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </flux:button>

                <flux:menu class="w-[280px]">
                    <flux:menu.radio.group>
                        <div class="px-3 py-2 text-xs font-semibold text-neutral-500 uppercase tracking-wider">
                            {{ __('Recent Notifications') }}
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    @forelse($user?->unreadNotifications()->take(5)->get() ?? [] as $notification)
                        <flux:menu.item class="flex flex-col items-start gap-1 py-3">
                            <div class="flex items-center gap-2 w-full">
                                <flux:icon.envelope-open variant="mini" class="h-4 w-4 text-primary-500" />
                                <span class="font-bold text-sm truncate">{{ $notification->data['customer_name'] ?? 'System' }}</span>
                                <flux:spacer />
                                <span class="text-[10px] text-neutral-500">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="pl-6 text-xs text-neutral-400 line-clamp-1">
                                {{ $notification->type === 'App\Notifications\InquiryReceived' ? __('New inquiry for') : __('Viewing requested for') }} 
                                <span class="text-neutral-300">{{ $notification->data['listing_title'] ?? 'Listing' }}</span>
                            </div>
                        </flux:menu.item>
                    @empty
                        <div class="px-3 py-6 text-center text-xs text-neutral-500 italic">
                            {{ __('No unread notifications') }}
                        </div>
                    @endforelse

                    <flux:menu.separator />
                    
                    <flux:menu.item :href="route('dashboard.inquiries.index')" icon="chat-bubble-left-right" wire:navigate>{{ __('View all inquiries') }}</flux:menu.item>
                    <flux:menu.item :href="route('dashboard.viewing-requests.index')" icon="calendar" wire:navigate>{{ __('View all requests') }}</flux:menu.item>
                </flux:menu>
            </flux:dropdown>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Overview') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Manage')" class="grid">
                    <flux:navlist.item icon="building-office-2" :href="route('dashboard.listings.index')" :current="request()->routeIs('dashboard.listings.*')" wire:navigate>
                        {{ __('Listings') }}
                        @if($count = $user?->listings()->count())
                            <flux:navlist.badge>{{ $count }}</flux:navlist.badge>
                        @endif
                    </flux:navlist.item>
                    <flux:navlist.item icon="tag" :href="route('dashboard.categories.index')" :current="request()->routeIs('dashboard.categories.*')" wire:navigate>{{ __('Categories') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Engage')" class="grid">
                    <flux:navlist.item icon="chat-bubble-left-right" :href="route('dashboard.inquiries.index')" :current="request()->routeIs('dashboard.inquiries.*')" wire:navigate>
                        {{ __('Inquiries') }}
                        @if($count = $user?->inquiries()->count())
                             <flux:navlist.badge>{{ $count }}</flux:navlist.badge>
                        @endif
                    </flux:navlist.item>
                    <flux:navlist.item icon="calendar" :href="route('dashboard.viewing-requests.index')" :current="request()->routeIs('dashboard.viewing-requests.*')" wire:navigate>
                        {{ __('Waitlist') }} 
                        @if($count = $user?->viewingRequests()->where('status', 'PENDING')->count())
                             <flux:navlist.badge color="red">{{ $count }}</flux:navlist.badge>
                        @endif
                    </flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('dashboard.leads.index')" :current="request()->routeIs('dashboard.leads.*')" wire:navigate>{{ __('Leads') }}</flux:navlist.item>
                    <flux:navlist.item icon="calendar-days" :href="route('dashboard.calendar.index')" :current="request()->routeIs('dashboard.calendar.*')" wire:navigate>{{ __('Calendar') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Insights')" class="grid">
                    <flux:navlist.item icon="chart-bar" :href="route('dashboard.analytics.index')" :current="request()->routeIs('dashboard.analytics.*')" wire:navigate>{{ __('Analytics') }}</flux:navlist.item>
                    <flux:navlist.item icon="document-chart-bar" :href="route('dashboard.reports.index')" :current="request()->routeIs('dashboard.reports.*')" wire:navigate>{{ __('Reports') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Account')" class="grid">
                     <flux:navlist.item icon="document-text" :href="route('dashboard.documents.index')" :current="request()->routeIs('dashboard.documents.*')" wire:navigate>{{ __('Documents') }}</flux:navlist.item>
                     <flux:navlist.item icon="lifebuoy" :href="route('dashboard.support.index')" :current="request()->routeIs('dashboard.support.*')" wire:navigate>{{ __('Support') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <!-- Desktop User Menu -->
            @if($user)
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="$user->name"
                    :initials="$user->initials()"
                    icon:trailing="chevrons-up-down"
                    data-test="sidebar-menu-button"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ $user->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ $user->name }}</span>
                                    <span class="truncate text-xs">{{ $user->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
            <flux:header class="lg:hidden">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

                <flux:spacer />

                @if($user)
                <flux:dropdown position="top" align="end">
                    <flux:profile
                        :initials="$user->initials()"
                        icon-trailing="chevron-down"
                    />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ $user->name }}</span>
                                    <span class="truncate text-xs">{{ $user->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
                </flux:dropdown>
                @endif
            </flux:header>
            @endif

        {{ $slot }}

        @fluxScripts
    </body>
</html>
