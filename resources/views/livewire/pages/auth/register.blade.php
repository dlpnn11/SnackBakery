<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
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

    {{-- Logo & Header --}}
    <div class="flex flex-col items-center mb-8">
        <div class="w-20 h-20 bg-[#fdf0e0] rounded-full flex items-center justify-center mb-4 shadow-sm border-4 border-white/50">
            <img src="{{ asset('img/logo-snackbakery.png') }}" alt="logo" class="w-35 h-35 object-contain">
            {{-- <x-application-logo class="w-10 h-10 text-[#d4995f]" /> --}}
        </div>
        <h2 class="text-[22px] font-bold text-[#8B5E3C]">Snack &amp; Bakery</h2>
        <h3 class="text-xl font-medium text-gray-400 mt-1">Daftar Akun</h3>
    </div>

    <form wire:submit="register" class="space-y-5">
        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-semibold text-[#8B5E3C] mb-1.5">Nama Lengkap</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-[#D4831F]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input wire:model="name" id="name" type="text" name="name" required autofocus autocomplete="name" placeholder="Nama Lengkap"
                       class="pl-10 w-full rounded-xl border-[#ecddc8] text-sm focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] transition-all" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-semibold text-[#8B5E3C] mb-1.5">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-[#D4831F]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <input wire:model="email" id="email" type="email" name="email" required autocomplete="username" placeholder="Email Address"
                       class="pl-10 w-full rounded-xl border-[#ecddc8] text-sm focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] transition-all" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
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
                <input wire:model="password" id="password" type="password" name="password" required autocomplete="new-password" placeholder="Password"
                       class="pl-10 w-full rounded-xl border-[#ecddc8] text-sm focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] transition-all" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-[#8B5E3C] mb-1.5">Konfirmasi Kata Sandi</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-[#D4831F]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Konfirmasi Password"
                       class="pl-10 w-full rounded-xl border-[#ecddc8] text-sm focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] transition-all" />
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full bg-[#7a4820] hover:bg-[#5e3718] text-white font-bold py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#8B5E3C]/50 focus:ring-offset-2">
                Daftar
            </button>
        </div>

        <!-- Login Link -->
        <div class="text-center mt-5">
            <p class="text-xs text-gray-800">
                Sudah memiliki akun? 
                <a href="{{ route('login') }}" wire:navigate class="text-[#d4995f] hover:text-[#8B5E3C] font-medium transition-colors">Login disini.</a>
            </p>
        </div>
    </form>
</div>
