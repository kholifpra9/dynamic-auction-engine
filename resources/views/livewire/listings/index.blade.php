<?php

use Livewire\Volt\Component;
use App\Models\Listing;
use App\Models\Category;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithPagination;

    public $categoryFilter = '';

    public function getCategoriesProperty()
    {
        return Category::all();
    }

    public function getListingsProperty()
    {
        return Listing::with(['category', 'seller'])
            ->when($this->categoryFilter, fn ($q) => $q->where('category_id', $this->categoryFilter))
            ->latest()
            ->paginate(9);
    }
}; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header & Filter Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Daftar Listing Lelang</h1>
            <p class="text-sm text-gray-500 mt-1">Jelajahi dan tawar hewan peliharaan & hias berkualitas terkini.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Category Filter Select --}}
            <div class="relative min-w-[200px]">
                <select wire:model.live="categoryFilter" 
                        class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 font-medium focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 appearance-none cursor-pointer">
                    <option value="">Semua Kategori</option>
                    @foreach ($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>

            @if (auth()->check())
                <a href="{{ route('listings.create') }}" 
                   wire:navigate
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Buat Lelang</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Listings Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @forelse ($this->listings as $listing)
            <div class="group relative bg-white rounded-2xl border border-gray-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden">
                
                {{-- Image & Badges Container --}}
                <div class="relative w-full h-52 bg-gray-100 overflow-hidden">
                    @if ($listing->photo_path && Storage::disk('public')->exists($listing->photo_path))
                        <img src="{{ asset('storage/' . $listing->photo_path) }}" 
                            alt="{{ $listing->title }}" 
                            loading="lazy"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        {{-- Fallback jika photo_path null / kosong --}}
                        <div class="w-full h-full flex flex-col items-center justify-center bg-gray-100 text-gray-400">
                            <svg class="w-10 h-10 mb-1 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <span class="text-xs font-medium">Gambar Tidak Tersedia</span>
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

                    {{-- FLOATING COUNTDOWN BAR (Bawah Gambar) --}}
                    @if ($listing->isCurrentlyActive())
                        <div class="absolute bottom-2 inset-x-2 z-10">
                            <div wire:key="countdown-{{ $listing->id }}"
                                x-data="{
                                    endsAt: {{ $listing->auction_end->timestamp }} * 1000,
                                    remaining: '',
                                    expired: false,
                                    tick() {
                                        let diff = this.endsAt - Date.now();
                                        if (diff <= 0) {
                                            this.remaining = 'Selesai';
                                            if (!this.expired) {
                                                this.expired = true;
                                                $wire.$refresh();
                                            }
                                            return;
                                        }
                                        let h = Math.floor(diff / 3600000);
                                        let m = Math.floor((diff % 3600000) / 60000);
                                        let s = Math.floor((diff % 60000) / 1000);
                                        this.remaining = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
                                    }
                                }"
                                x-init="tick(); setInterval(() => tick(), 1000)"
                                class="flex items-center justify-between px-3 py-1.5 rounded-xl bg-gray-900/80 backdrop-blur-md text-white text-xs shadow-md border border-white/10"
                            >
                                <span class="inline-flex items-center gap-1 text-[11px] text-gray-300 font-medium">
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Sisa Waktu
                                </span>
                                <span class="font-mono font-bold tracking-wider text-amber-300 text-xs" x-text="remaining"></span>
                            </div>
                        </div>
                    @endif
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

                        {{-- Seller & Created Info --}}
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
            {{-- Empty State --}}
            <div class="col-span-full bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center max-w-xl mx-auto my-8">
                <div class="w-16 h-16 mx-auto mb-4 text-indigo-500 bg-indigo-50 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Tidak Ada Listing Ditemukan</h3>
                <p class="text-sm text-gray-500 mb-6">Belum ada item lelang yang tersedia untuk kategori ini saat ini.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination Links --}}
    <div class="mt-8">
        {{ $this->listings->links() }}
    </div>
</div>