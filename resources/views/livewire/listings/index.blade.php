<?php

use Livewire\Volt\Component;
use App\Models\Listing;
use App\Models\Category;
use Livewire\WithPagination;

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
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Daftar Listing Lelang</h1>
            <p class="text-sm text-gray-500 mt-1">Jelajahi lelang hewan peliharaan & hias terkini</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="categoryFilter" class="rounded-lg border-gray-300 text-sm text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                <option value="">Semua Kategori</option>
                @foreach ($this->categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            @if (auth()->check())
                <a href="{{ route('listings.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                    + Buat Listing
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @forelse ($this->listings as $listing)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition flex flex-col justify-between overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs font-medium px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-full">
                            {{ $listing->category->name }}
                        </span>
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $listing->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $listing->status === 'active' ? 'Aktif' : 'Selesai' }}
                        </span>
                    </div>

                    <h2 class="text-lg font-bold text-gray-900 mb-2 line-clamp-1">
                        <a href="{{ route('listings.detail', $listing) }}" class="hover:text-indigo-600 transition">
                            {{ $listing->title }}
                        </a>
                    </h2>

                    <div class="mt-4 pt-4 border-t border-gray-50 flex items-baseline justify-between">
                        <span class="text-xs text-gray-400">Harga Saat Ini</span>
                        <span class="text-xl font-extrabold text-indigo-600">
                            Rp{{ number_format($listing->current_price, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-3 border-t border-gray-100">
                    <a href="{{ route('listings.detail', $listing) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center justify-between">
                        <span>Lihat Detail</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 bg-white rounded-xl border border-dashed border-gray-300">
                <p class="text-gray-500 font-medium">Belum ada listing yang tersedia.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $this->listings->links() }}
    </div>
</div>