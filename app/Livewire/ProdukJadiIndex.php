<?php

namespace App\Livewire;

use App\Models\BahanBaku;
use App\Models\ProdukJadi;
use App\Models\Resep;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ProdukJadiIndex extends Component
{
    use WithPagination;

    // ── Search & Sort ──────────────────────────────
    public string $search        = '';
    public string $sortField     = 'created_at';
    public string $sortDirection = 'desc';

    // ── Modal State ────────────────────────────────
    public bool $showModal       = false;
    public bool $showDeleteModal = false;
    public ?int $editingId       = null;
    public ?int $deletingId      = null;

    // ── Form: Info Produk ──────────────────────────
    public string $nama_produk   = '';
    public string $harga_jual    = '';
    public string $stok          = '';
    public string $minimum_stok  = '';

    // ── Form: Resep Dinamis ────────────────────────
    // Array of ['bahan_baku_id' => '', 'kebutuhan_satuan' => '']
    public array $resepRows = [];

    // ── Flash ──────────────────────────────────────
    public string $flashMessage = '';
    public string $flashType    = 'success';

    // ── Validation ────────────────────────────────
    protected function rules(): array
    {
        return [
            'nama_produk'                    => 'required|string|max:255',
            'harga_jual'                     => 'required|integer|min:0',
            'stok'                           => 'required|integer|min:0',
            'minimum_stok'                   => 'required|integer|min:0',
            'resepRows.*.bahan_baku_id'      => 'nullable|exists:bahan_baku,id',
            'resepRows.*.kebutuhan_satuan'   => 'nullable|numeric|min:0.01',
        ];
    }

    protected function messages(): array
    {
        return [
            'nama_produk.required'  => 'Nama produk wajib diisi.',
            'harga_jual.required'   => 'Harga jual wajib diisi.',
            'harga_jual.min'        => 'Harga jual tidak boleh negatif.',
            'stok.required'         => 'Stok wajib diisi.',
            'minimum_stok.required' => 'Minimum stok wajib diisi.',
        ];
    }

    // ── Global Search Listener ─────────────────
    #[On('global-search')]
    public function handleGlobalSearch(string $value): void
    {
        $this->search = $value;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ── Sorting ────────────────────────────────────
    public function sortBy(string $field): void
    {
        $this->sortField     = $this->sortField === $field
            ? $this->sortField
            : $field;
        $this->sortDirection = ($this->sortField === $field && $this->sortDirection === 'asc')
            ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    // ── Resep Dinamis: tambah/hapus baris ─────────
    public function tambahBahanResep(): void
    {
        $this->resepRows[] = ['bahan_baku_id' => '', 'kebutuhan_satuan' => ''];
    }

    public function hapusBahanResep(int $index): void
    {
        array_splice($this->resepRows, $index, 1);
        $this->resepRows = array_values($this->resepRows);
    }

    // ── Modal: Create ──────────────────────────────
    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->resepRows = [['bahan_baku_id' => '', 'kebutuhan_satuan' => '']];
        $this->showModal = true;
    }

    // ── Modal: Edit ────────────────────────────────
    public function openEditModal(int $id): void
    {
        $produk = ProdukJadi::with('resep.bahanBaku')->findOrFail($id);

        $this->editingId    = $produk->id;
        $this->nama_produk  = $produk->nama_produk;
        $this->harga_jual   = (string) $produk->harga_jual;
        $this->stok         = (string) $produk->stok;
        $this->minimum_stok = (string) $produk->minimum_stok;

        $this->resepRows = $produk->resep->map(fn($r) => [
            'bahan_baku_id'    => (string) $r->bahan_baku_id,
            'kebutuhan_satuan' => (string) $r->kebutuhan_satuan,
        ])->toArray();

        if (empty($this->resepRows)) {
            $this->resepRows = [['bahan_baku_id' => '', 'kebutuhan_satuan' => '']];
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    // ── SAVE (Create / Update) ────────────────────
    public function save(): void
    {
        $this->validate();

        // Filter baris resep yang valid (bahan_baku_id terisi)
        $validResep = collect($this->resepRows)
            ->filter(fn($r) => !empty($r['bahan_baku_id']) && !empty($r['kebutuhan_satuan']))
            ->values();

        try {
            DB::transaction(function () use ($validResep) {
                $produkData = [
                    'nama_produk'  => $this->nama_produk,
                    'harga_jual'   => (int) $this->harga_jual,
                    'stok'         => (int) $this->stok,
                    'minimum_stok' => (int) $this->minimum_stok,
                ];

                if ($this->editingId) {
                    $produk = ProdukJadi::findOrFail($this->editingId);
                    $produk->update($produkData);
                } else {
                    $produk = ProdukJadi::create($produkData);
                }

                // Sync resep: hapus lama, buat baru
                Resep::where('produk_jadi_id', $produk->id)->delete();

                foreach ($validResep as $row) {
                    Resep::create([
                        'produk_jadi_id'   => $produk->id,
                        'bahan_baku_id'    => (int) $row['bahan_baku_id'],
                        'kebutuhan_satuan' => (float) $row['kebutuhan_satuan'],
                    ]);
                }
            });

            $this->setFlash(
                $this->editingId
                    ? 'Produk jadi berhasil diperbarui!'
                    : 'Produk jadi baru berhasil ditambahkan!',
                'success'
            );

        } catch (\Exception $e) {
            $this->setFlash('Terjadi kesalahan: ' . $e->getMessage(), 'danger');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    // ── Delete ────────────────────────────────────
    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId      = null;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            DB::transaction(function () {
                Resep::where('produk_jadi_id', $this->deletingId)->delete();
                ProdukJadi::findOrFail($this->deletingId)->delete();
            });
            $this->setFlash('Produk jadi berhasil dihapus.', 'danger');
        }
        $this->showDeleteModal = false;
        $this->deletingId      = null;
    }

    // ── Status Badge ──────────────────────────────
    public static function getStatus(ProdukJadi $p): array
    {
        if ($p->stok <= 0) {
            return ['label' => 'Habis',   'bg' => 'bg-red-100',     'text' => 'text-red-700',     'rowBg' => 'bg-red-50/60'];
        }
        if ($p->minimum_stok <= 0) {
            return ['label' => 'Aman',    'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'rowBg' => ''];
        }
        $rasio = $p->stok / $p->minimum_stok;
        if ($rasio >= 3) return ['label' => 'Aman',    'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'rowBg' => ''];
        if ($rasio >= 2) return ['label' => 'Cukup',   'bg' => 'bg-blue-100',    'text' => 'text-blue-700',    'rowBg' => ''];
        if ($rasio >= 1) return ['label' => 'Menipis', 'bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'rowBg' => ''];
        return             ['label' => 'Kritis',  'bg' => 'bg-red-100',     'text' => 'text-red-700',     'rowBg' => ''];
    }

    // ── Helpers ───────────────────────────────────
    private function resetForm(): void
    {
        $this->reset(['nama_produk', 'harga_jual', 'stok', 'minimum_stok', 'resepRows']);
        $this->resetValidation();
    }

    private function setFlash(string $msg, string $type = 'success'): void
    {
        $this->flashMessage = $msg;
        $this->flashType    = $type;
    }

    // ── Render ────────────────────────────────────
    public function render()
    {
        $query = ProdukJadi::query();
        if ($this->search) {
            $query->where('nama_produk', 'like', '%' . $this->search . '%');
        }
        $query->orderBy($this->sortField, $this->sortDirection);
        $produkList = $query->paginate(10);

        $all          = ProdukJadi::all();
        $totalItem    = $all->count();
        $totalStok    = $all->sum('stok');
        $totalAset    = $all->sum(fn($p) => $p->stok * $p->harga_jual);
        $totalKritis  = $all->filter(fn($p) => in_array(
            self::getStatus($p)['label'], ['Kritis', 'Habis']
        ))->count();

        $bahanList = BahanBaku::orderBy('nama_bahan')->get();

        return view('livewire.produk-jadi-index', compact(
            'produkList', 'totalItem', 'totalStok', 'totalAset', 'totalKritis', 'bahanList'
        ))->layout('layouts.erp', ['title' => 'Produk Jadi — Mini ERP']);
    }
}
