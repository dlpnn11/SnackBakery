<div x-data>
    {{-- Flash --}}
    @if($flashMessage)
    <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)"
         x-transition:leave="transition ease-in duration-300" x-transition:leave-end="opacity-0"
         class="fixed top-5 right-5 z-[999] px-5 py-3 rounded-xl shadow-lg text-white text-sm font-medium
                {{ $flashType==='success' ? 'bg-emerald-500' : 'bg-red-500' }}">
        {{ $flashMessage }}
    </div>
    @endif

    <h1 class="text-xl md:text-2xl font-bold text-[#8B5E3C] mb-4 md:mb-6">Bahan Baku</h1>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5 mb-6 md:mb-7">
        <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm p-4 md:p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
            </div>
            <div>
                <p class="text-[10px] md:text-[11px] text-gray-400 font-medium uppercase tracking-wide">Total produk</p>
                <p class="text-lg md:text-xl font-bold text-gray-800">{{ number_format($totalItem) }} <span class="text-[10px] md:text-sm font-normal text-gray-400">Item</span></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm p-4 md:p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M16 6V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H2v13c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6h-6zm-6-2h4v2h-4V4z"/></svg>
            </div>
            <div>
                <p class="text-[10px] md:text-[11px] text-gray-400 font-medium uppercase tracking-wide">Total stok</p>
                <p class="text-lg md:text-xl font-bold text-gray-800">{{ number_format($totalStok,0,',','.') }} <span class="text-[10px] md:text-sm font-normal text-gray-400">Item</span></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm p-4 md:p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
            </div>
            <div>
                <p class="text-[10px] md:text-[11px] text-gray-400 font-medium uppercase tracking-wide">Total profil</p>
                <p class="text-lg md:text-xl font-bold text-gray-800">Rp{{ number_format($totalNilai,0,',','.') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm p-4 md:p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
            </div>
            <div>
                <p class="text-[10px] md:text-[11px] text-gray-400 font-medium uppercase tracking-wide">Produk kadaluarsa</p>
                <p class="text-lg md:text-xl font-bold text-gray-800">{{ $totalExpiring }} <span class="text-[10px] md:text-sm font-normal text-gray-400">Item</span></p>
            </div>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm overflow-hidden">
        {{-- Table Toolbar --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-4 md:px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-[#8B5E3C]">Daftar Bahan Baku</h2>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <button wire:click="openCreateModal"
                        class="flex items-center justify-center gap-2 bg-[#D4831F] hover:bg-[#b86a16] text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200 w-full md:w-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Produk
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="w-full overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap md:whitespace-normal">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-4 md:px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Nama Bahan</th>
                        <th class="px-4 md:px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Harga Satuan</th>
                        <th class="px-4 md:px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-[#D4831F] transition-colors select-none" wire:click="sortBy('stok')">
                            <span class="flex items-center gap-1">
                                Kuantitas
                                @if($sortField==='stok')
                                    <svg class="w-3 h-3 {{ $sortDirection==='asc' ? 'rotate-180' : '' }} transition-transform" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                @else
                                    <svg class="w-3 h-3 opacity-25" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                @endif
                            </span>
                        </th>
                        <th class="px-4 md:px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-[#D4831F] transition-colors select-none" wire:click="sortBy('tanggal_expired')">
                            <span class="flex items-center gap-1">
                                Tanggal Kadaluarsa
                                @if($sortField==='tanggal_expired')
                                    <svg class="w-3 h-3 {{ $sortDirection==='asc' ? 'rotate-180' : '' }} transition-transform" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                @else
                                    <svg class="w-3 h-3 opacity-25" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                @endif
                            </span>
                        </th>
                        <th class="px-4 md:px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 md:px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($bahanBakus as $bahan)
                        @php $st = \App\Livewire\BahanBakuIndex::getStatusBahan($bahan); @endphp
                        <tr class="hover:bg-[#fdf6ec]/70 transition-colors {{ $st['rowBg'] }}">
                            <td class="px-4 md:px-6 py-3 md:py-4 font-medium text-gray-800">{{ $bahan->nama_bahan }}</td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600">Rp{{ number_format($bahan->harga_beli_persatuan,0,',','.') }}</td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600">{{ number_format($bahan->stok,0,',','.') }} {{ ucfirst($bahan->satuan) }}</td>
                            <td class="px-4 md:px-6 py-3 md:py-4">
                                @if($bahan->tanggal_expired)
                                    @php
                                        $exp      = $bahan->tanggal_expired;
                                        $isPast   = now()->gt($exp);
                                        $diffDays = now()->diffInDays($exp, false);
                                        $months   = now()->diff($exp)->m;
                                        $days     = now()->diff($exp)->d;
                                    @endphp
                                    <span class="text-gray-700">{{ $exp->format('d/m/y') }}</span>
                                    @if($isPast)
                                        <span class="block text-xs font-semibold text-red-500 mt-0.5">Sudah kadaluarsa!</span>
                                    @else
                                        <span class="block text-xs mt-0.5 {{ $diffDays <= 7 ? 'text-red-500 font-semibold' : ($diffDays <= 30 ? 'text-amber-500' : 'text-gray-400') }}">
                                            ({{ $months > 0 ? $months.' bulan ' : '' }}{{ $days }} hari lagi)
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] md:text-[11px] font-bold tracking-wide {{ $st['bg'] }} {{ $st['text'] }}">
                                    {{ $st['label'] }}
                                </span>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4">
                                <div class="flex items-center justify-center gap-1 md:gap-1.5">
                                    <button wire:click="openEditModal({{ $bahan->id }})"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-[#D4831F] hover:bg-[#D4831F]/10 transition-all" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $bahan->id }})"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 md:px-6 py-10 md:py-16 text-center text-gray-400">
                                <svg class="w-10 h-10 md:w-12 md:h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <p class="font-medium text-xs md:text-sm">Belum ada data bahan baku</p>
                                <p class="text-[10px] md:text-xs mt-1">Klik "Tambah Produk" untuk mulai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($bahanBakus->hasPages())
        <div class="px-4 md:px-6 py-3 md:py-4 border-t border-gray-100 flex items-center justify-between">
            <button wire:click="previousPage" wire:loading.attr="disabled"
                    {{ $bahanBakus->onFirstPage() ? 'disabled' : '' }}
                    class="px-3 md:px-4 py-1.5 md:py-2 text-[11px] md:text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                Previous
            </button>
            <span class="text-[11px] md:text-sm text-gray-400">
                Page {{ $bahanBakus->currentPage() }} of {{ $bahanBakus->lastPage() }}
            </span>
            <button wire:click="nextPage" wire:loading.attr="disabled"
                    {{ $bahanBakus->hasMorePages() ? '' : 'disabled' }}
                    class="px-3 md:px-4 py-1.5 md:py-2 text-[11px] md:text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                Next
            </button>
        </div>
        @endif
    </div>

    {{-- CREATE / EDIT MODAL --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between px-6 py-4 md:py-5 border-b border-gray-100 shrink-0">
                <h3 class="text-sm md:text-base font-bold text-[#8B5E3C]">
                    {{ $editingId ? '✏️ Edit Bahan Baku' : '➕ Tambah Bahan Baku' }}
                </h3>
                <button wire:click="closeModal" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="overflow-y-auto overflow-x-hidden p-6 py-4 md:py-5 space-y-4 custom-scrollbar">
                <form wire:submit="save" id="bahanForm" class="space-y-4">
                    {{-- Nama Bahan --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Bahan <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="nama_bahan" placeholder="contoh: Tepung Terigu Protein Tinggi"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] outline-none transition-all"/>
                        @error('nama_bahan')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Jumlah & Satuan --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah Stok <span class="text-red-400">*</span></label>
                            <input type="number" step="0.01" wire:model="jumlah_input" placeholder="contoh: 5"
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] outline-none transition-all"/>
                            @error('jumlah_input')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Satuan Input <span class="text-red-400">*</span></label>
                            <select wire:model="satuan_input"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] outline-none transition-all">
                                <option value="gram">Gram</option>
                                <option value="kg">Kilogram (Kg) → disimpan gram</option>
                                <option value="ml">Mililiter (ml)</option>
                                <option value="liter">Liter → disimpan ml</option>
                                <option value="butir">Butir</option>
                                <option value="lembar">Lembar</option>
                                <option value="buah">Buah</option>
                            </select>
                        </div>
                    </div>

                    {{-- Harga Beli --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga Beli (per satuan input) <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-400">Rp</span>
                            <input type="number" wire:model="harga_input" placeholder="20000"
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] outline-none transition-all"/>
                        </div>
                        @error('harga_input')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        <p class="text-[11px] text-gray-400 mt-1">Sistem otomatis konversi ke harga per satuan terkecil.</p>
                    </div>

                    {{-- Minimum Stok --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Minimum Stok <span class="text-red-400">*</span></label>
                        <input type="number" step="0.01" wire:model="minimum_stok" placeholder="contoh: 500"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] outline-none transition-all"/>
                        @error('minimum_stok')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Tanggal Expired --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal Kadaluarsa <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <input type="date" wire:model="tanggal_expired"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] outline-none transition-all"/>
                        @error('tanggal_expired')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </form>
            </div>
            
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 shrink-0 bg-gray-50">
                <button type="button" wire:click="closeModal"
                        class="px-4 md:px-5 py-2 md:py-2.5 rounded-xl text-xs md:text-sm font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-100 transition-all">Batal</button>
                <button type="submit" form="bahanForm"
                        class="px-4 md:px-5 py-2 md:py-2.5 rounded-xl text-xs md:text-sm font-semibold text-white bg-[#D4831F] hover:bg-[#b86a16] shadow-md hover:shadow-lg transition-all">
                    {{ $editingId ? 'Perbarui Data' : 'Simpan' }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- DELETE MODAL --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeDeleteModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6 text-center">
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <h3 class="text-sm md:text-base font-bold text-gray-800 mb-2">Hapus Bahan Baku?</h3>
            <p class="text-[11px] md:text-sm text-gray-500 mb-6">Data yang dihapus tidak dapat dikembalikan.</p>
            <div class="flex justify-center gap-3">
                <button wire:click="closeDeleteModal"
                        class="px-4 md:px-5 py-2 md:py-2.5 rounded-xl text-xs md:text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition-all">Batal</button>
                <button wire:click="delete"
                        class="px-4 md:px-5 py-2 md:py-2.5 rounded-xl text-xs md:text-sm font-semibold text-white bg-red-500 hover:bg-red-600 shadow-md transition-all">Ya, Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
