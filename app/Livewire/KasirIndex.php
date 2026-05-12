<?php

namespace App\Livewire;

use App\Models\DetailTransaksi;
use App\Models\ProdukJadi;
use App\Models\Resep;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class KasirIndex extends Component
{
    // ── Cart ── format: [ "produkId" => [...] ] ─────
    public array  $cart             = [];
    public string $search           = '';
    public string $metodePembayaran = 'tunai';
    public string $nominalBayar     = '';
    public string $flashMessage     = '';
    public string $flashType        = 'success';

    #[On('global-search')]
    public function handleGlobalSearch(string $search = ''): void
    {
        $this->search = $search;
    }

    // ── Tambah ke keranjang (atau increment qty) ────
    public function tambahKeKeranjang(int $id): void
    {
        $p   = ProdukJadi::find($id);
        $key = "p{$id}";

        if (!$p || $p->stok <= 0) return;

        if (isset($this->cart[$key])) {
            if ($this->cart[$key]['qty'] < $p->stok) {
                $this->cart[$key]['qty']++;
            }
        } else {
            $this->cart[$key] = [
                'produk_id' => $id,
                'nama'      => $p->nama_produk,
                'harga'     => $p->harga_jual,
                'qty'       => 1,
                'stok_max'  => $p->stok,
            ];
        }
    }

    public function kurangiQty(string $key): void
    {
        if (!isset($this->cart[$key])) return;
        if ($this->cart[$key]['qty'] > 1) {
            $this->cart[$key]['qty']--;
        } else {
            unset($this->cart[$key]);
        }
    }

    public function hapusItem(string $key): void
    {
        unset($this->cart[$key]);
    }

    public function kosongkanKeranjang(): void
    {
        $this->cart            = [];
        $this->nominalBayar    = '';
    }

    // ── Totals ────────────────────────────────────
    public function getTotalPendapatanProperty(): int
    {
        return (int) collect($this->cart)->sum(fn($i) => $i['harga'] * $i['qty']);
    }

    public function getTotalItemProperty(): int
    {
        return (int) collect($this->cart)->sum(fn($i) => $i['qty']);
    }

    public function getKembalianProperty(): int
    {
        $nominal = (int) str_replace(['.', ','], '', $this->nominalBayar);
        $tagihan = $this->getTotalPendapatanProperty();
        $kembalian = $nominal - $tagihan;
        return $kembalian > 0 ? $kembalian : 0;
    }

    // ── HPP Calculation ───────────────────────────
    /**
     * PRD §8.2 Static HPP Rule:
     * HPP per produk = Σ (kebutuhan_satuan × harga_beli_persatuan) semua bahan resep
     * Total HPP = Σ (HPP per produk × qty)
     */
    private function hitungTotalHPP(): int
    {
        $total = 0;
        foreach ($this->cart as $item) {
            $reseps = Resep::with('bahanBaku')
                ->where('produk_jadi_id', $item['produk_id'])
                ->get();
            $hppSatuan = $reseps->sum(
                fn($r) => $r->kebutuhan_satuan * $r->bahanBaku->harga_beli_persatuan
            );
            $total += (int) round($hppSatuan * $item['qty']);
        }
        return $total;
    }

    // ── Checkout ──────────────────────────────────
    public function simpanTransaksi(): void
    {
        if (empty($this->cart)) {
            $this->flash('Keranjang masih kosong!', 'danger');
            return;
        }

        $nominal         = (int) str_replace(['.', ','], '', $this->nominalBayar);
        $totalPendapatan = $this->getTotalPendapatanProperty();

        if ($this->metodePembayaran === 'tunai' && $nominal < $totalPendapatan) {
            $this->flash('Nominal uang bayar kurang dari total belanja!', 'danger');
            return;
        }

        try {
            DB::transaction(function () use ($totalPendapatan) {
                $hpp = $this->hitungTotalHPP();

                $trx = Transaksi::create([
                    'metode_pembayaran' => $this->metodePembayaran,
                    'total_pendapatan'  => $totalPendapatan,
                    'total_hpp'         => $hpp,
                    'tanggal_transaksi' => now(),
                ]);

                foreach ($this->cart as $item) {
                    $produk = ProdukJadi::lockForUpdate()->findOrFail($item['produk_id']);

                    if ($produk->stok < $item['qty']) {
                        throw new \Exception(
                            "Stok {$produk->nama_produk} tidak cukup. Tersedia: {$produk->stok} pcs."
                        );
                    }

                    DetailTransaksi::create([
                        'transaksi_id'   => $trx->id,
                        'produk_jadi_id' => $item['produk_id'],
                        'qty'            => $item['qty'],
                        'subtotal'       => $item['harga'] * $item['qty'],
                    ]);

                    $produk->decrement('stok', $item['qty']);
                }
            });

            $labaKotor = $this->getTotalPendapatanProperty() - $this->hitungTotalHPP();
            $this->flash(
                '✅ Transaksi tersimpan! Laba Kotor: Rp' . number_format($labaKotor, 0, ',', '.'),
                'success'
            );
            $this->kosongkanKeranjang();

        } catch (\Exception $e) {
            $this->flash('❌ Rollback! ' . $e->getMessage(), 'danger');
        }
    }

    private function flash(string $msg, string $type = 'success'): void
    {
        $this->flashMessage = $msg;
        $this->flashType    = $type;
    }

    public function render()
    {
        $query = ProdukJadi::where('stok', '>', 0)->orderBy('nama_produk');
        if ($this->search) {
            $query->where('nama_produk', 'like', '%' . $this->search . '%');
        }
        $produkList = $query->get();

        $totalPendapatan = $this->getTotalPendapatanProperty();
        $totalItem       = $this->getTotalItemProperty();

        return view('livewire.kasir-index', compact('produkList', 'totalPendapatan', 'totalItem'))
            ->layout('layouts.erp', ['title' => 'Kasir / POS — Mini ERP']);
    }
}
