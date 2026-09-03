<?php

use Livewire\Volt\Component;
use App\Models\Category;
use App\Models\Listing;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $category_id = '';
    public $title = '';
    public $description = '';
    public $photo;
    public $starting_price = '';
    public $duration_minutes = 15;

    public $specValues = [];

    public function getCategoriesProperty()
    {
        return Category::all();
    }

    public function getSelectedCategorySlugProperty()
    {
        return Category::find($this->category_id)?->slug;
    }

    public function getSpecFieldsProperty(): array
    {
        return config('listing_categories.' . $this->selectedCategorySlug, []);
    }

    public function updatedCategoryId()
    {
        // Reset nilai spec saat ganti kategori, agar tidak menyangkut field kategori lama
        $this->specValues = [];
    }

    public function save()
    {
        $this->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|min:3',
            'starting_price' => 'required|numeric|min:1',
            'duration_minutes' => 'required|integer|min:1',
            'photo' => 'required|image|max:2048', // max 2MB
        ]);

        $specs = $this->validateSpecsAgainstConfig();
        
        // Simpan file foto ke storage
        $photoPath = $this->photo->store('listings', 'public');

        Listing::create([
            'user_id' => auth()->id(),
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'specs' => $specs,
            'photo_path' => $photoPath, // Fixed: Perbaikan dari bug $this->$photoPath
            'starting_price' => $this->starting_price,
            'current_price' => $this->starting_price,
            'auction_start' => now(),
            'auction_end' => now()->addMinutes((int) $this->duration_minutes),
            'status' => 'active',
        ]);

        session()->flash('toast', ['type' => 'success', 'message' => 'Lelang berhasil dibuat!']);

        return redirect()->route('my-auctions');
    }

    private function validateSpecsAgainstConfig(): array
    {
        $rules = [];
        foreach ($this->specFields as $key => $field) {
            $rules["specValues.$key"] = $field['rules'];
        }

        $validated = $this->validate($rules);

        return $validated['specValues'] ?? [];
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header & Breadcrumb --}}
    <div class="mb-6">
        <nav class="flex text-sm text-gray-500 gap-2 items-center mb-2">
            <a href="{{ route('my-auctions') }}" wire:navigate class="hover:text-indigo-600 transition">Lelang Saya</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">Buat Baru</span>
        </nav>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Buat Listing Lelang Baru</h1>
        <p class="text-sm text-gray-500 mt-1">Isi formulir berikut untuk mempublikasikan item lelang Anda ke publik.</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-6 sm:p-8">
        <form wire:submit="save" class="space-y-6">
            
            {{-- Kategori --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori Lelang <span class="text-red-500">*</span></label>
                <div class="relative">
                    <select wire:model.live="category_id" class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all appearance-none cursor-pointer">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
                @error('category_id') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            {{-- Judul Listing --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Listing <span class="text-red-500">*</span></label>
                <input type="text" wire:model="title" placeholder="Contoh: Ikan Betta Dragon Red Super Full Egg" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">
                @error('title') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            {{-- Upload Foto dengan Live Preview --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Foto Item <span class="text-red-500">*</span></label>
                
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 rounded-2xl hover:border-indigo-400 transition-colors bg-gray-50/50">
                    <div class="space-y-2 text-center">
                        @if ($photo)
                            <div class="relative inline-block group">
                                <img src="{{ $photo->temporaryUrl() }}" class="h-44 w-44 object-cover rounded-xl border border-gray-200 shadow-sm">
                                <div class="mt-2 text-xs text-gray-500 font-medium">Preview Foto Unggahan</div>
                            </div>
                        @else
                            <svg class="mx-auto h-12 w-12 text-gray-400 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label class="relative cursor-pointer bg-white rounded-md font-semibold text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                    <span>Pilih berkas foto</span>
                                    <input type="file" wire:model="photo" accept="image/*" class="sr-only">
                                </label>
                                <p class="pl-1">atau tarik berkas ke sini</p>
                            </div>
                            <p class="text-xs text-gray-400">PNG, JPG, WEBP hingga 2MB</p>
                        @endif

                        {{-- Loading Indicator saat Unggah Foto --}}
                        <div wire:loading wire:target="photo" class="text-xs text-indigo-600 font-medium mt-2">
                            Mengunggah foto...
                        </div>
                    </div>
                </div>
                @error('photo') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            {{-- Field Dinamis Spesifikasi Kategori --}}
            @if (count($this->specFields) > 0)
                <div class="p-5 bg-indigo-50/40 rounded-2xl border border-indigo-100/80 space-y-4" wire:key="specs-{{ $this->selectedCategorySlug }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-xs font-bold text-indigo-900 uppercase tracking-wider">
                            Spesifikasi Tambahan (Kategori: {{ ucfirst(str_replace('-', ' ', $this->selectedCategorySlug)) }})
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach ($this->specFields as $key => $field)
                            <div wire:key="spec-{{ $key }}">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">{{ $field['label'] }}</label>

                                @if ($field['type'] === 'select')
                                    <select wire:model="specValues.{{ $key }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($field['options'] as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $field['type'] }}" wire:model="specValues.{{ $key }}"
                                           class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">
                                @endif

                                @error('specValues.' . $key) <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Deskripsi --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Ringkas</label>
                <textarea wire:model="description" rows="3" placeholder="Jelaskan kondisi, keunikan, atau catatan kesehatan item lelang..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all"></textarea>
                @error('description') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            {{-- Harga Awal & Durasi Lelang --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Harga Awal <span class="text-red-500">*</span></label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-gray-500 sm:text-sm font-semibold">Rp</span>
                        </div>
                        <input type="number" step="0.01" wire:model="starting_price" placeholder="50000" class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">
                    </div>
                    @error('starting_price') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Durasi Lelang <span class="text-red-500">*</span></label>
                    <div class="relative rounded-xl shadow-sm">
                        <input type="number" wire:model="duration_minutes" placeholder="15" class="w-full pl-4 pr-16 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <span class="text-gray-400 text-xs font-semibold">menit</span>
                        </div>
                    </div>
                    @error('duration_minutes') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Tombol Aksi Submit --}}
            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('my-auctions') }}" wire:navigate class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-xl text-sm shadow-sm transition-all duration-200 flex items-center gap-2">
                    <span wire:loading.remove>Publikasikan Lelang</span>
                    <span wire:loading>Memproses...</span>
                </button>
            </div>
        </form>
    </div>
</div>