<?php

use Livewire\Volt\Component;
use App\Models\Listing;

new class extends Component {
    public function getLatestListingsProperty()
    {
        return Listing::with(['category', 'seller'])
            ->where('status', 'active')
            ->latest()
            ->take(3)
            ->get();
    }
}; ?>

<div>
    {{-- Hero Section --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-900 via-indigo-800 to-slate-900 text-white rounded-3xl mb-12 shadow-xl">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/20 via-transparent to-transparent"></div>
        
        <div class="relative max-w-7xl mx-auto px-6 sm:px-8 py-12 sm:py-20 flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="max-w-2xl text-center md:text-left space-y-6">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-200 border border-indigo-400/30 backdrop-blur-md">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Platform Lelang Satwa #1 Terpercaya
                </span>

                <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                    Tawar & Dapatkan <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-indigo-200">
                        Satwa Impian Anda
                    </span>
                </h1>

                <p class="text-indigo-100/80 text-sm sm:text-base font-normal leading-relaxed">
                    Jelajahi lelang ikan hias dan burung berkualitas tinggi secara transparan, aman, dan real-time. Temukan koleksi langka dengan harga penawaran terbaik.
                </p>

                <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 pt-2">
                    <a href="{{ route('listings.index') }}" 
                       wire:navigate 
                       class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition-all duration-200 flex items-center gap-2">
                        <span>Jelajahi Lelang</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>

                    @auth
                        <a href="{{ route('listings.create') }}" 
                           wire:navigate 
                           class="px-6 py-3 bg-white/10 hover:bg-white/20 active:bg-white/30 text-white border border-white/20 font-bold text-sm rounded-xl backdrop-blur-md transition-all duration-200">
                            + Mulai Buat Lelang
                        </a>
                    @else
                        <a href="{{ route('register') }}" 
                           wire:navigate 
                           class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white border border-white/20 font-bold text-sm rounded-xl backdrop-blur-md transition-all duration-200">
                            Daftar Sekarang
                        </a>
                    @endauth
                </div>
            </div>

            <div class="shrink-0 w-full md:w-80 p-6 bg-white/10 backdrop-blur-xl border border-white/15 rounded-2xl shadow-2xl text-center space-y-4">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-500/30 flex items-center justify-center border border-indigo-400/30">
                    <svg class="w-8 h-8 text-sky-300 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Sistem Lelang Real-Time</h3>
                    <p class="text-xs text-indigo-200/80 mt-1">Penawaran harga langsung terpantau dengan kalkulasi transparan.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 3 Listings Terbaru --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 mb-12">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
            <div>
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Update Terkini</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mt-1">
                    Lelang Terbaru
                </h2>
            </div>

            <a href="{{ route('listings.index') }}" 
               wire:navigate 
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                <span>Lihat Semua Lelang</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </a>
        </div>

        {{-- Grid Listings --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($this->latestListings as $listing)
                <div class="group relative bg-white rounded-2xl border border-gray-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden">
                    
                    {{-- Image & Badges Container --}}
                    <div class="relative w-full h-52 bg-gray-100 overflow-hidden">
                        @if ($listing->photo_path && Storage::disk('public')->exists($listing->photo_path))
                            <img src="{{ asset('storage/' . $listing->photo_path) }}" 
                                 alt="{{ $listing->title }}" 
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center bg-gray-100 text-gray-400">
                                <svg class="w-10 h-10 mb-1 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                <span class="text-xs font-medium">Gambar Tidak Tersedia</span>
                            </div>
                        @endif
                        
                        {{-- Category Badge --}}
                        <div class="absolute top-3 left-3 z-10">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/90 backdrop-blur-md text-gray-800 shadow-sm border border-white/20">
                                {{ $listing->category->name }}
                            </span>
                        </div>

                        {{-- Status Badge --}}
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
                            <h3 class="text-base font-bold text-gray-900 line-clamp-1 group-hover:text-indigo-600 transition-colors">
                                <a href="{{ route('listings.detail', $listing) }}" wire:navigate class="focus:outline-none">
                                    <span class="absolute inset-0" aria-hidden="true"></span>
                                    {{ $listing->title }}
                                </a>
                            </h3>

                            <div class="flex items-center justify-between text-xs text-gray-400 mt-2">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $listing->seller->name ?? 'Penjual' }}
                                </span>
                                <span>{{ $listing->created_at->diffForHumans() }}</span>
                            </div>
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
            @empty
                <div class="col-span-full bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center max-w-xl mx-auto my-4">
                    <div class="w-16 h-16 mx-auto mb-4 text-indigo-500 bg-indigo-50 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Lelang Aktif</h3>
                    <p class="text-sm text-gray-500 mb-6">Jadilah yang pertama melelang satwa impian Anda!</p>
                    @auth
                        <a href="{{ route('listings.create') }}" 
                           wire:navigate 
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                            + Buat Lelang Baru
                        </a>
                    @endauth
                </div>
            @endforelse
        </div>
    </div>
</div>