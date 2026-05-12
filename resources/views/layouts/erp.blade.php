<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Mini ERP Snack & Bakery' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            * { font-family: 'Poppins', sans-serif; }
            /* Custom scrollbar */
            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: #d4a574; border-radius: 3px; }
            ::-webkit-scrollbar-thumb:hover { background: #b86a16; }
            
            /* Sembunyikan Alpine.js sebelum load */
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="antialiased bg-[#fdf6ec]">
        <div class="flex min-h-screen" x-data="{ sidebarOpen: false }">

            {{-- ============================================ --}}
            {{-- MOBILE OVERLAY --}}
            {{-- ============================================ --}}
            <div x-show="sidebarOpen" 
                 x-cloak
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sidebarOpen = false" 
                 class="fixed inset-0 bg-black/60 z-40 md:hidden backdrop-blur-sm">
            </div>

            {{-- ============================================ --}}
            {{-- SIDEBAR --}}
            {{-- ============================================ --}}
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                   class="w-[230px] bg-[#4A2E19] text-white flex flex-col fixed inset-y-0 left-0 z-50 shadow-xl transform transition-transform duration-300 ease-in-out md:translate-x-0">
                {{-- Logo Area --}}
                <div class="flex flex-col items-center py-6 px-4 border-b border-white/10 relative">
                    {{-- Tombol Tutup Sidebar (Hanya Mobile) --}}
                    <button @click="sidebarOpen = false" class="absolute top-4 right-4 md:hidden text-white/60 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <div class="w-14 h-14 bg-[#F5E6D3] rounded-full flex items-center justify-center mb-2 shadow-md">
                        <img src="{{ asset('img/logo-snackbakery.png') }}" alt="logo" class="w-35 h-35 object-contain">
                        {{-- <svg class="w-8 h-8 text-[#8B5E3C]" fill="currentColor" viewBox="0 0 24 24"> --}}
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <span class="font-semibold text-sm tracking-wide">Snack & Bakery</span>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
                    {{-- Dashboard --}}
                    <a href="{{ route('dashboard') }}" wire:navigate
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>
                        Dashboard
                    </a>

                    {{-- Stok Gudang Group --}}
                    <div x-data="{ open: {{ request()->routeIs('bahan-baku.*') || request()->routeIs('produk-jadi.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                                class="flex items-center justify-between w-full px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                                       {{ request()->routeIs('bahan-baku.*') || request()->routeIs('produk-jadi.*') ? 'bg-[#D4831F] text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                Stok Gudang
                            </span>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="open" x-collapse class="mt-1 ml-4 space-y-1">
                            <a href="{{ route('bahan-baku.index') }}" wire:navigate
                               class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200
                                      {{ request()->routeIs('bahan-baku.*') ? 'bg-[#D4831F]/80 text-white font-medium' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('bahan-baku.*') ? 'bg-white' : 'bg-white/40' }}"></span>
                                Bahan Baku
                            </a>
                            <a href="{{ route('produk-jadi.index') }}" wire:navigate
                               class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200
                                      {{ request()->routeIs('produk-jadi.*') ? 'bg-[#D4831F]/80 text-white font-medium' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('produk-jadi.*') ? 'bg-white' : 'bg-white/40' }}"></span>
                                Produk Jadi
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('dapur.index') }}" wire:navigate
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('dapur.*') ? 'bg-white/20 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <!-- <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg> -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chef-hat-icon lucide-chef-hat"><path d="M17 21a1 1 0 0 0 1-1v-5.35c0-.457.316-.844.727-1.041a4 4 0 0 0-2.134-7.589 5 5 0 0 0-9.186 0 4 4 0 0 0-2.134 7.588c.411.198.727.585.727 1.041V20a1 1 0 0 0 1 1Z"/><path d="M6 17h12"/></svg>
                        Dapur
                    </a>
                    <a href="{{ route('kasir.index') }}" wire:navigate
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('kasir.*') ? 'bg-white/20 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        Kasir
                    </a>
                </nav>

                {{-- Bottom Section --}}
                <div class="border-t border-white/10 p-3 space-y-1">
                    {{-- <a href="{{ route('profile') }}" wire:navigate
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Pengaturan
                    </a> --}}
                    <button x-data
                            @click="
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = '/logout';
                                const csrf = document.createElement('input');
                                csrf.type = 'hidden';
                                csrf.name = '_token';
                                csrf.value = document.querySelector('meta[name=csrf-token]').content;
                                form.appendChild(csrf);
                                document.body.appendChild(form);
                                form.submit();
                            "
                            class="flex items-center gap-3 w-full px-4 py-2.5 rounded-lg text-sm text-red-300 hover:bg-red-500/20 hover:text-red-200 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Keluar
                    </button>
                </div>
            </aside>

            {{-- ============================================ --}}
            {{-- MAIN CONTENT AREA --}}
            {{-- ============================================ --}}
            <div class="flex-1 md:ml-[230px] flex flex-col min-w-0 transition-all duration-300">
                {{-- Top Bar --}}
                <header class="sticky top-0 z-20 bg-white/80 backdrop-blur-md border-b border-[#e8d5b8]/50 px-4 md:px-8 py-3">
                    <div class="flex items-center justify-between gap-4">
                        
                        <div class="flex items-center gap-3 flex-1">
                            {{-- Hamburger Menu (Mobile) --}}
                            <button @click="sidebarOpen = true" class="md:hidden text-[#8B5E3C] hover:bg-[#F5E6D3] p-1.5 rounded-lg transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>

                            {{-- Global Search Bar --}}
                            <div class="relative w-full max-w-xs" x-data>
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" placeholder="Telusuri..."
                                       x-on:input.debounce.500ms="$dispatch('global-search', { search: $event.target.value })"
                                       x-on:keyup.escape="$event.target.value = ''; $dispatch('global-search', { search: '' })"
                                       class="pl-10 pr-4 py-2 w-full bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] outline-none transition-all duration-200" />
                            </div>
                        </div>

                        {{-- User Info --}}
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="hidden sm:inline text-sm text-gray-600">Halo, <strong class="text-gray-800">{{ Auth::user()->name }}</strong></span>
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#D4831F] to-[#8B5E3C] flex items-center justify-center text-white font-semibold text-sm shadow-md">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        </div>
                    </div>
                </header>

                {{-- Page Content --}}
                <main class="p-4 md:p-8 flex-1 overflow-x-hidden">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
