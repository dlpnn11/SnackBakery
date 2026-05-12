<div x-data>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-[#8B5E3C]">📊 Dashboard</h1>
            <p class="text-xs md:text-sm text-gray-400 mt-0.5">Ringkasan performa bisnis Snack & Bakery</p>
        </div>
        {{-- <p class="text-[11px] md:text-xs text-gray-400 bg-white border border-gray-100 px-3 md:px-4 py-2 rounded-xl shadow-sm self-start md:self-auto">
            📅 {{ now()->isoFormat('dddd, D MMMM YYYY') }}
        </p> --}}
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- 4 SUMMARY CARDS                           --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5 mb-6 md:mb-7">

        {{-- Card 1: Produk Jadi --}}
        <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm p-4 md:p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
            </div>
            <div>
                <p class="text-[10px] md:text-[11px] text-gray-400 font-semibold uppercase tracking-wide">Produk Jadi</p>
                <p class="text-xl md:text-2xl font-bold text-gray-800">{{ $jumlahProdukJadi }}</p>
                <p class="text-[10px] md:text-xs text-gray-400">jenis produk</p>
            </div>
        </div>

        {{-- Card 2: Bahan Baku --}}
        <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm p-4 md:p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <p class="text-[10px] md:text-[11px] text-gray-400 font-semibold uppercase tracking-wide">Bahan Baku</p>
                <p class="text-xl md:text-2xl font-bold text-gray-800">{{ $jumlahBahanBaku }}</p>
                <p class="text-[10px] md:text-xs text-gray-400">jenis bahan</p>
            </div>
        </div>

        {{-- Card 3: Total Pendapatan --}}
        <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm p-4 md:p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
            </div>
            <div>
                <p class="text-[10px] md:text-[11px] text-gray-400 font-semibold uppercase tracking-wide">Total Pendapatan</p>
                <p class="text-lg md:text-xl font-bold text-gray-800">Rp{{ number_format($totalPendapatan,0,',','.') }}</p>
                <p class="text-[10px] md:text-xs text-gray-400">dari semua transaksi</p>
            </div>
        </div>

        {{-- Card 4: Laba Kotor Bersih --}}
        <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm p-4 md:p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl {{ $labaKotorBersih >= 0 ? 'bg-[#fdf0e0]' : 'bg-red-100' }} flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 {{ $labaKotorBersih >= 0 ? 'text-[#D4831F]' : 'text-red-500' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/></svg>
            </div>
            <div>
                <p class="text-[10px] md:text-[11px] text-gray-400 font-semibold uppercase tracking-wide">Laba Kotor Bersih</p>
                <p class="text-lg md:text-xl font-bold {{ $labaKotorBersih >= 0 ? 'text-[#D4831F]' : 'text-red-500' }}">
                    Rp{{ number_format($labaKotorBersih,0,',','.') }}
                </p>
                <p class="text-[10px] md:text-xs text-gray-400">pendapatan − modal</p>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- TABEL RIWAYAT TRANSAKSI                   --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm overflow-hidden">
        <div class="px-4 md:px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm md:text-base font-bold text-[#8B5E3C]">🧾 Riwayat Transaksi</h2>
            <p class="text-[11px] md:text-xs text-gray-400 mt-0.5">Klik "Lihat Detail" untuk membuka struk kasir</p>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-sm min-w-[700px] lg:min-w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-4 md:px-6 py-3 text-left text-[10px] md:text-[11px] font-semibold text-gray-400 uppercase tracking-wider">ID Transaksi</th>
                        <th class="px-4 md:px-6 py-3 text-left text-[10px] md:text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 md:px-6 py-3 text-left text-[10px] md:text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Metode</th>
                        <th class="px-4 md:px-6 py-3 text-right text-[10px] md:text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Transaksi</th>
                        <th class="px-4 md:px-6 py-3 text-right text-[10px] md:text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Laba Kotor</th>
                        <th class="px-4 md:px-6 py-3 text-center text-[10px] md:text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transaksiList as $trx)
                    <tr class="hover:bg-[#fdf6ec]/70 transition-colors">
                        <td class="px-4 md:px-6 py-3 md:py-4">
                            <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-lg">#{{ str_pad($trx->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600">
                            <p class="font-medium text-xs md:text-sm">{{ $trx->tanggal_transaksi->format('d M Y') }}</p>
                            <p class="text-[10px] md:text-xs text-gray-400">{{ $trx->tanggal_transaksi->format('H:i') }} WIB</p>
                        </td>
                        <td class="px-4 md:px-6 py-3 md:py-4">
                            @if($trx->metode_pembayaran === 'tunai')
                                <span class="inline-flex items-center gap-1 md:gap-1.5 px-2 md:px-3 py-1 rounded-full text-[10px] md:text-xs font-semibold bg-emerald-100 text-emerald-700">
                                    💵 Tunai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 md:gap-1.5 px-2 md:px-3 py-1 rounded-full text-[10px] md:text-xs font-semibold bg-blue-100 text-blue-700">
                                    📱 QRIS
                                </span>
                            @endif
                        </td>
                        <td class="px-4 md:px-6 py-3 md:py-4 text-right font-semibold text-gray-800 text-xs md:text-sm">
                            Rp{{ number_format($trx->total_pendapatan,0,',','.') }}
                        </td>
                        <td class="px-4 md:px-6 py-3 md:py-4 text-right">
                            @php $laba = $trx->total_pendapatan - $trx->total_hpp; @endphp
                            <span class="font-semibold text-xs md:text-sm {{ $laba >= 0 ? 'text-[#D4831F]' : 'text-red-500' }}">
                                Rp{{ number_format($laba,0,',','.') }}
                            </span>
                        </td>
                        <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                            <button wire:click="lihatDetail({{ $trx->id }})"
                                    class="inline-flex items-center gap-1.5 px-2 md:px-3 py-1.5 rounded-lg text-[10px] md:text-xs font-semibold border border-[#D4831F]/40 text-[#D4831F] hover:bg-[#D4831F] hover:text-white transition-all duration-200">
                                <svg class="w-3.5 h-3.5 hidden sm:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 md:px-6 py-10 md:py-14 text-center text-gray-400">
                            <svg class="w-10 h-10 md:w-12 md:h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                            <p class="font-medium text-xs md:text-sm">Belum ada transaksi tercatat</p>
                            <p class="text-[10px] md:text-xs mt-1">Buka modul Kasir untuk mencatat penjualan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transaksiList->hasPages())
        <div class="px-4 md:px-6 py-3 md:py-4 border-t border-gray-100 flex items-center justify-between">
            <button wire:click="previousPage" {{ $transaksiList->onFirstPage() ? 'disabled' : '' }}
                    class="px-3 md:px-4 py-1.5 md:py-2 text-[11px] md:text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all">Previous</button>
            <span class="text-[11px] md:text-sm text-gray-400">Page {{ $transaksiList->currentPage() }} of {{ $transaksiList->lastPage() }}</span>
            <button wire:click="nextPage" {{ $transaksiList->hasMorePages() ? '' : 'disabled' }}
                    class="px-3 md:px-4 py-1.5 md:py-2 text-[11px] md:text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all">Next</button>
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- MODAL STRUK / INVOICE                     --}}
    {{-- ══════════════════════════════════════════ --}}
    @if($showStruk && $activeStruk)
    <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="tutupStruk"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden mt-10 md:mt-0 max-h-[90vh] flex flex-col">

            {{-- Struk Header --}}
            <div class="shrink-0 bg-gradient-to-r from-[#8B5E3C] to-[#D4831F] px-6 pt-6 pb-8 text-white text-center relative">
                {{-- Close X --}}
                <button wire:click="tutupStruk" class="absolute top-4 right-4 p-1.5 rounded-full bg-white/20 text-white hover:bg-white/30 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold tracking-wide">Snack & Bakery</h3>
                <p class="text-white/70 text-xs mt-0.5">Nota Penjualan</p>
            </div>

            {{-- Body Scrollable Area --}}
            <div class="overflow-y-auto overflow-x-hidden p-6 pt-0 -mt-4 relative z-10 custom-scrollbar">
                {{-- Struk Info --}}
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 md:px-5 py-4 mb-4">
                    <div class="grid grid-cols-2 gap-y-2 text-xs">
                        <div>
                            <p class="text-gray-400">No. Transaksi</p>
                            <p class="font-bold text-gray-800 font-mono">#{{ str_pad($activeStruk->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">Metode Bayar</p>
                            <p class="font-bold text-gray-800 capitalize">{{ $activeStruk->metode_pembayaran === 'tunai' ? '💵 Tunai' : '📱 QRIS' }}</p>
                        </div>
                        <div class="col-span-2 mt-1 border-t border-gray-50 pt-2">
                            <p class="text-gray-400">Tanggal & Waktu</p>
                            <p class="font-bold text-gray-800">{{ $activeStruk->tanggal_transaksi->isoFormat('dddd, D MMMM YYYY — HH:mm') }} WIB</p>
                        </div>
                    </div>
                </div>

                {{-- Divider dashed --}}
                <div class="flex items-center mb-4">
                    <div class="flex-1 border-t-2 border-dashed border-gray-200"></div>
                    <span class="px-3 text-gray-300 text-xs">✂</span>
                    <div class="flex-1 border-t-2 border-dashed border-gray-200"></div>
                </div>

                {{-- Daftar Item --}}
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Rincian Pembelian</p>
                    <div class="space-y-3">
                        @forelse($activeStruk->detailTransaksi as $detail)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-4">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800">{{ $detail->produkJadi->nama_produk ?? '—' }}</p>
                                <p class="text-xs text-gray-400">{{ $detail->qty }} pcs × Rp{{ number_format($detail->subtotal / $detail->qty, 0, ',', '.') }}</p>
                            </div>
                            <p class="text-sm font-semibold text-gray-800 self-end sm:self-auto">Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</p>
                        </div>
                        @empty
                        <p class="text-sm text-gray-400 text-center py-2">Tidak ada detail transaksi.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Divider dashed --}}
                <div class="flex items-center my-4">
                    <div class="flex-1 border-t-2 border-dashed border-gray-200"></div>
                </div>

                {{-- Footer Ringkasan --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Pendapatan</span>
                        <span class="font-semibold text-gray-800">Rp{{ number_format($activeStruk->total_pendapatan, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Modal (HPP)</span>
                        <span class="font-semibold text-red-500">− Rp{{ number_format($activeStruk->total_hpp, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                        <span class="text-sm font-bold text-gray-700">Laba Kotor</span>
                        @php $laba = $activeStruk->total_pendapatan - $activeStruk->total_hpp; @endphp
                        <span class="text-xl font-bold {{ $laba >= 0 ? 'text-[#D4831F]' : 'text-red-500' }}">
                            Rp{{ number_format($laba, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Modal Footer Button (Sticky at bottom) --}}
            <div class="shrink-0 p-5 bg-gray-50 border-t border-gray-100">
                <button wire:click="tutupStruk"
                        class="w-full py-2.5 rounded-xl text-sm font-semibold bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 transition-all shadow-sm">
                    Tutup Nota
                </button>
            </div>

        </div>
    </div>
    @endif
</div>
