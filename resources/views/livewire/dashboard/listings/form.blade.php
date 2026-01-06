<?php

use App\Models\Listing;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ?Listing $listing = null;

    public string $title = '';
    public string $description = '';
    public string $price_per_night = '';
    public string $location_text = '';
    public ?string $latitude = null;
    public ?string $longitude = null;
    public string $status = 'AVAILABLE';
    public array $selectedCategories = [];
    public $images = [];

    public function mount(?Listing $listing = null): void
    {
        if ($listing && $listing->exists) {
            $this->listing = $listing;
            $this->title = $listing->title;
            $this->description = $listing->description;
            $this->price_per_night = $listing->price_per_night;
            $this->location_text = $listing->location_text;
            $this->latitude = $listing->latitude;
            $this->longitude = $listing->longitude;
            $this->status = $listing->status;
            $this->selectedCategories = $listing->categories->pluck('id')->toArray();
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'location_text' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', 'in:AVAILABLE,UNAVAILABLE'],
            'selectedCategories' => ['array'],
            'images.*' => ['image', 'max:10240'], // 10MB max
        ]);

        $data = collect($validated)->except(['selectedCategories', 'images'])->toArray();

        if ($this->listing) {
            $this->listing->update($data);
            $listing = $this->listing;
        } else {
            $listing = Listing::create([
                ...$data,
                'owner_id' => Auth::id(),
            ]);
        }

        $listing->categories()->sync($this->selectedCategories);

        if ($this->images) {
            foreach ($this->images as $image) {
                $listing->addMedia($image->getRealPath())
                    ->usingFileName($image->getClientOriginalName())
                    ->toMediaCollection('listings');
            }
        }

        session()->flash('status', $this->listing ? __('Listing updated successfully.') : __('Listing created successfully.'));

        $this->redirectRoute('dashboard.listings.index');
    }

    public function deleteMedia($mediaId): void
    {
        $media = $this->listing->media()->find($mediaId);
        if ($media) {
            $media->delete();
            $this->listing->refresh();
        }
    }

    public function with(): array
    {
        return [
            'categories' => Category::where('owner_id', Auth::id())->get(),
        ];
    }
}; ?>

<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-neutral-100">{{ $listing ? __('Edit listing') : __('Add listing') }}</h1>
            <p class="text-sm text-neutral-400">{{ __('Publish a new unit with rate and location details.') }}</p>
        </div>
        <flux:button variant="ghost" href="{{ route('dashboard.listings.index') }}" wire:navigate>
            {{ __('Back to listings') }}
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-emerald-700/50 bg-emerald-900/30 px-4 py-3 text-sm text-emerald-100">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6 rounded-xl border border-neutral-800/80 bg-neutral-900/70 p-6 shadow-lg shadow-black/20">
        <div class="grid gap-6 md:grid-cols-2">
            <flux:input wire:model="title" :label="__('Title')" required placeholder="e.g. Modern Studio at Mactan" />
            
            <flux:select wire:model="status" :label="__('Status')">
                <flux:select.option value="AVAILABLE">{{ __('Available') }}</flux:select.option>
                <flux:select.option value="UNAVAILABLE">{{ __('Unavailable') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="space-y-2">
            <flux:label>{{ __('Categories') }}</flux:label>
            <div class="flex flex-wrap gap-3">
                @foreach($categories as $category)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}" class="rounded border-neutral-700 bg-neutral-800 text-primary-500 focus:ring-primary-500">
                        <span class="text-sm text-neutral-300">{{ $category->name }}</span>
                    </label>
                @endforeach
            </div>
            @if($categories->isEmpty())
                <p class="text-xs text-neutral-500">{{ __('No categories found. Create some first.') }}</p>
            @endif
        </div>

        <flux:textarea wire:model="description" :label="__('Description')" rows="4" required placeholder="Describe the amenities, view, and nearby spots..." />

        <div class="grid gap-6 md:grid-cols-3">
            <flux:input wire:model="price_per_night" :label="__('Price per night')" type="number" step="0.01" min="0" required prefix="$" />
            <flux:input wire:model="latitude" :label="__('Latitude')" type="number" step="0.000001" placeholder="Optional" />
            <flux:input wire:model="longitude" :label="__('Longitude')" type="number" step="0.000001" placeholder="Optional" />
        </div>

        <flux:input wire:model="location_text" :label="__('Location address')" required placeholder="e.g. 123 Condo St, Cebu City" />

        <div class="space-y-2">
            <flux:label>{{ __('Photos') }}</flux:label>
            <div class="flex items-center justify-center w-full">
                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer border-neutral-700 bg-neutral-800/30 hover:bg-neutral-800/50">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <flux:icon.photo class="w-8 h-8 mb-2 text-neutral-500" />
                        <p class="mb-2 text-sm text-neutral-400 font-semibold">{{ __('Click to upload photos') }}</p>
                        <p class="text-xs text-neutral-500">PNG, JPG or WEBP (Max 10MB)</p>
                    </div>
                <input type="file" wire:model="images" multiple class="hidden" accept="image/*" />
                </label>
            </div>
            
            <div wire:loading wire:target="images" class="text-xs text-primary-400">
                {{ __('Uploading...') }}
            </div>

            @if($images)
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
                    @foreach($images as $image)
                        <div class="relative group aspect-video overflow-hidden rounded-xl border border-neutral-800 bg-black/20">
                            <img src="{{ $image->temporaryUrl() }}" class="h-full w-full object-cover transition-transform group-hover:scale-105">
                            <div class="absolute inset-x-0 bottom-0 bg-neutral-950/60 p-1 text-[8px] font-black text-center text-white uppercase tracking-widest">
                                {{ __('Pending upload') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($listing && $listing->getMedia('listings')->isNotEmpty())
                <div class="mt-8">
                    <flux:label class="mb-2">{{ __('Current photos') }}</flux:label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach($listing->getMedia('listings') as $media)
                            <div class="relative group aspect-video overflow-hidden rounded-xl border border-neutral-800 bg-neutral-950 shadow-inner">
                                <img src="{{ $media->getUrl() }}" class="h-full w-full object-contain">
                                <button type="button" wire:click="deleteMedia({{ $media->id }})" class="absolute top-2 right-2 p-1.5 bg-red-600/90 hover:bg-red-600 rounded-full text-white shadow-lg transition-all active:scale-90">
                                    <flux:icon.trash class="w-3.5 h-3.5" />
                                </button>
                                <div class="absolute inset-x-0 bottom-0 bg-neutral-950/40 p-1 text-[8px] font-black text-center text-white/60 uppercase tracking-widest">
                                    {{ $media->human_readable_size }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-neutral-800">
            <flux:button type="submit" variant="primary" class="px-8">
                {{ $listing ? __('Update listing') : __('Publish listing') }}
            </flux:button>
            <flux:button variant="ghost" type="button" href="{{ route('dashboard.listings.index') }}" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</section>
