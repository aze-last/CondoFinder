<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Owner Management')]);

new class extends Component {
    use WithPagination;

    public $search = '';
    public $name = '';
    public $email = '';
    public $password = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function save()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => \Illuminate\Support\Facades\Hash::make($this->password),
            'is_active' => true,
        ]);

        $user->assignRole('Owner');

        $this->reset(['name', 'email', 'password']);
        $this->dispatch('close-modal', name: 'create-owner');
        
        Flux::toast(__('New owner account created successfully.'));
    }

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['is_active' => !$user->is_active]);

        Flux::toast(__('Owner status updated successfully.'));
    }

    public function with()
    {
        return [
            'owners' => User::role('Owner')
                ->where(function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%')
                          ->orWhere('slug', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10),
        ];
    }
} ?><div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-neutral-100">{{ __('Owner Management') }}</h1>
                <p class="text-sm text-neutral-400">{{ __('View and manage all platform owners and their accounts.') }}</p>
            </div>
            
            <flux:modal.trigger name="create-owner">
                <flux:button variant="primary" icon="user-plus">{{ __('New Owner') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:modal name="create-owner" class="md:w-[450px]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Create New Owner') }}</flux:heading>
                    <flux:subheading>{{ __('Manually onboard a new property owner to the platform.') }}</flux:subheading>
                </div>

                <div class="space-y-4">
                    <flux:input wire:model="name" label="{{ __('Full Name') }}" placeholder="{{ __('John Doe') }}" />
                    <flux:input wire:model="email" type="email" label="{{ __('Email Address') }}" placeholder="{{ __('john@example.com') }}" />
                    <flux:input wire:model="password" type="password" label="{{ __('Password') }}" placeholder="{{ __('Min. 8 characters') }}" />
                </div>

                <div class="flex gap-2 justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" wire:click="save">{{ __('Create Account') }}</flux:button>
                </div>
            </div>
        </flux:modal>

        <div class="flex items-center gap-4">
            <div class="relative flex-1">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    icon="magnifying-glass" 
                    placeholder="{{ __('Search owners by name, email, or slug...') }}" 
                />
            </div>
        </div>

        <div class="rounded-xl border border-neutral-800 bg-neutral-900/70 shadow-lg backdrop-blur overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-neutral-400">
                    <thead class="border-b border-neutral-800 text-xs uppercase text-neutral-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">{{ __('Name') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Email') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Listings') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Status') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Joined') }}</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-800">
                        @forelse ($owners as $owner)
                            <tr class="hover:bg-neutral-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <flux:profile :initials="$owner->initials()" size="sm" />
                                        <span class="font-medium text-neutral-100">{{ $owner->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-neutral-400">{{ $owner->email }}</td>
                                <td class="px-6 py-4">
                                    <flux:badge variant="ghost">{{ $owner->listings_count ?? $owner->listings()->count() }}</flux:badge>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:badge :color="$owner->is_active ? 'emerald' : 'red'">
                                        {{ $owner->is_active ? __('Active') : __('Suspended') }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4 text-neutral-500 text-xs">{{ $owner->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <flux:dropdown>
                                        <flux:button variant="ghost" icon="ellipsis-horizontal" size="xs" />
                                        <flux:menu>
                                            <flux:menu.item icon="pencil">{{ __('Edit Details') }}</flux:menu.item>
                                            <flux:menu.item icon="finger-print">{{ __('Verify Identity') }}</flux:menu.item>
                                            <flux:menu.item icon="arrow-path">{{ __('Reset Password') }}</flux:menu.item>
                                            <flux:menu.separator />
                                            <flux:menu.item 
                                                wire:click="toggleStatus({{ $owner->id }})" 
                                                :icon="$owner->is_active ? 'pause' : 'play'"
                                                :variant="$owner->is_active ? 'danger' : null"
                                            >
                                                {{ $owner->is_active ? __('Suspend Account') : __('Reactivate Account') }}
                                            </flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-neutral-500 italic">
                                    {{ __('No owners found matching your search.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($owners->hasPages())
                <div class="p-4 border-t border-neutral-800">
                    {{ $owners->links() }}
                </div>
            @endif
        </div>
    </div>
