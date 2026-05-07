<div x-data>
    {{-- Flash --}}
    @if($flashMessage)
    <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)"
         x-transition:leave="transition ease-in duration-300" x-transition:leave-end="opacity-0"
         class="fixed top-5 right-5 z-[999] max-w-sm px-5 py-3 rounded-xl shadow-lg text-white text-sm font-medium
                {{ $flashType==='success' ? 'bg-emerald-500' : 'bg-red-500' }}">
        {{ $flashMessage }}
    </div>
    @endif

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#8B5E3C]">🍳 Dapur</h1>
            <p class="text-sm text-gray-400 mt-0.5">Manajemen resep & kalkulator produksi</p>
        </div>
    </div>

    {{-- SPLIT LAYOUT --}}
    <div class="grid grid-cols-3 gap-6 items-start">

        {{-- ═══════════════════════════════════════ --}}
        {{-- PANEL KIRI — Daftar Produk Jadi (1/3)  --}}
        {{-- ═══════════════════════════════════════ --}}
        <div class="col-span-1">
            <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-[#8B5E3C] uppercase tracking-wide">Produk Jadi</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Klik produk untuk melihat resep</p>
                </div>

                <div class="divide-y divide-gray-50">
                    @forelse($produkList as $produk)
                    <button wire:click="pilihProduk({{ $produk->id }})"
                            class="w-full text-left px-5 py-4 flex items-center justify-between hover:bg-[#fdf6ec] transition-colors duration-150
                                   {{ $selectedProdukId === $produk->id ? 'bg-[#fdf0e0] border-l-4 border-[#D4831F]' : 'border-l-4 border-transparent' }}">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $produk->nama_produk }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Stok: <span class="font-medium text-gray-600">{{ number_format($produk->stok) }}</span> •
                                {{ $produk->resep_count }} bahan
                            </p>
                        </div>
                        <div class="shrink-0 ml-3">
                            @if($produk->resep_count > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Resep ✓</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">No Resep</span>
                            @endif
                        </div>
                    </button>
                    @empty
                    <div class="px-5 py-10 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                        <p class="text-xs">Belum ada produk jadi.<br>Tambah di menu Stok Gudang.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- PANEL KANAN — Resep & Kalkulator (2/3)         --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div class="col-span-2 space-y-5">

            @if(!$selectedProdukId)
            {{-- Empty State --}}
            <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm flex flex-col items-center justify-center py-24 text-center">
                <div class="w-16 h-16 bg-[#fdf0e0] rounded-full flex items-center justify-center mb-4">
                    <span class="text-3xl">🍳</span>
                </div>
                <h3 class="text-base font-semibold text-gray-700 mb-1">Pilih produk untuk memulai</h3>
                <p class="text-sm text-gray-400">Klik salah satu produk di panel kiri untuk melihat resep dan kalkulator produksi.</p>
            </div>

            @else
            {{-- Produk Header --}}
            <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm px-6 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-[#8B5E3C]">{{ $selectedProduk->nama_produk }}</h2>
                        <p class="text-sm text-gray-400 mt-0.5">
                            Harga jual: <span class="font-semibold text-gray-700">Rp{{ number_format($selectedProduk->harga_jual,0,',','.') }}</span> •
                            Stok etalase: <span class="font-semibold text-gray-700">{{ $selectedProduk->stok }} pcs</span>
                        </p>
                    </div>
                    @if(!empty($kalkulasi['max']))
                    <div class="text-right">
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Estimasi Maks. Produksi</p>
                        <p class="text-3xl font-bold text-[#D4831F]">{{ $kalkulasi['max'] }}</p>
                        <p class="text-xs text-gray-400">pcs bisa dibuat sekarang</p>
                    </div>
                    @endif
                </div>

                {{-- Bottleneck Info --}}
                @if(!empty($kalkulasi['bottleneck']) && $kalkulasi['max'] >= 0)
                <div class="mt-4 flex items-center gap-2 px-4 py-2.5 rounded-xl
                            {{ $kalkulasi['max'] === 0 ? 'bg-red-50 border border-red-200' : 'bg-amber-50 border border-amber-200' }}">
                    <svg class="w-4 h-4 {{ $kalkulasi['max'] === 0 ? 'text-red-500' : 'text-amber-500' }} shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    <p class="text-xs {{ $kalkulasi['max'] === 0 ? 'text-red-700' : 'text-amber-800' }}">
                        <span class="font-semibold">Bottleneck:</span>
                        {{ $kalkulasi['max'] === 0 ? 'Stok habis! Tidak bisa produksi.' : '' }}
                        Bahan pembatas: <span class="font-semibold">{{ $kalkulasi['bottleneck'] }}</span>
                    </p>
                </div>
                @endif
            </div>

            {{-- Daftar Resep --}}
            <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-[#8B5E3C] uppercase tracking-wide">📋 Daftar Resep</h3>
                    <span class="text-xs text-gray-400">{{ $resepDetail->count() }} bahan dibutuhkan per 1 pcs</span>
                </div>

                @if($resepDetail->isEmpty())
                <div class="px-6 py-8 text-center text-gray-400">
                    <p class="text-sm">Belum ada resep untuk produk ini.</p>
                    <p class="text-xs mt-1 text-gray-300">Fitur manajemen resep akan tersedia pada update berikutnya.</p>
                </div>
                @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Bahan Baku</th>
                            <th class="px-6 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Kebutuhan / pcs</th>
                            <th class="px-6 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Stok Tersedia</th>
                            <th class="px-6 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Kapasitas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($resepDetail as $r)
                        @php
                            $kapasitas = $r->kebutuhan_satuan > 0
                                ? (int) floor($r->bahanBaku->stok / $r->kebutuhan_satuan)
                                : 999;
                            $isBottleneck = isset($kalkulasi['bottleneck'])
                                && $kalkulasi['bottleneck'] === $r->bahanBaku->nama_bahan;
                        @endphp
                        <tr class="hover:bg-[#fdf6ec]/50 transition-colors {{ $isBottleneck ? 'bg-amber-50/60' : '' }}">
                            <td class="px-6 py-3.5 font-medium text-gray-800">
                                {{ $r->bahanBaku->nama_bahan }}
                                @if($isBottleneck)
                                    <span class="ml-2 text-[10px] font-bold text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">BOTTLENECK</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-right text-gray-600">
                                {{ number_format($r->kebutuhan_satuan, 2, ',', '.') }} {{ $r->bahanBaku->satuan }}
                            </td>
                            <td class="px-6 py-3.5 text-right {{ $r->bahanBaku->stok <= 0 ? 'text-red-500 font-semibold' : 'text-gray-600' }}">
                                {{ number_format($r->bahanBaku->stok, 2, ',', '.') }} {{ $r->bahanBaku->satuan }}
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <span class="font-bold {{ $kapasitas === 0 ? 'text-red-500' : ($kapasitas < 10 ? 'text-amber-600' : 'text-emerald-600') }}">
                                    {{ $kapasitas }} pcs
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            {{-- Simulasi Sisa Bahan --}}
            @if(!empty($kalkulasi['simulasi']))
            <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-[#8B5E3C] uppercase tracking-wide">🔢 Simulasi Sisa Bahan</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Jika memproduksi <span class="font-semibold text-[#D4831F]">{{ $jumlahMasak }} pcs</span></p>
                </div>
                <div class="px-6 py-4 space-y-3">
                    @foreach($kalkulasi['simulasi'] as $sim)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-700">{{ $sim['nama'] }}</span>
                            <span class="text-xs {{ $sim['cukup'] ? 'text-gray-500' : 'text-red-500 font-semibold' }}">
                                Sisa: {{ number_format($sim['sisa'], 2, ',', '.') }} {{ $sim['satuan'] }}
                                @if(!$sim['cukup']) ⚠️ Tidak cukup! @endif
                            </span>
                        </div>
                        @php
                            $pct = $sim['stok'] > 0 ? min(100, ($sim['sisa'] / $sim['stok']) * 100) : 0;
                            $pct = max(0, $pct);
                        @endphp
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full transition-all duration-500
                                        {{ !$sim['cukup'] ? 'bg-red-400' : ($pct < 30 ? 'bg-amber-400' : 'bg-emerald-400') }}"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- KALKULATOR EKSEKUSI --}}
            @if($resepDetail->isNotEmpty())
            <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm px-6 py-6">
                <h3 class="text-sm font-bold text-[#8B5E3C] uppercase tracking-wide mb-4">⚡ Eksekusi Produksi</h3>

                <div class="flex items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah Produksi Aktual</label>
                        <div class="flex items-center gap-2">
                            <button wire:click="$set('jumlahMasak', {{ max(1, $jumlahMasak - 1) }})"
                                    class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-all font-bold text-lg">−</button>
                            <input type="number" wire:model.live="jumlahMasak" min="1"
                                   max="{{ $kalkulasi['max'] ?? 9999 }}"
                                   class="w-24 text-center px-3 py-2 border border-gray-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] outline-none"/>
                            <button wire:click="$set('jumlahMasak', {{ $jumlahMasak + 1 }})"
                                    class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-all font-bold text-lg">+</button>
                            <span class="text-sm text-gray-400">pcs</span>
                        </div>
                    </div>

                    <div class="text-right">
                        @if(!empty($kalkulasi['max']))
                        <p class="text-xs text-gray-400 mb-2">Maks. estimasi: <span class="font-bold text-[#D4831F]">{{ $kalkulasi['max'] }} pcs</span></p>
                        @endif
                        <button wire:click="prosesProduksi"
                                wire:loading.attr="disabled"
                                wire:confirm="Yakin ingin memproses produksi {{ $jumlahMasak }} pcs {{ $selectedProduk->nama_produk }}? Stok bahan baku akan dipotong."
                                @class([
                                    'flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold shadow-md transition-all duration-200',
                                    'bg-[#D4831F] hover:bg-[#b86a16] text-white hover:shadow-lg' => empty($kalkulasi['simulasi']) || collect($kalkulasi['simulasi'])->every(fn($s) => $s['cukup']),
                                    'bg-gray-200 text-gray-400 cursor-not-allowed' => !empty($kalkulasi['simulasi']) && collect($kalkulasi['simulasi'])->contains(fn($s) => !$s['cukup']),
                                ])>
                            <div wire:loading wire:target="prosesProduksi">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </div>
                            <div wire:loading.remove wire:target="prosesProduksi">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            Proses Produksi
                        </button>
                    </div>
                </div>

                {{-- Warning jika bahan tidak cukup --}}
                @if(!empty($kalkulasi['simulasi']) && collect($kalkulasi['simulasi'])->contains(fn($s) => !$s['cukup']))
                <div class="mt-4 flex items-start gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
                    <svg class="w-4 h-4 text-red-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    <p class="text-xs text-red-700">
                        <span class="font-bold">Stok tidak cukup!</span>
                        Beberapa bahan tidak mencukupi untuk {{ $jumlahMasak }} pcs produksi.
                        Kurangi jumlah atau tambah stok bahan baku terlebih dahulu.
                    </p>
                </div>
                @endif
            </div>
            @endif

            @endif {{-- end selectedProdukId --}}
        </div>
    </div>
</div>
