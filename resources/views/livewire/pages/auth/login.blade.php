<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    {{-- CSS Injection untuk menimpa gaya bawaan guest.blade.php tanpa menyentuh file tersebut --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif !important; background-color: #fdf6ec !important; }
        .min-h-screen { background-color: #fdf6ec !important; }
        /* Sembunyikan logo Laravel bawaan Breeze di luar card */
        .min-h-screen > div:first-child { display: none !important; }
        /* Modifikasi Card bawaan Breeze */
        .sm\:max-w-md { 
            max-width: 26rem !important; 
            border-radius: 1.25rem !important; 
            box-shadow: 0 10px 25px -5px rgba(139, 94, 60, 0.1) !important;
            padding: 2.5rem !important;
            border: 1px solid rgba(236, 221, 200, 0.6);
        }
    </style>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- Logo & Header --}}
    <div class="flex flex-col items-center mb-8">
        <div class="w-20 h-20 bg-[#fdf0e0] rounded-full flex items-center justify-center mb-4 shadow-sm border-4 border-white/50">
            <svg class="w-10 h-10 text-[#d4995f]" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
        </div>
        <h2 class="text-[22px] font-bold text-[#8B5E3C]">Managemen Stok</h2>
        <h3 class="text-xl font-medium text-gray-400 mt-1">Login</h3>
    </div>

    <form wire:submit="login" class="space-y-5">
        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-semibold text-[#8B5E3C] mb-1.5">Nama Pengguna</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-[#D4831F]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" placeholder="Username / Email"
                       class="pl-10 w-full rounded-xl border-[#ecddc8] text-sm focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] transition-all" />
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-semibold text-[#8B5E3C] mb-1.5">Kata Sandi</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-[#D4831F]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password" placeholder="Password"
                       class="pl-10 w-full rounded-xl border-[#ecddc8] text-sm focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] transition-all" />
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
            
            <div class="flex justify-end mt-2">
                @if (Route::has('password.request'))
                    <a class="text-[11px] font-medium text-[#d4995f] hover:text-[#8B5E3C] transition-colors" href="{{ route('password.request') }}" wire:navigate>
                        Lupa password?
                    </a>
                @endif
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full bg-[#7a4820] hover:bg-[#5e3718] text-white font-bold py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#8B5E3C]/50 focus:ring-offset-2">
                Login
            </button>
        </div>

        <!-- Register Link -->
        <div class="text-center mt-5">
            <p class="text-xs text-gray-800">
                Tidak memiliki akun? 
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" wire:navigate class="text-[#d4995f] hover:text-[#8B5E3C] font-medium transition-colors">Daftar disini.</a>
                @else
                    <a href="#" class="text-[#d4995f] hover:text-[#8B5E3C] font-medium transition-colors">Daftar disini.</a>
                @endif
            </p>
        </div>
    </form>
</div>
