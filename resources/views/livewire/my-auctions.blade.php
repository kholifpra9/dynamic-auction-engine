<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    public function getListingsProperty()
    {
        return auth()->user()->listings()->with('category')->latest()->get();
    }
}; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Lelang Saya</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola dan pantau semua item lelang yang telah Anda publikasikan.</p>
        </div>
        
        <a href="{{ route('listings.create') }}" 
           wire:navigate 
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Buat Listing Baru</span>
        </a>
    </div>

    {{-- Listings Grid / Empty State --}}
    @forelse ($this->listings as $listing)
        @if ($loop->first)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @endif

        <div class="group relative bg-white rounded-2xl border border-gray-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden">
            
            {{-- Image & Badges Container --}}
            <div class="relative w-full h-52 bg-gray-100 overflow-hidden">
                @if ($listing->photo_path && Storage::disk('public')->exists($listing->photo_path))
                    <img src="{{ Storage::url($listing->photo_path) }}" 
                         alt="{{ $listing->title }}" 
                         onerror="this.onerror=null; this.remove(); this.nextElementSibling.classList.remove('hidden');"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    
                    {{-- Hidden Placeholder Fallback (Triggered by JS OnError) --}}
                    <div class="hidden w-full h-full flex-col items-center justify-center bg-gray-100 text-gray-400">
                        <svg class="w-12 h-12 mb-1 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span class="text-xs font-medium">Gambar Tidak Tersedia</span>
                    </div>
                @else
                    {{-- Fallback jika photo_path null / kosong --}}
                    <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 text-gray-400">
                        <svg class="w-12 h-12 mb-1 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span class="text-xs font-medium text-gray-400">Gambar Tidak Tersedia</span>
                    </div>
                @endif
                
                {{-- Category Badge (Top Left) --}}
                <div class="absolute top-3 left-3 z-10">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/90 backdrop-blur-md text-gray-800 shadow-sm border border-white/20">
                        {{ $listing->category->name }}
                    </span>
                </div>

                {{-- Status Badge (Top Right) --}}
                <div class="absolute top-3 right-3 z-10">
                    @if ($listing->isCurrentlyActive())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/90 backdrop-blur-md text-white shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-900/75 backdrop-blur-md text-white shadow-sm">
                            Selesai
                        </span>
                    @endif
                </div>
            </div>

            {{-- Card Body --}}
            <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                <div>
                    {{-- Title --}}
                    <h2 class="text-base font-bold text-gray-900 line-clamp-1 group-hover:text-indigo-600 transition-colors">
                        <a href="{{ route('listings.detail', $listing) }}" wire:navigate class="focus:outline-none">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            {{ $listing->title }}
                        </a>
                    </h2>

                    {{-- Spec / Time Info --}}
                    <p class="text-xs text-gray-400 mt-1">
                        Dibuat {{ $listing->created_at->diffForHumans() }}
                    </p>
                </div>

                {{-- Price & Action Footer --}}
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between relative z-10">
                    <div>
                        <span class="text-[11px] uppercase tracking-wider text-gray-400 block font-semibold">Harga Saat Ini</span>
                        <span class="text-lg font-extrabold text-indigo-600">
                            Rp {{ number_format($listing->current_price, 0, ',', '.') }}
                        </span>
                    </div>

                    <a href="{{ route('listings.detail', $listing) }}" 
                       wire:navigate
                       class="inline-flex items-center gap-1 text-sm font-semibold text-gray-700 group-hover:text-indigo-600 transition-colors">
                        Detail 
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        @if ($loop->last)
            </div>
        @endif
    @empty
        {{-- Empty State --}}
        <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center max-w-xl mx-auto my-8">
            <div class="w-16 h-16 mx-auto mb-4 text-indigo-500 bg-indigo-50 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Lelang</h3>
            <p class="text-sm text-gray-500 mb-6">Anda belum membuat barang atau hewan peliharaan untuk dilelang. Mulai lelang pertama Anda sekarang!</p>
            <a href="{{ route('listings.create') }}" 
               wire:navigate 
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Buat Listing Baru</span>
            </a>
        </div>
    @endforelse
</div>