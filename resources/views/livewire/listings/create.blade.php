<?php

use Livewire\Volt\Component;
use App\Models\Category;
use App\Models\Listing;

new class extends Component {
    public $category_id = '';
    public $title = '';
    public $description = '';
    public $photo_url = '';
    public $starting_price = '';
    public $duration_minutes = 15;

    // Field specs
    public $panjang_cm = '';
    public $usia_bulan = '';
    public $jenis = '';
    public $jenis_kicau = '';
    public $jenis_kelamin = '';

    public function getCategoriesProperty()
    {
        return Category::all();
    }

    public function getSelectedCategorySlugProperty()
    {
        return Category::find($this->category_id)?->slug;
    }

    public function save()
    {
        $this->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|min:3',
            'starting_price' => 'required|numeric|min:1',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        $specs = $this->buildSpecsForCategory();

        Listing::create([
            'user_id' => auth()->id(),
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'specs' => $specs,
            'photo_path' => $this->photo_url,
            'starting_price' => $this->starting_price,
            'current_price' => $this->starting_price,
            'auction_start' => now(),
            'auction_end' => now()->addMinutes((int) $this->duration_minutes),
            'status' => 'active',
        ]);

        return redirect()->route('my-auctions');
    }

    private function buildSpecsForCategory(): array
    {
        return match ($this->selectedCategorySlug) {
            'ikan-hias' => $this->validate([
                'panjang_cm' => 'required|numeric|min:0',
                'usia_bulan' => 'required|integer|min:0',
                'jenis' => 'required|string',
            ]),
            'burung' => $this->validate([
                'usia_bulan' => 'required|integer|min:0',
                'jenis_kicau' => 'required|string',
                'jenis_kelamin' => 'required|in:Jantan,Betina',
            ]),
            default => [],
        };
    }
}; ?>

<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Buat Listing Baru</h1>

        <form wire:submit="save" class="space-y-5">
            {{-- Select Kategori --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select wire:model.live="category_id" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach ($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- Judul --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Listing</label>
                <input type="text" wire:model="title" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                @error('title') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- Deskripsi --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea wire:model="description" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"></textarea>
            </div>

            {{-- Foto URL --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">URL Foto</label>
                <input type="text" wire:model="photo_url" placeholder="https://..." class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
            </div>

            {{-- Dynamic Fields Container --}}
            @if ($this->selectedCategorySlug === 'ikan-hias' || $this->selectedCategorySlug === 'burung')
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 space-y-4" wire:key="specs-container-{{ $this->selectedCategorySlug }}">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                        Spesifikasi {{ ucfirst(str_replace('-', ' ', $this->selectedCategorySlug)) }}
                    </h3>

                    @if ($this->selectedCategorySlug === 'ikan-hias')
                        <div wire:key="spec-ikan-panjang">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Panjang (cm)</label>
                            <input type="number" step="0.1" wire:model="panjang_cm" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            @error('panjang_cm') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div wire:key="spec-ikan-usia">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Usia (bulan)</label>
                            <input type="number" wire:model="usia_bulan" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            @error('usia_bulan') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div wire:key="spec-ikan-jenis">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Jenis</label>
                            <input type="text" wire:model="jenis" placeholder="misal: Koi, Cupang" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            @error('jenis') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                    @elseif ($this->selectedCategorySlug === 'burung')
                        <div wire:key="spec-burung-usia">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Usia (bulan)</label>
                            <input type="number" wire:model="usia_bulan" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            @error('usia_bulan') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div wire:key="spec-burung-jenis-kicau">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Jenis Kicau</label>
                            <input type="text" wire:model="jenis_kicau" placeholder="misal: Kenari, Murai" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            @error('jenis_kicau') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div wire:key="spec-burung-jenis-kelamin">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                            <select wire:model="jenis_kelamin" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="">-- Pilih --</option>
                                <option value="Jantan">Jantan</option>
                                <option value="Betina">Betina</option>
                            </select>
                            @error('jenis_kelamin') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
            @endif

            {{-- Harga & Durasi --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Awal (Rp)</label>
                    <input type="number" step="0.01" wire:model="starting_price" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    @error('starting_price') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Durasi Lelang (menit)</label>
                    <input type="number" wire:model="duration_minutes" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    @error('duration_minutes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition shadow-sm">
                    Buat Listing
                </button>
            </div>
        </form>
    </div>
</div>