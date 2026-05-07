<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\DashboardIndex;
use App\Livewire\BahanBakuIndex;
use App\Livewire\DapurIndex;
use App\Livewire\ProdukJadiIndex;
use App\Livewire\KasirIndex;
use App\Livewire\Actions\Logout;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('dashboard', DashboardIndex::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// ──────────────────────────────────────
// Dunia Gudang
// ──────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('bahan-baku', BahanBakuIndex::class)->name('bahan-baku.index');
    Route::get('produk-jadi', ProdukJadiIndex::class)->name('produk-jadi.index');
    Route::get('dapur', DapurIndex::class)->name('dapur.index');
    Route::get('kasir', KasirIndex::class)->name('kasir.index');
});

Route::post('logout', function (Request $request, Logout $logout) {
    $logout();
    return redirect('/');
})->middleware('auth')->name('logout');

require __DIR__.'/auth.php';
