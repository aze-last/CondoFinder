<?php

use App\Models\Inquiry;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Global Inquiries')]);

new class extends Component {
    use WithPagination;

    public $search = '';

    public function updatingSearch() { $this->resetPage(); }

    public function with()
    {
        return [
            'inquiries' => Inquiry::with(['listing', 'owner'])
                ->where(function($query) {
                    $query->where('customer_name', 'like', '%' . $this->search . '%')
                          ->orWhere('customer_email', 'like', '%' . $this->search . '%')
                          ->orWhereHas('listing', function($q) {
                              $q->where('title', 'like', '%' . $this->search . '%');
                          });
                })
                ->latest()
                ->paginate(15),
        ];
    }
} ?><div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-neutral-100">{{ __('Inquiry Monitoring') }}</h1>
                <p class="text-sm text-neutral-400">{{ __('Monitor all inquiries sent to owners across the platform.') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="relative flex-1">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    icon="magnifying-glass" 
                    placeholder="{{ __('Search by customer name, email, or listing title...') }}" 
                />
            </div>
        </div>

        <div class="rounded-xl border border-neutral-800 bg-neutral-900/70 shadow-lg backdrop-blur overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-neutral-400">
                    <thead class="border-b border-neutral-800 text-xs uppercase text-neutral-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">{{ __('Customer') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Listing / Owner') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Message') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Sent At') }}</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-800">
                        @forelse ($inquiries as $inquiry)
                            <tr class="hover:bg-neutral-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-neutral-100">{{ $inquiry->customer_name }}</span>
                                        <span class="text-xs text-neutral-500">{{ $inquiry->customer_email }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm text-neutral-300 font-medium">{{ $inquiry->listing?->title ?? __('Deleted Listing') }}</span>
                                        <span class="text-[10px] text-neutral-500 italic">{{ __('Owner:') }} {{ $inquiry->owner?->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    <p class="text-sm text-neutral-400 truncate">{{ $inquiry->message }}</p>
                                </td>
                                <td class="px-6 py-4 text-neutral-500 text-xs whitespace-nowrap">{{ $inquiry->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <flux:button variant="ghost" icon="eye" size="xs" />
                                    <flux:button variant="ghost" icon="shield-exclamation" size="xs" color="red" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-neutral-500 italic">
                                    {{ __('No inquiries found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($inquiries->hasPages())
                <div class="p-4 border-t border-neutral-800">
                    {{ $inquiries->links() }}
                </div>
            @endif
        </div>
    </div>
