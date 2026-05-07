<?php

namespace App\Livewire;

use App\Models\BahanBaku;
use App\Models\ProdukJadi;
use App\Models\Transaksi;
use Livewire\Component;
use Livewire\WithPagination;

class DashboardIndex extends Component
{
    use WithPagination;

    // ── Modal Struk ────────────────────────────────
    public bool        $showStruk    = false;
    public ?Transaksi  $activeStruk  = null;

    // ── Buka Modal ────────────────────────────────
    public function lihatDetail(int $id): void
    {
        $this->activeStruk = Transaksi::with(['detailTransaksi.produkJadi'])
            ->findOrFail($id);
        $this->showStruk = true;
    }

    public function tutupStruk(): void
    {
        $this->showStruk   = false;
        $this->activeStruk = null;
    }

    // ── Render ────────────────────────────────────
    public function render()
    {
        // ── 4 Metrik Utama ─────────────────────────
        $jumlahProdukJadi  = ProdukJadi::count();
        $jumlahBahanBaku   = BahanBaku::count();
        $totalPendapatan   = (int) Transaksi::sum('total_pendapatan');
        $totalHpp          = (int) Transaksi::sum('total_hpp');
        $labaKotorBersih   = $totalPendapatan - $totalHpp;

        // ── Riwayat Transaksi (paginasi 10) ────────
        $transaksiList = Transaksi::withCount('detailTransaksi')
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.dashboard-index', compact(
            'jumlahProdukJadi',
            'jumlahBahanBaku',
            'totalPendapatan',
            'labaKotorBersih',
            'transaksiList',
        ))->layout('layouts.erp', ['title' => 'Dashboard — Mini ERP']);
    }
}
