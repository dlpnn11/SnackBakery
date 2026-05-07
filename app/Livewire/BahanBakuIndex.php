<?php

namespace App\Livewire;

use App\Models\BahanBaku;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class BahanBakuIndex extends Component
{
    use WithPagination;

    // ── Search & Sort ──────────────────────────────
    public string $search        = '';
    public string $sortField     = 'created_at';
    public string $sortDirection = 'desc';

    // ── Modal State ────────────────────────────────
    public bool  $showModal       = false;
    public bool  $showDeleteModal = false;
    public ?int  $editingId       = null;
    public ?int  $deletingId      = null;

    // ── Form Fields ────────────────────────────────
    public string $nama_bahan      = '';
    public string $satuan_input    = 'gram';
    public string $jumlah_input    = '';
    public string $harga_input     = '';
    public string $minimum_stok   = '';
    public string $tanggal_expired = '';

    // ── Flash ──────────────────────────────────────
    public string $flashMessage = '';
    public string $flashType    = 'success';

    /**
     * Peta konversi satuan → satuan terkecil di DB.
     * faktor: berapa unit terkecil per 1 satuan input.
     */
    private array $konversiMap = [
        'gram'   => ['faktor' => 1,    'satuan_db' => 'gram'],
        'kg'     => ['faktor' => 1000, 'satuan_db' => 'gram'],
        'ml'     => ['faktor' => 1,    'satuan_db' => 'ml'],
        'liter'  => ['faktor' => 1000, 'satuan_db' => 'ml'],
        'butir'  => ['faktor' => 1,    'satuan_db' => 'butir'],
        'lembar' => ['faktor' => 1,    'satuan_db' => 'lembar'],
        'buah'   => ['faktor' => 1,    'satuan_db' => 'buah'],
    ];

    // ── Validation ────────────────────────────────
    protected function rules(): array
    {
        return [
            'nama_bahan'      => 'required|string|max:255',
            'satuan_input'    => 'required|in:gram,kg,ml,liter,butir,lembar,buah',
            'jumlah_input'    => 'required|numeric|min:0.01',
            'harga_input'     => 'required|numeric|min:0',
            'minimum_stok'   => 'required|numeric|min:0',
            'tanggal_expired' => 'nullable|date',
        ];
    }

    protected function messages(): array
    {
        return [
            'nama_bahan.required'   => 'Nama bahan wajib diisi.',
            'jumlah_input.required' => 'Jumlah stok wajib diisi.',
            'jumlah_input.min'      => 'Jumlah stok minimal 0.01.',
            'harga_input.required'  => 'Harga beli wajib diisi.',
            'minimum_stok.required' => 'Minimum stok wajib diisi.',
        ];
    }

    // ── Reset pagination on search ────────────────
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

    // ── SORTING ───────────────────────────────────
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    // ── MODAL ACTIONS ─────────────────────────────
    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId  = null;
        $this->showModal  = true;
    }

    public function openEditModal(int $id): void
    {
        $bahan = BahanBaku::findOrFail($id);

        $this->editingId       = $bahan->id;
        $this->nama_bahan      = $bahan->nama_bahan;
        $this->satuan_input    = $bahan->satuan;
        $this->jumlah_input    = (string) $bahan->stok;
        $this->harga_input     = (string) $bahan->harga_beli_persatuan;
        $this->minimum_stok   = (string) $bahan->minimum_stok;
        $this->tanggal_expired = $bahan->tanggal_expired
            ? $bahan->tanggal_expired->format('Y-m-d')
            : '';

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

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

    // ── SAVE (Create / Update) ────────────────────
    public function save(): void
    {
        $this->validate();

        $map    = $this->konversiMap[$this->satuan_input];
        $faktor = $map['faktor'];
        $satuanDb = $map['satuan_db'];

        // ── Business Rule: Absolute Unit Rule ─────
        // Semua nilai disimpan dalam satuan terkecil (gram / ml / dst.)
        $stokDb    = (float) $this->jumlah_input * $faktor;
        $minStokDb = (int)   round((float) $this->minimum_stok * $faktor);

        // Harga per satuan terkecil
        // Jika user input 1 Kg seharga 20.000, maka per gram = 20.000 / 1000 = 20
        $hargaPerUnit = $faktor > 1
            ? (int) round((float) $this->harga_input / $faktor)
            : (int) $this->harga_input;

        $data = [
            'nama_bahan'           => $this->nama_bahan,
            'satuan'               => $satuanDb,
            'harga_beli_persatuan' => $hargaPerUnit,
            'stok'                 => $stokDb,
            'minimum_stok'        => $minStokDb,
            'tanggal_expired'      => $this->tanggal_expired ?: null,
        ];

        if ($this->editingId) {
            BahanBaku::findOrFail($this->editingId)->update($data);
            $this->setFlash('Data bahan baku berhasil diperbarui!', 'success');
        } else {
            BahanBaku::create($data);
            $this->setFlash('Bahan baku baru berhasil ditambahkan!', 'success');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    // ── DELETE ────────────────────────────────────
    public function delete(): void
    {
        if ($this->deletingId) {
            BahanBaku::findOrFail($this->deletingId)->delete();
            $this->setFlash('Data bahan baku berhasil dihapus.', 'danger');
        }
        $this->showDeleteModal = false;
        $this->deletingId      = null;
    }

    // ── STATUS BADGE LOGIC ────────────────────────
    /**
     * Mengembalikan array [label, bg, text, rowBg] berdasarkan PRD:
     * ≥ 3× min → Aman (hijau)
     * ≥ 2× min → Cukup (biru)
     * ≥ 1× min → Menipis (kuning)
     *  < 1× min → Kritis (merah)
     *    = 0    → Habis (merah + highlight baris)
     */
    public static function getStatusBahan(BahanBaku $bahan): array
    {
        if ($bahan->stok <= 0) {
            return [
                'label' => 'Habis',
                'bg'    => 'bg-red-100',
                'text'  => 'text-red-700',
                'rowBg' => 'bg-red-50/70',
            ];
        }

        if ($bahan->minimum_stok <= 0) {
            return [
                'label' => 'Aman',
                'bg'    => 'bg-emerald-100',
                'text'  => 'text-emerald-700',
                'rowBg' => '',
            ];
        }

        $rasio = $bahan->stok / $bahan->minimum_stok;

        if ($rasio >= 3) {
            return ['label' => 'Aman',    'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'rowBg' => ''];
        }
        if ($rasio >= 2) {
            return ['label' => 'Cukup',   'bg' => 'bg-blue-100',    'text' => 'text-blue-700',    'rowBg' => ''];
        }
        if ($rasio >= 1) {
            return ['label' => 'Menipis', 'bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'rowBg' => ''];
        }

        return ['label' => 'Kritis',  'bg' => 'bg-red-100',     'text' => 'text-red-700',     'rowBg' => ''];
    }

    // ── HELPERS ───────────────────────────────────
    private function resetForm(): void
    {
        $this->reset([
            'nama_bahan', 'jumlah_input', 'harga_input',
            'minimum_stok', 'tanggal_expired',
        ]);
        $this->satuan_input = 'gram';
        $this->resetValidation();
    }

    private function setFlash(string $message, string $type = 'success'): void
    {
        $this->flashMessage = $message;
        $this->flashType    = $type;
    }

    // ── RENDER ────────────────────────────────────
    public function render()
    {
        $query = BahanBaku::query();

        if ($this->search) {
            $query->where('nama_bahan', 'like', '%' . $this->search . '%');
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        $bahanBakus = $query->paginate(10);

        // Summary stats (dihitung dari semua data, bukan hanya page aktif)
        $all           = BahanBaku::all();
        $totalItem     = $all->count();
        $totalStok     = $all->sum('stok');
        $totalNilai    = $all->sum(fn($b) => $b->stok * $b->harga_beli_persatuan);
        $totalExpiring = $all->filter(fn($b) =>
            $b->tanggal_expired &&
            now()->lte($b->tanggal_expired) &&
            now()->diffInDays($b->tanggal_expired) <= 30
        )->count();

        return view('livewire.bahan-baku-index', compact(
            'bahanBakus',
            'totalItem',
            'totalStok',
            'totalNilai',
            'totalExpiring',
        ))->layout('layouts.erp', ['title' => 'Bahan Baku — Mini ERP']);
    }
}
