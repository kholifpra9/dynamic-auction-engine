<?php

use App\Actions\CloseAuctionAction;
use Livewire\Volt\Component;
use App\Models\Listing;
use App\Actions\PlaceBidAction;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    public Listing $listing;
    public $bidAmount = '';
    public $errorMessage = '';

    private function closeIfExpired(): void
    {
        if ($this->listing->status === 'active' && $this->listing->auction_end->isPast()) {
            app(CloseAuctionAction::class)->execute($this->listing);
        }
    }

    public function mount(Listing $listing)
    {
        $this->listing = $listing->load(['category', 'seller', 'bids.bidder']);
        $this->closeIfExpired();
        $this->listing->refresh();
    }

    public function getMinimumBidProperty(): float
    {
        return app(PlaceBidAction::class)->minimumNextBid($this->listing);
    }

    public function submitBid()
    {
        $this->errorMessage = '';

        $this->validate([
            'bidAmount' => 'required|numeric|min:0',
        ]);

        try {
            app(PlaceBidAction::class)->execute(
                $this->listing,
                auth()->user(),
                (float) $this->bidAmount
            );

            $this->listing->refresh();
            $this->listing->load('bids.bidder');
            $this->bidAmount = '';

            $this->dispatch('notify', type: 'success', message: 'Tawaran berhasil diajukan!');

        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function getListeners()
    {
        return [
            "echo:listing.{$this->listing->id},NewBidPlaced" => 'refreshListing',
        ];
    }

    public function refreshListing()
    {
        $wasActive = $this->listing->status === 'active';
        
        $this->closeIfExpired();
        $this->listing->refresh();
        $this->listing->load('bids.bidder');

        if ($wasActive && $this->listing->status === 'ended') {
            $this->dispatch('notify', type: 'warning', message: 'Lelang telah berakhir.');
        }
    }
}; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Breadcrumb Navigasi --}}
    <nav class="flex mb-6 text-sm text-gray-500 gap-2 items-center">
        <a href="{{ route('listings.index') }}" wire:navigate class="hover:text-indigo-600 transition">Daftar Lelang</a>
        <span>/</span>
        <span class="text-gray-900 font-medium truncate max-w-xs sm:max-w-md">{{ $listing->title }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        {{-- Detail Utama & Spesifikasi --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Main Image & Basic Info Card --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                {{-- Media Header --}}
                <div class="relative w-full h-80 sm:h-96 bg-gray-100">
                    @if ($listing->photo_path && Storage::disk('public')->exists($listing->photo_path))
                        <img src="{{ Storage::url($listing->photo_path) }}" 
                             alt="{{ $listing->title }}" 
                             onerror="this.onerror=null; this.remove(); this.nextElementSibling.classList.remove('hidden');"
                             class="w-full h-full object-cover">
                        
                        <div class="hidden w-full h-full flex flex-col items-center justify-center bg-gray-100 text-gray-400">
                            <svg class="w-16 h-16 mb-2 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <span class="text-sm font-medium">Gambar Tidak Tersedia</span>
                        </div>
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 text-gray-400">
                            <svg class="w-16 h-16 mb-2 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <span class="text-sm font-medium">Gambar Tidak Tersedia</span>
                        </div>
                    @endif

                    {{-- Category Floating Badge --}}
                    <div class="absolute top-4 left-4 z-10">
                        <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white/90 backdrop-blur-md text-gray-800 shadow-sm border border-white/20">
                            {{ $listing->category->name }}
                        </span>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mb-3">
                        {{ $listing->title }}
                    </h1>
                    
                    {{-- Seller Info --}}
                    <div class="flex items-center gap-3 py-3 border-y border-gray-100 mb-6">
                        <div class="w-10 h-10 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
                            {{ strtoupper(substr($listing->seller->name, 0, 1)) }}
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block font-medium">Dipublikasikan Oleh</span>
                            <span class="text-sm font-bold text-gray-800">{{ $listing->seller->name }}</span>
                        </div>
                    </div>

                    {{-- Spesifikasi Detail --}}
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Spesifikasi Detail</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @forelse ($listing->specs as $key => $value)
                            <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-100">
                                <dt class="text-xs text-gray-400 uppercase font-semibold">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                <dd class="text-base font-bold text-gray-800 mt-1">{{ $value }}</dd>
                            </div>
                        @empty
                            <div class="col-span-full text-xs text-gray-400 italic">Tidak ada spesifikasi khusus tercantum.</div>
                        @endforelse
                    </dl>
                </div>
            </div>

            {{-- Riwayat Bid Card --}}
            <div class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Riwayat Penawaran</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Daftar penawaran harga secara berurutan.</p>
                    </div>
                    <span class="text-xs font-semibold px-3 py-1 bg-gray-100 text-gray-600 rounded-full">
                        {{ $listing->bids->count() }} Penawaran
                    </span>
                </div>

                <div class="flow-root">
                    <ul class="divide-y divide-gray-100">
                        @forelse ($listing->bids as $index => $bid)
                            <li class="py-4 flex items-center justify-between text-sm hover:bg-gray-50/50 px-2 rounded-xl transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full {{ $index === 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }} flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($bid->bidder->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900">{{ $bid->bidder->name }}</span>
                                            @if ($index === 0)
                                                <span class="text-[10px] font-bold px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full">Tertinggi</span>
                                            @endif
                                        </div>
                                        <span class="text-xs text-gray-400">{{ $bid->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <span class="font-extrabold text-base text-indigo-600">
                                    Rp {{ number_format($bid->amount, 0, ',', '.') }}
                                </span>
                            </li>
                        @empty
                            <li class="py-8 text-center text-gray-400 text-sm">
                                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Belum ada penawaran diajukan. Jadi yang pertama menawar!
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Side Panel: Status Lelang & Form Bid --}}
        <div class="lg:sticky lg:top-8 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-sm space-y-6">
                
                {{-- Price Banner --}}
                <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100/60">
                    <span class="text-[11px] uppercase tracking-wider text-indigo-600/80 block font-bold">Harga Saat Ini</span>
                    <p class="text-3xl font-black text-indigo-600 mt-0.5">
                        Rp {{ number_format($listing->current_price, 0, ',', '.') }}
                    </p>
                </div>

                @if ($listing->isCurrentlyActive())
                    {{-- Countdown Timer Widget --}}
                    <div x-data="{
                            endsAt: {{ $listing->auction_end->timestamp }} * 1000,
                            remaining: '',
                            expired: false,
                            tick() {
                                let diff = this.endsAt - Date.now();
                                if (diff <= 0) {
                                    this.remaining = 'Lelang Berakhir';
                                    if (!this.expired) {
                                        this.expired = true;
                                        $wire.refreshListing();
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
                        class="p-4 bg-amber-50/70 border border-amber-200/60 rounded-xl flex items-center justify-between text-amber-900"
                    >
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                            <span class="text-xs font-semibold">Sisa Waktu:</span>
                        </div>
                        <span class="font-mono text-base font-bold tracking-wider" x-text="remaining"></span>
                    </div>

                    {{-- Form Bidding (Hanya untuk User Lain) --}}
                    @if (auth()->check() && auth()->id() !== $listing->user_id)
                        <form wire:submit="submitBid" class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-xs font-bold text-gray-700">Nominal Harga Tawaran Anda</label>
                                    <span class="text-[11px] text-gray-400">Min. Rp {{ number_format($this->minimumBid, 0, ',', '.') }}</span>
                                </div>
                                <div class="relative rounded-xl shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm font-semibold">Rp</span>
                                    </div>
                                    <input type="number" step="0.01" wire:model="bidAmount"
                                           placeholder="{{ number_format($this->minimumBid, 0, ',', '.') }}"
                                           class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">
                                </div>
                                @error('bidAmount') 
                                    <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> 
                                @enderror
                            </div>

                            @if ($errorMessage)
                                <div class="p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-200 font-medium flex items-center gap-2">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ $errorMessage }}</span>
                                </div>
                            @endif

                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-xl text-sm shadow-sm transition-all duration-200 flex items-center justify-center gap-2">
                                <span wire:loading.remove>Ajukan Penawaran</span>
                                <span wire:loading>Memproses...</span>
                            </button>
                        </form>
                    @elseif(!auth()->check())
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-center">
                            <p class="text-xs text-gray-600 mb-3">Silakan masuk ke akun Anda terlebih dahulu untuk menawar.</p>
                            <a href="{{ route('login') }}" class="inline-block w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-xs transition">
                                Masuk / Login
                            </a>
                        </div>
                    @else
                        <div class="p-4 bg-gray-50 border border-gray-200/80 rounded-xl text-center">
                            <p class="text-xs text-gray-500 italic">Ini adalah Item Lelang milik Anda sendiri (Pemilik tidak dapat Mengajukan harga).</p>
                        </div>
                    @endif
                @else
                    {{-- Status Selesai / Pemenang --}}
                    <div class="p-5 bg-gray-900 text-white rounded-xl text-center space-y-2">
                        <span class="inline-block text-xs uppercase tracking-widest font-bold text-amber-400">Status Lelang</span>
                        <h4 class="text-lg font-extrabold">Lelang Telah Selesai</h4>
                        
                        @if ($listing->current_winner_id)
                            <div class="pt-3 border-t border-gray-800 text-xs">
                                <p class="text-gray-400">Pemenang:</p>
                                <p class="text-base font-bold text-emerald-400 mt-0.5">{{ $listing->currentWinner->name }}</p>
                                <p class="text-gray-400 mt-1">Total Akhir: <span class="text-white font-bold">Rp {{ number_format($listing->current_price, 0, ',', '.') }}</span></p>
                            </div>
                        @else
                            <p class="text-xs text-gray-400 pt-2 border-t border-gray-800">Lelang berakhir tanpa ada penawaran masuk.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>