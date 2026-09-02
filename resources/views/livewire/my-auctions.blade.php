<?php

use Livewire\Volt\Component;

new class extends Component {
    public function getListingsProperty()
    {
        return auth()->user()->listings()->with('category')->latest()->get();
    }
}; ?>

<div class="max-w-6xl mx-auto px-4 py-8">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Lelang Saya</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar semua item lelang yang telah Anda buat</p>
        </div>
        
        <a href="{{ route('listings.create') }}" 
           wire:navigate 
           class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
            + Buat Listing Baru
        </a>
    </div>

    {{-- Listings Grid / Empty State --}}
    @forelse ($this->listings as $listing)
        @if ($loop->first)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition flex flex-col overflow-hidden">
            {{-- Header Card Info --}}
            <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                <div>
                    {{-- Badges --}}
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                            {{ $listing->category->name }}
                        </span>

                        @if ($listing->status === 'active')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                Selesai
                            </span>
                        @endif
                    </div>

                    {{-- Title --}}
                    <h2 class="text-lg font-bold text-gray-900 line-clamp-1 hover:text-indigo-600 transition">
                        <a href="{{ route('listings.detail', $listing) }}" wire:navigate>
                            {{ $listing->title }}
                        </a>
                    </h2>
                </div>

                {{-- Price & Action --}}
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-gray-500 block font-medium">Harga Saat Ini</span>
                        <span class="text-lg font-extrabold text-gray-900">
                            Rp {{ number_format($listing->current_price, 0, ',', '.') }}
                        </span>
                    </div>

                    <a href="{{ route('listings.detail', $listing) }}" 
                       wire:navigate
                       class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                        Detail <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        @if ($loop->last)
            </div>
        @endif
    @empty
        {{-- Empty State --}}
        <div class="bg-white rounded-xl border-2 border-dashed border-gray-200 p-12 text-center">
            <div class="w-12 h-12 mx-auto mb-4 text-gray-400 bg-gray-50 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900 mb-1">Anda belum membuat listing</h3>
            <p class="text-sm text-gray-500 mb-6">Mulai buat lelang pertama Anda untuk menjual item atau hewan peliharaan.</p>
            <a href="{{ route('listings.create') }}" 
               wire:navigate 
               class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                + Buat Listing Baru
            </a>
        </div>
    @endforelse
</div>