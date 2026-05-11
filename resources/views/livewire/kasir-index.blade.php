<div x-data>
    {{-- Flash --}}
    @if($flashMessage)
    <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4500)"
         x-transition:leave="transition ease-in duration-300" x-transition:leave-end="opacity-0"
         class="fixed top-5 right-5 z-999 max-w-sm md:max-w-md px-5 py-3 rounded-xl shadow-lg text-white text-sm font-medium
                {{ $flashType==='success' ? 'bg-emerald-500' : 'bg-red-500' }}">
        {{ $flashMessage }}
    </div>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-[#8B5E3C]">🛒 Kasir </h1>
            <p class="text-xs md:text-sm text-gray-400 mt-0.5">Pencatatan transaksi penjualan organik</p>
        </div>
        @if(!empty($cart))
        <button wire:click="kosongkanKeranjang"
                class="flex items-center justify-center gap-2 text-xs text-red-400 hover:text-red-600 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-lg transition-all self-start md:self-auto w-full md:w-auto">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Kosongkan Keranjang
        </button>
        @endif
    </div>

    {{-- SPLIT LAYOUT --}}
    <div class="flex flex-col lg:flex-row gap-6 items-start">

        {{-- ═══════════════════════════════════════ --}}
        {{-- PANEL KIRI — Grid Produk (2/3) ──────── --}}
        {{-- ═══════════════════════════════════════ --}}
        <div class="w-full lg:w-2/3">
            <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm overflow-hidden">
                <div class="px-4 md:px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-[#8B5E3C] uppercase tracking-wide">Pilih Produk</h2>
                        <p class="text-[10px] md:text-xs text-gray-400 mt-0.5">Klik produk untuk menambah ke keranjang</p>
                    </div>
                </div>

                @if($produkList->isEmpty())
                <div class="px-6 py-16 text-center text-gray-400">
                    <svg class="w-10 h-10 md:w-12 md:h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                    <p class="font-medium text-xs md:text-sm">Tidak ada produk dengan stok tersedia</p>
                    <p class="text-[10px] md:text-xs mt-1">Lakukan produksi di menu Dapur terlebih dahulu.</p>
                </div>
                @else
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4 p-4 md:p-5 max-h-[600px] overflow-y-auto custom-scrollbar">
                    @foreach($produkList as $produk)
                    @php $key = "p{$produk->id}"; $inCart = isset($cart[$key]); @endphp
                    <button wire:click="tambahKeKeranjang({{ $produk->id }})"
                            class="relative text-left p-3 md:p-4 rounded-xl border-2 transition-all duration-200 hover:shadow-md group flex flex-col h-full
                                   {{ $inCart ? 'border-[#D4831F] bg-[#fdf0e0]' : 'border-gray-100 bg-gray-50 hover:border-[#D4831F]/50 hover:bg-[#fdf6ec]' }}">
                        {{-- Qty badge jika sudah di cart --}}
                        @if($inCart)
                        <span class="absolute -top-2 -right-2 w-5 h-5 md:w-6 md:h-6 rounded-full bg-[#D4831F] text-white text-[10px] md:text-[11px] font-bold flex items-center justify-center shadow-md">
                            {{ $cart[$key]['qty'] }}
                        </span>
                        @endif

                        <div class="w-8 h-8 md:w-10 md:h-10 rounded-xl bg-white border border-gray-100 flex items-center justify-center mb-2 md:mb-3 group-hover:border-[#D4831F]/30 transition-colors shadow-sm shrink-0">
                            <span class="text-base md:text-xl">🍞</span>
                        </div>
                        <div class="flex-1 flex flex-col justify-between">
                            <p class="text-[11px] md:text-sm font-semibold text-gray-800 leading-tight mb-1">{{ $produk->nama_produk }}</p>
                            <div>
                                <p class="text-[11px] md:text-xs font-bold text-[#D4831F]">Rp{{ number_format($produk->harga_jual,0,',','.') }}</p>
                                <p class="text-[9px] md:text-[10px] text-gray-400 mt-0.5">Stok: {{ $produk->stok }} pcs</p>
                            </div>
                        </div>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ═══════════════════════════════════════ --}}
        {{-- PANEL KANAN — Keranjang + Checkout (1/3)--}}
        {{-- ═══════════════════════════════════════ --}}
        <div class="w-full lg:w-1/3 space-y-4 mb-10 lg:mb-0">

            {{-- Keranjang --}}
            <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm overflow-hidden flex flex-col max-h-[500px]">
                <div class="px-4 md:px-5 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                    <h2 class="text-sm font-bold text-[#8B5E3C] uppercase tracking-wide">🧺 Keranjang</h2>
                    @if($totalItem > 0)
                    <span class="text-[10px] md:text-xs bg-[#D4831F] text-white px-2 py-0.5 rounded-full font-bold">{{ $totalItem }} item</span>
                    @endif
                </div>

                @if(empty($cart))
                <div class="px-4 md:px-5 py-10 text-center text-gray-400 my-auto">
                    <span class="text-3xl md:text-4xl block mb-2">🛒</span>
                    <p class="text-[10px] md:text-xs">Keranjang kosong.<br>Klik produk di kiri untuk menambah.</p>
                </div>
                @else
                <div class="divide-y divide-gray-50 overflow-y-auto custom-scrollbar flex-1">
                    @foreach($cart as $key => $item)
                    <div class="px-4 md:px-5 py-3 flex items-start sm:items-center gap-2 md:gap-3 flex-col sm:flex-row">
                        <div class="flex-1 min-w-0 w-full sm:w-auto">
                            <p class="text-[11px] md:text-sm font-medium text-gray-800 truncate">{{ $item['nama'] }}</p>
                            <p class="text-[10px] md:text-xs text-gray-400">Rp{{ number_format($item['harga'],0,',','.') }} × {{ $item['qty'] }}</p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-auto w-full sm:w-auto justify-end mt-2 sm:mt-0">
                            <button wire:click="kurangiQty('{{ $key }}')"
                                    class="w-6 h-6 md:w-7 md:h-7 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center font-bold text-sm transition-all">−</button>
                            <span class="w-5 md:w-6 text-center text-[11px] md:text-sm font-bold text-gray-800">{{ $item['qty'] }}</span>
                            <button wire:click="tambahKeKeranjang({{ $item['produk_id'] }})"
                                    class="w-6 h-6 md:w-7 md:h-7 rounded-lg bg-[#D4831F]/10 hover:bg-[#D4831F]/20 text-[#D4831F] flex items-center justify-center font-bold text-sm transition-all">+</button>
                            <button wire:click="hapusItem('{{ $key }}')"
                                    class="w-6 h-6 md:w-7 md:h-7 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-all ml-0.5 md:ml-1 border border-transparent hover:border-red-100">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="px-4 md:px-5 py-1.5 text-right">
                        <span class="text-[10px] md:text-xs font-semibold text-gray-600">
                            Subtotal: Rp{{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}
                        </span>
                    </div>
                    @endforeach
                </div>

                {{-- Total --}}
                <div class="px-4 md:px-5 py-4 border-t border-dashed border-gray-200 bg-[#fdf8f2] shrink-0">
                    <div class="flex items-center justify-between">
                        <span class="text-xs md:text-sm font-semibold text-gray-600">TOTAL</span>
                        <span class="text-base md:text-lg font-bold text-[#8B5E3C]">Rp{{ number_format($totalPendapatan,0,',','.') }}</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Form Pembayaran --}}
            @if(!empty($cart))
            <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm px-4 md:px-5 py-4 md:py-5 space-y-4">
                <h3 class="text-xs md:text-sm font-bold text-[#8B5E3C] uppercase tracking-wide">💳 Pembayaran</h3>

                {{-- Metode Pembayaran --}}
                <div>
                    <label class="block text-[10px] md:text-xs font-semibold text-gray-600 mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="metodePembayaran" value="tunai" class="sr-only peer"/>
                            <div class="flex items-center justify-center gap-1.5 md:gap-2 px-2 md:px-3 py-2 md:py-2.5 rounded-xl border-2 text-[11px] md:text-sm font-semibold transition-all
                                        peer-checked:border-[#D4831F] peer-checked:bg-[#fdf0e0] peer-checked:text-[#D4831F]
                                        border-gray-200 text-gray-500 hover:border-gray-300">
                                💵 Tunai
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="metodePembayaran" value="qris" class="sr-only peer"/>
                            <div class="flex items-center justify-center gap-1.5 md:gap-2 px-2 md:px-3 py-2 md:py-2.5 rounded-xl border-2 text-[11px] md:text-sm font-semibold transition-all
                                        peer-checked:border-[#D4831F] peer-checked:bg-[#fdf0e0] peer-checked:text-[#D4831F]
                                        border-gray-200 text-gray-500 hover:border-gray-300">
                                📱 QRIS
                            </div>
                        </label>
                    </div>
                    @if($metodePembayaran === 'qris')
                    <p class="text-[9px] md:text-[10px] text-blue-500 mt-1.5 bg-blue-50 px-2.5 py-1.5 rounded-lg leading-snug">
                        ℹ️ QRIS: Uang masuk via scan rekening pribadi pemilik. Hanya sebagai penanda status.
                    </p>
                    @endif
                </div>

                {{-- Nominal Diterima --}}
                <div>
                    <label class="block text-[10px] md:text-xs font-semibold text-gray-600 mb-1.5">Nominal Uang Diterima</label>
                    <div class="relative">
                        <span class="absolute left-3 md:left-4 top-1/2 -translate-y-1/2 text-xs md:text-sm text-gray-400 font-medium">Rp</span>
                        <input type="number" wire:model.live="nominalDiterima"
                               placeholder="{{ $totalPendapatan }}"
                               class="w-full pl-9 md:pl-10 pr-3 md:pr-4 py-2 md:py-2.5 border border-gray-200 rounded-xl text-xs md:text-sm font-semibold focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] outline-none transition-all"/>
                    </div>
                    @if($nominalDiterima && (int)$nominalDiterima >= $totalPendapatan)
                    <p class="text-[10px] md:text-xs text-emerald-600 mt-1.5 font-medium">
                        ✓ Pembayaran cukup
                    </p>
                    @elseif($nominalDiterima)
                    <p class="text-[10px] md:text-xs text-red-500 mt-1.5">
                        ✗ Kurang Rp{{ number_format($totalPendapatan - (int)$nominalDiterima, 0, ',', '.') }}
                    </p>
                    @endif
                </div>

                {{-- Tombol Simpan --}}
                <button wire:click="simpanTransaksi"
                        wire:loading.attr="disabled"
                        wire:confirm="Simpan transaksi Rp{{ number_format($totalPendapatan,0,',','.') }} dengan {{ $metodePembayaran }}?"
                        @class([
                            'w-full py-2.5 md:py-3 rounded-xl text-xs md:text-sm font-bold shadow-md transition-all duration-200 flex items-center justify-center gap-2',
                            'bg-[#D4831F] hover:bg-[#b86a16] text-white hover:shadow-lg' => !empty($nominalDiterima) && (int)$nominalDiterima >= $totalPendapatan,
                            'bg-gray-200 text-gray-400 cursor-not-allowed' => empty($nominalDiterima) || (int)$nominalDiterima < $totalPendapatan,
                        ])>
                    <span wire:loading wire:target="simpanTransaksi">
                        <svg class="animate-spin w-3.5 h-3.5 md:w-4 md:h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </span>
                    <span wire:loading.remove wire:target="simpanTransaksi">
                        <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    Simpan Transaksi
                </button>
            </div>
            @endif

        </div>{{-- end panel kanan --}}
    </div>
</div>
