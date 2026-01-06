<?php

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public ?Category $editing = null;

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,'.($this->editing?->id ?? 'NULL').',id,owner_id,'.Auth::id()],
        ]);

        if ($this->editing) {
            $this->editing->update(['name' => $this->name]);
            $this->editing = null;
        } else {
            Category::create([
                'name' => $this->name,
                'owner_id' => Auth::id(),
            ]);
        }

        $this->name = '';
        session()->flash('status', __('Category saved.'));
    }

    public function edit(Category $category): void
    {
        $this->editing = $category;
        $this->name = $category->name;
    }

    public function delete(Category $category): void
    {
        $category->delete();
        session()->flash('status', __('Category deleted.'));
    }

    public function with(): array
    {
        return [
            'categories' => Category::where('owner_id', Auth::id())->latest()->get(),
        ];
    }
}; ?>

<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-neutral-100">{{ __('Categories') }}</h1>
            <p class="text-sm text-neutral-400">{{ __('Organize your units (e.g. Studio, Penthouse, beachfront).') }}</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-emerald-700/50 bg-emerald-900/30 px-4 py-3 text-sm text-emerald-100 italic">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-3">
        <!-- Form Side -->
        <div class="lg:col-span-1">
            <form wire:submit="save" class="space-y-4 rounded-xl border border-neutral-800/80 bg-neutral-900/70 p-6 shadow-lg shadow-black/20">
                <flux:input wire:model="name" :label="$editing ? __('Edit Category') : __('Create Category')" placeholder="e.g. Luxury Suites" required />
                
                <div class="flex items-center gap-2">
                    <flux:button type="submit" variant="primary" size="sm">
                        {{ $editing ? __('Update') : __('Add') }}
                    </flux:button>
                    
                    @if($editing)
                        <flux:button wire:click="$set('editing', null); $set('name', '')" variant="ghost" size="sm">
                            {{ __('Cancel') }}
                        </flux:button>
                    @endif
                </div>
            </form>
        </div>

        <!-- List Side -->
        <div class="lg:col-span-2 overflow-hidden rounded-xl border border-neutral-800/80 bg-neutral-900/70 shadow-lg shadow-black/20">
            <table class="min-w-full divide-y divide-neutral-800">
                <thead class="bg-neutral-900/80">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Slug') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-neutral-800/40 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-100">{{ $category->name }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-500">{{ $category->slug }}</td>
                            <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="edit({{ $category->id }})" />
                                <flux:button variant="ghost" size="sm" icon="trash" wire:click="delete({{ $category->id }})" class="text-red-500 hover:text-red-400" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-sm text-neutral-500 italic">
                                {{ __('No categories defined yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
