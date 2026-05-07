<?php

namespace App\Livewire;

use App\Models\BahanBaku;
use App\Models\ProdukJadi;
use App\Models\Resep;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DapurIndex extends Component
{
    // ── State ──────────────────────────────────────
    public ?int   $selectedProdukId   = null;
    public int    $jumlahMasak        = 1;
    public string $flashMessage       = '';
    public string $flashType          = 'success';

    // ── Computed cache (tidak disimpan ke session) ─
    /** @var array{max:int, bottleneck:string|null, simulasi:array} */
    public array  $kalkulasi          = [];

    // ── Pilih Produk ───────────────────────────────
    public function pilihProduk(int $id): void
    {
        $this->selectedProdukId = $id;
        $this->jumlahMasak      = 1;
        $this->kalkulasi        = $this->hitungKalkulasi($id);
    }

    // ── ALGORITMA KALKULATOR PRODUKSI ─────────────
    /**
     * Membaca stok bahan_baku vs resep.
     * Cari bottleneck: bahan yang paling membatasi jumlah produksi.
     *
     * Return:
     *   max        → estimasi maksimal produksi
     *   bottleneck → nama bahan pembatas
     *   simulasi   → array detail sisa per bahan jika diproduksi sebanyak 'jumlahMasak'
     */
    private function hitungKalkulasi(int $produkId): array
    {
        $reseps = Resep::with('bahanBaku')
            ->where('produk_jadi_id', $produkId)
            ->get();

        if ($reseps->isEmpty()) {
            return ['max' => 0, 'bottleneck' => null, 'simulasi' => []];
        }

        $max        = PHP_INT_MAX;
        $bottleneck = null;

        foreach ($reseps as $r) {
            if ($r->kebutuhan_satuan <= 0) continue;
            $bisa = (int) floor($r->bahanBaku->stok / $r->kebutuhan_satuan);
            if ($bisa < $max) {
                $max        = $bisa;
                $bottleneck = $r->bahanBaku->nama_bahan;
            }
        }

        if ($max === PHP_INT_MAX) $max = 0;

        // Simulasi sisa bahan jika jumlahMasak diproduksi
        $simulasi = [];
        foreach ($reseps as $r) {
            $dipakai     = $r->kebutuhan_satuan * $this->jumlahMasak;
            $sisa        = $r->bahanBaku->stok - $dipakai;
            $simulasi[]  = [
                'nama'     => $r->bahanBaku->nama_bahan,
                'satuan'   => $r->bahanBaku->satuan,
                'stok'     => $r->bahanBaku->stok,
                'kebutuhan'=> $r->kebutuhan_satuan,
                'dipakai'  => $dipakai,
                'sisa'     => $sisa,
                'cukup'    => $sisa >= 0,
            ];
        }

        return compact('max', 'bottleneck', 'simulasi');
    }

    // ── Update simulasi saat jumlahMasak berubah ───
    public function updatedJumlahMasak(): void
    {
        if ($this->selectedProdukId) {
            $this->kalkulasi = $this->hitungKalkulasi($this->selectedProdukId);
        }
    }

    // ── EKSEKUSI PRODUKSI ─────────────────────────
    /**
     * Aturan PRD §8.3 — ACID Compliance:
     * Potong stok bahan_baku + tambah stok produk_jadi
     * dalam satu DB::transaction(). Rollback jika ada stok kurang.
     */
    public function prosesProduksi(): void
    {
        if (!$this->selectedProdukId || $this->jumlahMasak < 1) {
            $this->flash('Pilih produk dan masukkan jumlah masak yang valid.', 'danger');
            return;
        }

        $produk = ProdukJadi::find($this->selectedProdukId);
        if (!$produk) {
            $this->flash('Produk tidak ditemukan.', 'danger');
            return;
        }

        $reseps = Resep::with('bahanBaku')
            ->where('produk_jadi_id', $this->selectedProdukId)
            ->get();

        if ($reseps->isEmpty()) {
            $this->flash('Produk ini belum memiliki resep. Tambahkan resep terlebih dahulu.', 'danger');
            return;
        }

        // Validasi stok sebelum transaksi (early exit)
        foreach ($reseps as $r) {
            $kebutuhan = $r->kebutuhan_satuan * $this->jumlahMasak;
            if ($r->bahanBaku->stok < $kebutuhan) {
                $this->flash(
                    "Stok {$r->bahanBaku->nama_bahan} tidak cukup! " .
                    "Dibutuhkan: " . number_format($kebutuhan, 2) . " {$r->bahanBaku->satuan}, " .
                    "Tersedia: " . number_format($r->bahanBaku->stok, 2) . " {$r->bahanBaku->satuan}.",
                    'danger'
                );
                return;
            }
        }

        try {
            DB::transaction(function () use ($reseps, $produk) {
                // 1. Potong stok bahan baku
                foreach ($reseps as $r) {
                    $dipakai = $r->kebutuhan_satuan * $this->jumlahMasak;
                    $bahan   = BahanBaku::lockForUpdate()->find($r->bahan_baku_id);

                    if ($bahan->stok < $dipakai) {
                        throw new \Exception(
                            "Stok {$bahan->nama_bahan} tidak mencukupi saat eksekusi."
                        );
                    }

                    $bahan->decrement('stok', $dipakai);
                }

                // 2. Tambah stok produk jadi
                $produk->increment('stok', $this->jumlahMasak);
            });

            $this->flash(
                "✅ Produksi {$this->jumlahMasak} {$produk->nama_produk} berhasil! Stok etalase bertambah.",
                'success'
            );

            // Refresh kalkulasi
            $this->kalkulasi  = $this->hitungKalkulasi($this->selectedProdukId);
            $this->jumlahMasak = 1;

        } catch (\Exception $e) {
            $this->flash('❌ Produksi dibatalkan (Rollback): ' . $e->getMessage(), 'danger');
        }
    }

    // ── Helper ────────────────────────────────────
    private function flash(string $msg, string $type = 'success'): void
    {
        $this->flashMessage = $msg;
        $this->flashType    = $type;
    }

    // ── Render ────────────────────────────────────
    public function render()
    {
        $produkList = ProdukJadi::withCount('resep')->orderBy('nama_produk')->get();

        $selectedProduk = null;
        $resepDetail    = collect();

        if ($this->selectedProdukId) {
            $selectedProduk = ProdukJadi::find($this->selectedProdukId);
            $resepDetail    = Resep::with('bahanBaku')
                ->where('produk_jadi_id', $this->selectedProdukId)
                ->get();

            if (empty($this->kalkulasi) && $resepDetail->isNotEmpty()) {
                $this->kalkulasi = $this->hitungKalkulasi($this->selectedProdukId);
            }
        }

        return view('livewire.dapur-index', compact(
            'produkList',
            'selectedProduk',
            'resepDetail',
        ))->layout('layouts.erp', ['title' => 'Dapur — Mini ERP']);
    }
}
