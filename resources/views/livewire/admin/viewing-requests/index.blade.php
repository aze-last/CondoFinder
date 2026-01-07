<?php

use App\Models\ViewingRequest;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Global Viewing Requests')]);

new class extends Component {
    use WithPagination;

    public $search = '';

    public function updatingSearch() { $this->resetPage(); }

    public function with()
    {
        return [
            'requests' => ViewingRequest::with(['listing', 'owner'])
                ->where(function($query) {
                    $query->where('customer_name', 'like', '%' . $this->search . '%')
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
                <h1 class="text-2xl font-semibold text-neutral-100">{{ __('Viewing Request Monitoring') }}</h1>
                <p class="text-sm text-neutral-400">{{ __('Monitor all viewing requests and their current statuses across the platform.') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="relative flex-1">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    icon="magnifying-glass" 
                    placeholder="{{ __('Search by customer name or listing title...') }}" 
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
                            <th class="px-6 py-4 font-medium">{{ __('Preferred Date') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Status') }}</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-800">
                        @forelse ($requests as $request)
                            <tr class="hover:bg-neutral-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-neutral-100">{{ $request->customer_name }}</span>
                                        <span class="text-xs text-neutral-500">{{ $request->customer_email }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm text-neutral-300 font-medium">{{ $request->listing?->title ?? __('Deleted Listing') }}</span>
                                        <span class="text-[10px] text-neutral-500 italic">{{ __('Owner:') }} {{ $request->owner?->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-neutral-400 text-sm whitespace-nowrap">
                                    {{ $request->preferred_datetime->format('M d, Y h:i A') }}
                                </td>
                                <td class="px-6 py-4">
                                    <flux:badge :color="match($request->status) {
                                        'PENDING' => 'amber',
                                        'APPROVED' => 'emerald',
                                        'REJECTED' => 'red',
                                        'COMPLETED' => 'blue',
                                        default => 'zinc'
                                    }">
                                        {{ $request->status }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:button variant="ghost" icon="eye" size="xs" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-neutral-500 italic">
                                    {{ __('No viewing requests found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($requests->hasPages())
                <div class="p-4 border-t border-neutral-800">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
