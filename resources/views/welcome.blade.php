<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Condo Finder') }} - Condo Showroom SaaS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
@php
    $demoShowroomKey = config('app.demo_public_key', 'eea847b8-fd1e-49ac-b177-99bcf2458738');
    $demoShowroomUrl = \Illuminate\Support\Facades\Route::has('showroom.profile')
        ? route('showroom.profile', $demoShowroomKey)
        : url('/u/' . $demoShowroomKey);
@endphp
<body class="bg-white text-neutral-900 antialiased selection:bg-neutral-900 selection:text-white" x-data="{ 
    page: 'admin',
    query: '',
    activeCategory: 'all',
    sort: 'recommended',
    liked: {},
    categories: [
        { id: 'beach', label: 'Beach' },
        { id: 'villa', label: 'Villa' },
        { id: 'modern', label: 'Modern' },
        { id: 'budget', label: 'Budget' },
        { id: 'family', label: 'Family' },
        { id: 'mall', label: 'Near Mall' },
        { id: 'sea', label: 'Sea View' },
        { id: 'lux', label: 'Lux' },
        { id: 'studio', label: 'Studio' },
        { id: '2br', label: '2BR' }
    ],
    demoListings: [
        { id: 'l1', title: 'Condo in Kasambagan', area: 'Cebu City', price: 3738, rating: 4.99, reviews: 128, tag: 'Guest favorite', categoryIds: ['modern', 'mall', 'studio'], image: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=800&q=80' },
        { id: 'l2', title: 'Apartment in Lahug', area: 'Cebu City', price: 4072, rating: 4.89, reviews: 96, tag: 'Guest favorite', categoryIds: ['budget', 'mall', 'studio'], image: 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=800&q=80' },
        { id: 'l3', title: 'Condo with Pool View', area: 'Cebu City', price: 4350, rating: 4.79, reviews: 212, tag: 'Guest favorite', categoryIds: ['lux', 'family', 'sea'], image: 'https://images.unsplash.com/photo-1560185127-6a8c0f8d2c10?auto=format&fit=crop&w=800&q=80' },
        { id: 'l4', title: 'Apartment in Apas', area: 'Cebu City', price: 2386, rating: 4.82, reviews: 64, tag: 'Guest favorite', categoryIds: ['budget', 'family'], image: 'https://images.unsplash.com/photo-1502005229762-cf1b2da7c5d6?auto=format&fit=crop&w=800&q=80' },
        { id: 'l5', title: 'Condo Near IT Park', area: 'Cebu City', price: 3995, rating: 4.82, reviews: 44, tag: 'Guest favorite', categoryIds: ['modern', 'mall'], image: 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=800&q=80' },
        { id: 'l6', title: 'Place to stay in Cebu', area: 'Cebu City', price: 3227, rating: 5.0, reviews: 18, tag: 'Guest favorite', categoryIds: ['family', '2br'], image: 'https://images.unsplash.com/photo-1540518614846-7eded433c457?auto=format&fit=crop&w=800&q=80' }
    ],
    get filteredListings() {
        let items = this.demoListings.filter(l => {
            const matchesQ = !this.query || l.title.toLowerCase().includes(this.query.toLowerCase()) || l.area.toLowerCase().includes(this.query.toLowerCase());
            const matchesCat = this.activeCategory === 'all' || l.categoryIds.includes(this.activeCategory);
            return matchesQ && matchesCat;
        });
        if (this.sort === 'price_low') items = [...items].sort((a, b) => a.price - b.price);
        if (this.sort === 'price_high') items = [...items].sort((a, b) => b.price - a.price);
        if (this.sort === 'rating') items = [...items].sort((a, b) => b.rating - a.rating);
        return items;
    },
    formatPeso(n) {
        return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }).format(n);
    }
}">
    <div class="min-h-screen">
        <!-- Admin/Public preview toggle -->
        <div class="fixed top-4 right-4 z-50">
            <div class="rounded-full bg-white/90 backdrop-blur shadow-xl border border-neutral-100 px-2 py-1 flex items-center gap-1">
                <button @click="page = 'admin'" :class="page === 'admin' ? 'bg-neutral-900 text-white' : 'text-neutral-500 hover:text-neutral-900'" class="px-3 py-2 rounded-full text-[10px] font-black uppercase tracking-widest transition">
                    {{ __('Admin onboarding') }}
                </button>
                <button @click="page = 'public'" :class="page === 'public' ? 'bg-neutral-900 text-white' : 'text-neutral-500 hover:text-neutral-900'" class="px-3 py-2 rounded-full text-[10px] font-black uppercase tracking-widest transition">
                    {{ __('Preview site') }}
                </button>
            </div>
        </div>

        <!-- Admin Onboarding View -->
        <div x-show="page === 'admin'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <!-- Header -->
            <header class="border-b bg-white/80 backdrop-blur-md">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-neutral-900 font-black text-white">CF</div>
                        <div>
                            <div class="text-sm font-black tracking-tighter">{{ __('Condo Finder') }}</div>
                            <div class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">{{ __('Owner onboarding') }}</div>
                        </div>
                    </div>

                    <nav class="hidden md:flex items-center gap-6 flex-1 justify-center z-10 text-center relative">
                        <div class="absolute inset-0 -z-10"></div>
                        <a href="#features" class="text-xs font-black uppercase tracking-widest text-neutral-500 hover:text-neutral-900">{{ __('Features') }}</a>
                        <a href="#pricing" class="text-xs font-black uppercase tracking-widest text-neutral-500 hover:text-neutral-900">{{ __('Pricing') }}</a>
                        <a href="#" class="text-xs font-black uppercase tracking-widest text-neutral-500 hover:text-neutral-900">{{ __('Support') }}</a>
                    </nav>



                </div>
            </header>

            <!-- Hero Section -->
            <section class="mx-auto max-w-7xl px-4 pt-16 pb-12 sm:px-6 lg:px-8">
                <div class="grid items-center gap-16 lg:grid-cols-2">
                    <div>
                        <flux:badge variant="secondary" class="rounded-full bg-neutral-100 px-4 py-1.5 font-black text-[10px] uppercase tracking-[0.2em] text-neutral-500">
                            {{ __('Built for condo owners & agents') }}
                        </flux:badge>
                        <h1 class="mt-8 text-5xl font-black tracking-tighter text-neutral-900 sm:text-7xl leading-[0.9]">
                            {{ __('Your condo showroom,') }}
                            <span class="block text-neutral-400 underline decoration-neutral-100 decoration-8 underline-offset-8">{{ __('ready in minutes.') }}</span>
                        </h1>
                        <p class="mt-8 text-lg font-medium leading-relaxed text-neutral-500 max-w-xl">
                            {{ __('List units, receive inquiries, and schedule viewings—without complicated booking systems. Customers browse publicly. Owners manage everything secure.') }}
                        </p>

                        <div class="mt-10 flex flex-wrap gap-4">
                            <a href="{{ route('login') }}" class="relative inline-flex items-center justify-center group rounded-full">
                                <span aria-hidden class="pointer-events-none absolute inset-[-3px] rounded-full opacity-0 transition duration-300 group-hover:opacity-100" style="background: linear-gradient(90deg, #60a5fa, #a78bfa, #34d399);"></span>
                                <span class="relative inline-flex items-center justify-center h-14 px-8 rounded-full bg-neutral-900 text-white text-lg font-black shadow-xl transition-transform active:scale-95 hover:bg-neutral-800">
                                    {{ __('Login to Dashboard') }}
                                </span>
                            </a>
                            <a href="{{ url('/u/eea847b8-fd1e-49ac-b177-99bcf2458738') }}" target="_blank" class="h-14 px-8 flex items-center justify-center rounded-full bg-white border-2 border-neutral-200 text-neutral-900 text-lg font-black transition-all hover:bg-neutral-50 active:scale-95">
                                <flux:icon.play-circle class="mr-2 h-5 w-5" />
                                {{ __('Watch live demo') }}
                            </a>
                        </div>

                        <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-3">
                            @foreach(['No customer login', 'No payments', 'Fast inquiries'] as $text)
                                <div class="flex items-center gap-3">
                                    <div class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                        <flux:icon.check class="h-3.5 w-3.5" />
                                    </div>
                                    <span class="text-[11px] font-black text-neutral-600 uppercase tracking-widest">{{ __($text) }}</span>
                                </div>
                            @endforeach
                        </div>

                        
                    </div>

                    <!-- Dashboard Preview Mockup -->
                    <div class="relative lg:-mr-16">
                        <div class="overflow-hidden rounded-[3rem] border-8 border-neutral-900 bg-white shadow-2xl transition-all hover:scale-[1.02] duration-700">
                            <div class="border-b bg-neutral-50/50 p-6 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-neutral-900 text-white grid place-items-center font-black">CF</div>
                                    <div>
                                        <div class="text-xs font-black uppercase tracking-tighter">{{ __('Owner Dashboard') }}</div>
                                        <div class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">{{ __('Portfolio overview') }}</div>
                                    </div>
                                </div>
                                <flux:button variant="primary" size="sm" class="rounded-full px-4 font-black text-[10px] uppercase">+ {{ __('Listing') }}</flux:button>
                            </div>
                            <div class="p-6 grid grid-cols-2 gap-4">
                                @foreach([['Active Listings', '5'], ['New Inquiries', '12'], ['Pending Viewings', '3'], ['Total Views', '421']] as [$label, $value])
                                    <div class="rounded-3xl border-2 border-neutral-50 bg-white p-5 hover:shadow-lg transition-all cursor-default">
                                        <div class="text-[9px] font-black text-neutral-400 uppercase tracking-widest">{{ __($label) }}</div>
                                        <div class="mt-2 text-3xl font-black text-neutral-900 tracking-tighter">{{ $value }}</div>
                                        <div class="mt-1 text-[8px] font-black text-neutral-300 uppercase tracking-widest">{{ __('Last 7 days') }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="px-6 pb-6 text-neutral-900">
                                <div class="rounded-[2.5rem] border-2 border-neutral-50 p-6 bg-neutral-50/50">
                                    <div class="flex items-center justify-between border-b border-neutral-100 pb-4">
                                        <div class="text-[10px] font-black uppercase tracking-widest">{{ __('Recent Inquiries') }}</div>
                                        <flux:badge variant="primary" class="rounded-full px-2 py-0.5 font-black text-[9px] uppercase">{{ __('3 NEW') }}</flux:badge>
                                    </div>
                                    <div class="mt-5 space-y-3">
                                        @foreach(['Maria', 'Jared', 'Cian'] as $name)
                                            <div class="flex items-center justify-between gap-4 rounded-2xl bg-white border border-neutral-100 p-3 hover:shadow-md transition cursor-pointer">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-8 w-8 rounded-full bg-neutral-900 font-black text-white grid place-items-center text-[10px]">{{ substr($name, 0, 1) }}</div>
                                                    <div>
                                                        <div class="text-xs font-black">{{ $name }}</div>
                                                        <div class="text-[9px] font-bold text-neutral-400 truncate max-w-[120px]">{{ __('2BR near IT Park inquiry') }}</div>
                                                    </div>
                                                </div>
                                                <flux:button variant="ghost" size="xs" class="rounded-full font-black text-[9px] uppercase border border-neutral-100">{{ __('Reply') }}</flux:button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Badge -->
                    </div>
                </div>
            </section>

            <!-- Features Grid -->
            <section id="features" class="bg-neutral-50 px-4 py-24 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                        <div>
                            <h2 class="text-4xl font-black tracking-tighter">{{ __('Everything owners need') }}</h2>
                            <p class="mt-4 text-lg font-medium text-neutral-500 leading-relaxed">{{ __('Designed for inquiries, viewings, and fast conversions.') }}</p>
                        </div>
                        <flux:badge variant="neutral" class="rounded-full bg-white px-5 py-2 border-2 border-neutral-200 font-black text-xs uppercase tracking-widest text-neutral-500 shadow-sm">
                            {{ __('No customer login required') }}
                        </flux:badge>
                    </div>

                    <div class="mt-16 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        @php $features = [
                            ['Owner dashboard', 'Manage listings, categories, inquiries, and viewing requests.', 'layout-grid'],
                            ['Public showroom', 'Airbnb-style browsing for customers—no login, no checkout.', 'sparkles'],
                            ['Secure by default', 'Ownership checks, validation, rate limiting, and safe uploads.', 'shield-check'],
                            ['Smart notifications', 'Email + in-app alerts for inquiries and scheduled viewings.', 'bell']
                        ]; @endphp
                        @foreach($features as [$title, $desc, $icon])
                            <div class="group h-full rounded-[3rem] border border-neutral-100 bg-white p-8 transition-all duration-300 hover:border-neutral-900 hover:shadow-2xl hover:-translate-y-2">
                                <div class="h-14 w-14 items-center justify-center rounded-2xl bg-neutral-50 flex transition-colors group-hover:bg-neutral-900 group-hover:text-white">
                                    <flux:icon :name="$icon" class="h-7 w-7" />
                                </div>
                                <h3 class="mt-8 text-xl font-black tracking-tight uppercase tracking-tighter">{{ __($title) }}</h3>
                                <p class="mt-3 text-sm font-semibold leading-relaxed text-neutral-400 group-hover:text-neutral-600 transition">
                                    {{ __($desc) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- Pricing Section -->
            <section id="pricing" class="px-4 py-24 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16">
                        <div>
                            <h2 class="text-4xl font-black tracking-tighter leading-none">{{ __('Simple, scalable pricing') }}</h2>
                            <p class="mt-4 text-lg font-medium text-neutral-500">{{ __('List your properties. Upgrade when you grow.') }}</p>
                        </div>
                        <flux:badge variant="success" class="rounded-full px-5 py-2 font-black text-xs uppercase tracking-widest shadow-sm">
                            {{ __('Cancel anytime') }}
                        </flux:badge>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3 items-center">
                        @php $plans = [
                            ['Starter', '₱799/mo', 'Best for solo owners', ['Up to 10 listings', 'Basic analytics', 'Email notifications'], false],
                            ['Pro', '₱1,499/mo', 'Best for agents', ['Up to 50 listings', 'Viewing calendar', 'Priority support', 'Lead notes'], true],
                            ['Business', '₱2,999/mo', 'Teams & brokers', ['Unlimited listings', 'Team members', 'Advanced analytics', 'Custom domain'], false]
                        ]; @endphp
                        @foreach($plans as [$name, $price, $note, $plist, $featured])
                            <div @class([
                                'relative flex flex-col rounded-[3.5rem] p-10 transition-all duration-500 group',
                                'bg-neutral-900 text-white shadow-2xl scale-105 z-10' => $featured,
                                'bg-white text-neutral-900 border-2 border-neutral-100 hover:border-neutral-200' => !$featured
                            ])>
                                @if($featured)
                                    <div class="absolute -top-5 inset-x-0 flex justify-center">
                                        <flux:badge variant="success" class="rounded-full px-4 py-1.5 font-black text-[10px] uppercase tracking-widest shadow-xl ring-4 ring-neutral-900">{{ __('MOST POPULAR') }}</flux:badge>
                                    </div>
                                @endif
                                <div class="text-2xl font-black tracking-tighter uppercase">{{ __($name) }}</div>
                                <div @class(['mt-1 text-xs font-bold uppercase tracking-widest', 'text-white/40' => $featured, 'text-neutral-400' => !$featured])>{{ __($note) }}</div>
                                <div class="mt-10 flex items-baseline gap-1">
                                    <span class="text-5xl font-black tracking-tighter">{{ $price }}</span>
                                    <span @class(['text-sm font-bold uppercase tracking-widest opacity-40', 'text-white' => $featured, 'text-neutral-900' => !$featured])>/mo</span>
                                </div>
                                <ul class="mt-10 flex-1 space-y-4">
                                    @foreach($plist as $item)
                                        <li class="flex items-center gap-3 text-sm font-bold tracking-tight">
                                            <flux:icon.check class="h-4 w-4 text-emerald-500" />
                                            <span @class(['text-white/80' => $featured, 'text-neutral-600' => !$featured])>{{ __($item) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <flux:button :variant="$featured ? 'primary' : 'outline'" class="mt-12 rounded-[2rem] h-14 font-black text-sm uppercase tracking-widest transition-transform active:scale-95 group-hover:shadow-xl">
                                    {{ __('Choose :name', ['name' => $name]) }}
                                </flux:button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- Security Footer Note -->
            <section class="mx-auto max-w-7xl px-4 pb-24">
                <div class="rounded-[3rem] border border-neutral-100 p-8 md:p-12 bg-neutral-50/30">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
                        <div class="flex items-start gap-5">
                            <div class="h-16 w-16 rounded-3xl bg-neutral-900 text-white grid place-items-center shadow-xl shadow-neutral-200 shrink-0">
                                <flux:icon.lock-closed class="h-8 w-8" />
                            </div>
                            <div>
                                <h3 class="text-xl font-black uppercase tracking-tighter leading-none">{{ __('Security-first onboarding') }}</h3>
                                <p class="mt-3 text-sm font-semibold text-neutral-400 max-w-2xl leading-relaxed">
                                    {{ __('We strictly implement ownership checks. API keys for Google Maps and notifications are handled securely on the server-side, never exposed to visitors.') }}
                                </p>
                            </div>
                        </div>
                        <flux:button variant="outline" class="rounded-full px-8 py-3 font-black text-xs uppercase tracking-widest border-2 border-neutral-200">{{ __('Learn more') }}</flux:button>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="border-t bg-neutral-950 px-4 py-16 text-white sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl flex flex-col md:flex-row items-center justify-between gap-12">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-white text-neutral-900 grid place-items-center font-black">CF</div>
                            <div class="text-xs font-black uppercase tracking-widest"><span class="opacity-40 font-medium">Condo Finder —</span> {{ __('Condo showroom SaaS') }}</div>
                        </div>
                    <div class="flex flex-wrap justify-center gap-8 text-[10px] font-black uppercase tracking-[0.2em] text-neutral-500">
                        <a href="#" class="hover:text-white transition">{{ __('Terms') }}</a>
                        <a href="#" class="hover:text-white transition">{{ __('Privacy') }}</a>
                        <a href="#" class="hover:text-white transition">{{ __('Support') }}</a>
                        <a href="#" class="hover:text-white transition">{{ __('Twitter') }}</a>
                    </div>
                </div>
            </footer>
        </div>

        <!-- Public Showroom View -->
        <div x-show="page === 'public'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <header class="border-b bg-white/80 backdrop-blur-md">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-neutral-900 font-black text-white">CF</div>
                        <div>
                            <div class="text-sm font-black tracking-tighter">{{ __('Condo Finder') }}</div>
                            <div class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">{{ __('Live showroom demo') }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] text-neutral-500">
                        <span class="hidden sm:inline">{{ __('Owner demo public link') }}</span>
                        <a href="{{ $demoShowroomUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full bg-neutral-900 text-white px-4 py-2 hover:bg-neutral-800 transition">
                            <flux:icon.arrow-top-right-on-square class="h-3.5 w-3.5" />
                            {{ __('Open live') }}
                        </a>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4 flex-wrap mb-6">
                    <div>
                        <h2 class="text-2xl font-black tracking-tighter">{{ __('Real-time public preview') }}</h2>
                        <p class="text-sm font-semibold text-neutral-500">{{ __('This iframe shows the actual public showroom for the demo owner account.') }}</p>
                    </div>
                    <div class="text-xs font-semibold text-neutral-400 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        {{ __('Live connection') }}
                    </div>
                </div>

                <div class="rounded-3xl border border-neutral-200 shadow-2xl overflow-hidden bg-white">
                    <iframe src="{{ $demoShowroomUrl }}" class="w-full h-[80vh]" loading="lazy"></iframe>
                </div>
            </main>
        </div>
    </div>

    @fluxScripts
</body>
</html>
