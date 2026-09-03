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

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 group">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <div class="hidden space-x-2 sm:-my-px sm:flex items-center">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')" wire:navigate class="px-3 py-2 text-sm font-semibold rounded-lg transition-colors">
                        {{ __('Home') }}
                    </x-nav-link>

                    <x-nav-link :href="route('listings.index')" :active="request()->routeIs('listings.index') || request()->routeIs('listings.detail')" wire:navigate class="px-3 py-2 text-sm font-semibold rounded-lg transition-colors">
                        {{ __('Daftar Lelang') }}
                    </x-nav-link>

                    <x-nav-link :href="route('my-auctions')" :active="request()->routeIs('my-auctions')" wire:navigate class="px-3 py-2 text-sm font-semibold rounded-lg transition-colors">
                        {{ __('Lelang Saya') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown (Desktop Profile & Logout) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-3 px-3 py-1.5 border border-gray-200 text-sm font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition duration-150">
                            {{-- Avatar Inisial --}}
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                                {{ strtoupper(substr(auth()->user()->name ?? 'User', 0, 1)) }}
                            </div>

                            <div class="text-left hidden md:block">
                                <div class="text-xs font-bold text-gray-800 leading-tight" x-data="{{ json_encode(['name' => auth()->user()?->name ?? 'User']) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
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
                            <p class="text-sm font-bold text-gray-900 truncate" x-data="{{ json_encode(['name' => auth()->user()?->name ?? 'User']) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email ?? 'Email' }}</p>
                        </div>

                        <!-- Menu Profile -->
                        <div class="py-1">
                            <x-dropdown-link :href="route('profile')" wire:navigate class="flex items-center gap-2.5 text-xs font-semibold">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ __('Pengaturan Profil') }}
                            </x-dropdown-link>
                        </div>

                        <!-- Menu Logout -->
                        <div class="border-t border-gray-100 py-1">
                            <button wire:click="logout" class="w-full text-left">
                                <x-dropdown-link class="flex items-center gap-2.5 text-xs font-semibold text-red-600 hover:bg-red-50 hover:text-red-700">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    {{ __('Keluar / Log Out') }}
                                </x-dropdown-link>
                            </button>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile Button) -->
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
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-100 bg-gray-50/50">
        <div class="pt-2 pb-3 space-y-1 px-2">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')" wire:navigate class="rounded-xl font-semibold">
                {{ __('home') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('listings.index')" :active="request()->routeIs('listings.index') || request()->routeIs('listings.detail')" wire:navigate class="rounded-xl font-semibold">
                {{ __('Daftar Lelang') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('my-auctions')" :active="request()->routeIs('my-auctions')" wire:navigate class="rounded-xl font-semibold">
                {{ __('Lelang Saya') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Profile Options -->
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="px-4 flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 border border-indigo-200 flex items-center justify-center text-indigo-700 font-bold text-sm">
                    {{ strtoupper(substr(auth()->user()->name ?? 'User', 0, 1)) }}
                </div>
                <div>
                    <div class="font-bold text-sm text-gray-900" x-data="{{ json_encode(['name' => auth()->user()?->name ?? 'User']) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                    <div class="font-medium text-xs text-gray-500">{{ auth()->user()->email ?? 'Email' }}</div>
                </div>
            </div>

            <div class="space-y-1 px-2">
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
        </div>
    </div>
</nav>