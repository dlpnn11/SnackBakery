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

Route::get('healthz', function () {
    try {
        $userCount = \App\Models\User::count();
        return response()->json([
            'status' => 'healthy',
            'database' => 'connected',
            'driver' => \Illuminate\Support\Facades\DB::connection()->getDriverName(),
            'users_in_supabase' => $userCount,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::get('dashboard', DashboardIndex::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

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
