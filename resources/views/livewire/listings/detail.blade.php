<?php

use Livewire\Volt\Component;
use App\Models\Listing;
use App\Actions\PlaceBidAction;

new class extends Component {
    public Listing $listing;
    public $bidAmount = '';
    public $errorMessage = '';

    public function mount(Listing $listing)
    {
        $this->listing = $listing->load(['category', 'seller', 'bids.bidder']);
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
        $this->listing->refresh();
        $this->listing->load('bids.bidder');
    }
}; ?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Detail utama & spesifikasi --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-6 sm:p-8 rounded-xl shadow-sm border border-gray-100">
                <div class="mb-3">
                    <span class="text-xs font-semibold px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-full">
                        {{ $listing->category->name }}
                    </span>
                </div>
                <h1 class="text-3xl font-extrabold text-gray-900 mb-2">{{ $listing->title }}</h1>
                <p class="text-sm text-gray-500">
                    Dijual oleh: <span class="font-semibold text-gray-700">{{ $listing->seller->name }}</span>
                </p>

                <hr class="my-6 border-gray-100" />

                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Spesifikasi Detail</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($listing->specs as $key => $value)
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <dt class="text-xs text-gray-500 uppercase font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="text-base font-bold text-gray-800 mt-1">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- Riwayat Bid --}}
            <div class="bg-white p-6 sm:p-8 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Riwayat Bid</h3>
                <ul class="divide-y divide-gray-100">
                    @forelse ($listing->bids as $bid)
                        <li class="py-3.5 flex justify-between items-center text-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="font-semibold text-gray-900">{{ $bid->bidder->name }}</span>
                                <span class="text-xs text-gray-400">{{ $bid->created_at->diffForHumans() }}</span>
                            </div>
                            <span class="font-bold text-indigo-600">Rp{{ number_format($bid->amount, 0, ',', '.') }}</span>
                        </li>
                    @empty
                        <li class="py-6 text-center text-gray-400 text-sm">Belum ada bid diajukan.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Side Panel: Status Lelang & Form Bid --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 sticky top-6">
                <div class="mb-6">
                    <span class="text-xs text-gray-400 uppercase font-semibold">Harga Saat Ini</span>
                    <p class="text-3xl font-black text-indigo-600 mt-1">
                        Rp{{ number_format($listing->current_price, 0, ',', '.') }}
                    </p>
                </div>

                @if ($listing->status === 'active' && $listing->auction_end->isFuture())
                    <div class="mb-6 bg-amber-50 border border-amber-200 text-amber-800 text-xs font-medium px-3.5 py-2.5 rounded-lg flex items-center justify-between">
                        <span>Berakhir dalam:</span>
                        <span class="font-bold">{{ $listing->auction_end->diffForHumans() }}</span>
                    </div>

                    @if (auth()->id() !== $listing->user_id)
                        <form wire:submit="submitBid" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Bid Anda</label>
                                <input type="number" step="0.01" wire:model="bidAmount"
                                       placeholder="Minimal Rp{{ number_format($this->minimumBid, 0, ',', '.') }}"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                @error('bidAmount') 
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                @enderror
                            </div>

                            @if ($errorMessage)
                                <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200 font-medium">
                                    {{ $errorMessage }}
                                </div>
                            @endif

                            <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition shadow-sm">
                                Ajukan Bid
                            </button>
                        </form>
                    @else
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-center">
                            <p class="text-xs text-gray-500 italic">Ini listing milik Anda — tidak dapat mengajukan bid.</p>
                        </div>
                    @endif
                @else
                    <div class="p-4 bg-gray-100 rounded-lg text-center border border-gray-200">
                        <p class="text-sm font-bold text-gray-800 mb-1">Lelang Telah Berakhir</p>
                        @if ($listing->current_winner_id)
                            <p class="text-xs text-gray-600">
                                Pemenang: <span class="font-bold text-gray-900">{{ $listing->currentWinner->name }}</span>
                                <br>
                                Total: <span class="text-indigo-600 font-bold">Rp{{ number_format($listing->current_price, 0, ',', '.') }}</span>
                            </p>
                        @else
                            <p class="text-xs text-gray-500">Tidak ada bid yang masuk.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>