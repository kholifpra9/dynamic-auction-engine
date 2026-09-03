<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false, scrolled: false }" 
     x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 10 })"
     :class="{ 'bg-white/90 backdrop-blur-md shadow-sm border-b border-gray-200/80': scrolled, 'bg-white border-b border-gray-100': !scrolled }"
     class="sticky top-0 z-50 transition-all duration-300">
     
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <!-- Logo Aplikasi -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 group focus:outline-none">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <div class="hidden space-x-1 sm:-my-px sm:flex items-center">
                    {{-- Navigasi Utama (Bisa Diakses Semua Orang) --}}
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')" wire:navigate 
                                class="px-3.5 py-2 text-sm font-semibold rounded-xl transition-all duration-200 hover:bg-gray-100/80 hover:text-indigo-600">
                        {{ __('Home') }}
                    </x-nav-link>

                    <x-nav-link :href="route('listings.index')" :active="request()->routeIs('listings.index') || request()->routeIs('listings.detail')" wire:navigate 
                                class="px-3.5 py-2 text-sm font-semibold rounded-xl transition-all duration-200 hover:bg-gray-100/80 hover:text-indigo-600">
                        {{ __('Daftar Lelang') }}
                    </x-nav-link>

                    {{-- Navigasi Khusus User Terautentikasi (Sudah Login) --}}
                    @auth
                        <x-nav-link :href="route('my-auctions')" :active="request()->routeIs('my-auctions')" wire:navigate 
                                    class="px-3.5 py-2 text-sm font-semibold rounded-xl transition-all duration-200 hover:bg-gray-100/80 hover:text-indigo-600">
                            {{ __('Lelang Saya') }}
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            <!-- Side Menu Desktop (Profile atau Login Button) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    {{-- User Dropdown (Tampil saat Login) --}}
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-3 px-3 py-1.5 border border-gray-200 text-sm font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition-all duration-200">
                                {{-- Avatar Inisial --}}
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                                </div>

                                <div class="text-left hidden md:block">
                                    <div class="text-xs font-bold text-gray-800 leading-tight" 
                                         x-data="{{ json_encode(['name' => auth()->user()?->name ?? 'User']) }}" 
                                         x-text="name" 
                                         x-on:profile-updated.window="name = $event.detail.name"></div>
                                    <span class="text-[10px] text-gray-400 font-medium">Akun Pengguna</span>
                                </div>

                                <svg class="w-4 h-4 text-gray-400 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <!-- User Header Info -->
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Terautentikasi</p>
                                <p class="text-sm font-bold text-gray-900 truncate" 
                                   x-data="{{ json_encode(['name' => auth()->user()?->name ?? 'User']) }}" 
                                   x-text="name" 
                                   x-on:profile-updated.window="name = $event.detail.name"></p>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()?->email }}</p>
                            </div>

                            <!-- Menu Profile -->
                            <div class="py-1">
                                <x-dropdown-link :href="route('profile')" wire:navigate class="flex items-center gap-2.5 text-xs font-semibold hover:bg-indigo-50/50 hover:text-indigo-600 transition-colors">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ __('Pengaturan Profil') }}
                                </x-dropdown-link>
                            </div>

                            <!-- Menu Logout -->
                            <div class="border-t border-gray-100 py-1">
                                <button wire:click="logout" class="w-full text-left">
                                    <x-dropdown-link class="flex items-center gap-2.5 text-xs font-semibold text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        {{ __('Keluar / Log Out') }}
                                    </x-dropdown-link>
                                </button>
                            </div>
                        </x-slot>
                    </x-dropdown>
                @else
                    {{-- Tombol Login / Guest Actions (Tampil saat Belum Login) --}}
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" wire:navigate 
                           class="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-indigo-600 hover:bg-gray-100/80 rounded-xl transition-all duration-200">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" wire:navigate 
                           class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm hover:shadow transition-all duration-200">
                            Daftar
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Hamburger Button (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Mobile Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-100 bg-white shadow-lg">
        <div class="pt-2 pb-3 space-y-1 px-3">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')" wire:navigate class="rounded-xl font-semibold">
                {{ __('Home') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('listings.index')" :active="request()->routeIs('listings.index') || request()->routeIs('listings.detail')" wire:navigate class="rounded-xl font-semibold">
                {{ __('Daftar Lelang') }}
            </x-responsive-nav-link>

            @auth
                <x-responsive-nav-link :href="route('my-auctions')" :active="request()->routeIs('my-auctions')" wire:navigate class="rounded-xl font-semibold">
                    {{ __('Lelang Saya') }}
                </x-responsive-nav-link>
            @endauth
        </div>

        <!-- Responsive Options Header -->
        <div class="pt-4 pb-3 border-t border-gray-100 px-3">
            @auth
                <div class="px-2 flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-100 border border-indigo-200 flex items-center justify-center text-indigo-700 font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-sm text-gray-900" 
                             x-data="{{ json_encode(['name' => auth()->user()?->name ?? 'User']) }}" 
                             x-text="name" 
                             x-on:profile-updated.window="name = $event.detail.name"></div>
                        <div class="font-medium text-xs text-gray-500">{{ auth()->user()?->email }}</div>
                    </div>
                </div>

                <div class="space-y-1">
                    <x-responsive-nav-link :href="route('profile')" wire:navigate class="rounded-xl font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {{ __('Pengaturan Profil') }}
                    </x-responsive-nav-link>

                    <button wire:click="logout" class="w-full text-left">
                        <x-responsive-nav-link class="rounded-xl font-semibold text-red-600 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            {{ __('Keluar / Log Out') }}
                        </x-responsive-nav-link>
                    </button>
                </div>
            @else
                <div class="grid grid-cols-2 gap-2 pt-1 pb-2">
                    <a href="{{ route('login') }}" wire:navigate 
                       class="w-full py-2.5 text-center text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" wire:navigate 
                       class="w-full py-2.5 text-center text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition">
                        Daftar
                    </a>
                </div>
            @endauth
        </div>
    </div>
</nav>