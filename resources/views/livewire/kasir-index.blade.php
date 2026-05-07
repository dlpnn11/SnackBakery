<div x-data>
    {{-- Flash --}}
    @if($flashMessage)
    <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4500)"
         x-transition:leave="transition ease-in duration-300" x-transition:leave-end="opacity-0"
         class="fixed top-5 right-5 z-[999] max-w-md px-5 py-3 rounded-xl shadow-lg text-white text-sm font-medium
                {{ $flashType==='success' ? 'bg-emerald-500' : 'bg-red-500' }}">
        {{ $flashMessage }}
    </div>
    @endif

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#8B5E3C]">🛒 Kasir / POS</h1>
            <p class="text-sm text-gray-400 mt-0.5">Pencatatan transaksi penjualan organik</p>
        </div>
        @if(!empty($cart))
        <button wire:click="kosongkanKeranjang"
                class="flex items-center gap-2 text-xs text-red-400 hover:text-red-600 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-lg transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Kosongkan Keranjang
        </button>
        @endif
    </div>

    {{-- SPLIT LAYOUT --}}
    <div class="grid grid-cols-3 gap-6 items-start">

        {{-- ═══════════════════════════════════════ --}}
        {{-- PANEL KIRI — Grid Produk (2/3) ──────── --}}
        {{-- ═══════════════════════════════════════ --}}
        <div class="col-span-2">
            <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-[#8B5E3C] uppercase tracking-wide">Pilih Produk</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Klik produk untuk menambah ke keranjang</p>
                </div>

                @if($produkList->isEmpty())
                <div class="px-6 py-16 text-center text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                    <p class="font-medium text-sm">Tidak ada produk dengan stok tersedia</p>
                    <p class="text-xs mt-1">Lakukan produksi di menu Dapur terlebih dahulu.</p>
                </div>
                @else
                <div class="grid grid-cols-3 gap-4 p-5">
                    @foreach($produkList as $produk)
                    @php $key = "p{$produk->id}"; $inCart = isset($cart[$key]); @endphp
                    <button wire:click="tambahKeKeranjang({{ $produk->id }})"
                            class="relative text-left p-4 rounded-xl border-2 transition-all duration-200 hover:shadow-md group
                                   {{ $inCart ? 'border-[#D4831F] bg-[#fdf0e0]' : 'border-gray-100 bg-gray-50 hover:border-[#D4831F]/50 hover:bg-[#fdf6ec]' }}">
                        {{-- Qty badge jika sudah di cart --}}
                        @if($inCart)
                        <span class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-[#D4831F] text-white text-[11px] font-bold flex items-center justify-center shadow-md">
                            {{ $cart[$key]['qty'] }}
                        </span>
                        @endif

                        <div class="w-10 h-10 rounded-xl bg-white border border-gray-100 flex items-center justify-center mb-3 group-hover:border-[#D4831F]/30 transition-colors shadow-sm">
                            <span class="text-xl">🍞</span>
                        </div>
                        <p class="text-sm font-semibold text-gray-800 leading-tight mb-1">{{ $produk->nama_produk }}</p>
                        <p class="text-xs font-bold text-[#D4831F]">Rp{{ number_format($produk->harga_jual,0,',','.') }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Stok: {{ $produk->stok }} pcs</p>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ═══════════════════════════════════════ --}}
        {{-- PANEL KANAN — Keranjang + Checkout (1/3)--}}
        {{-- ═══════════════════════════════════════ --}}
        <div class="col-span-1 space-y-4">

            {{-- Keranjang --}}
            <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-[#8B5E3C] uppercase tracking-wide">🧺 Keranjang</h2>
                    @if($totalItem > 0)
                    <span class="text-xs bg-[#D4831F] text-white px-2 py-0.5 rounded-full font-bold">{{ $totalItem }} item</span>
                    @endif
                </div>

                @if(empty($cart))
                <div class="px-5 py-10 text-center text-gray-400">
                    <span class="text-4xl block mb-2">🛒</span>
                    <p class="text-xs">Keranjang kosong.<br>Klik produk di kiri untuk menambah.</p>
                </div>
                @else
                <div class="divide-y divide-gray-50">
                    @foreach($cart as $key => $item)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $item['nama'] }}</p>
                            <p class="text-xs text-gray-400">Rp{{ number_format($item['harga'],0,',','.') }} × {{ $item['qty'] }}</p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button wire:click="kurangiQty('{{ $key }}')"
                                    class="w-6 h-6 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center font-bold text-sm transition-all">−</button>
                            <span class="w-6 text-center text-sm font-bold text-gray-800">{{ $item['qty'] }}</span>
                            <button wire:click="tambahKeKeranjang({{ $item['produk_id'] }})"
                                    class="w-6 h-6 rounded-lg bg-[#D4831F]/10 hover:bg-[#D4831F]/20 text-[#D4831F] flex items-center justify-center font-bold text-sm transition-all">+</button>
                            <button wire:click="hapusItem('{{ $key }}')"
                                    class="w-6 h-6 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-all ml-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="px-5 py-1.5 text-right">
                        <span class="text-xs font-semibold text-gray-600">
                            Subtotal: Rp{{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}
                        </span>
                    </div>
                    @endforeach
                </div>

                {{-- Total --}}
                <div class="px-5 py-4 border-t border-dashed border-gray-200 bg-[#fdf8f2]">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-600">TOTAL</span>
                        <span class="text-lg font-bold text-[#8B5E3C]">Rp{{ number_format($totalPendapatan,0,',','.') }}</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Form Pembayaran --}}
            @if(!empty($cart))
            <div class="bg-white rounded-2xl border border-[#ecddc8]/60 shadow-sm px-5 py-5 space-y-4">
                <h3 class="text-sm font-bold text-[#8B5E3C] uppercase tracking-wide">💳 Pembayaran</h3>

                {{-- Metode Pembayaran --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="metodePembayaran" value="tunai" class="sr-only peer"/>
                            <div class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border-2 text-sm font-semibold transition-all
                                        peer-checked:border-[#D4831F] peer-checked:bg-[#fdf0e0] peer-checked:text-[#D4831F]
                                        border-gray-200 text-gray-500 hover:border-gray-300">
                                💵 Tunai
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="metodePembayaran" value="qris" class="sr-only peer"/>
                            <div class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border-2 text-sm font-semibold transition-all
                                        peer-checked:border-[#D4831F] peer-checked:bg-[#fdf0e0] peer-checked:text-[#D4831F]
                                        border-gray-200 text-gray-500 hover:border-gray-300">
                                📱 QRIS
                            </div>
                        </label>
                    </div>
                    @if($metodePembayaran === 'qris')
                    <p class="text-[10px] text-blue-500 mt-1.5 bg-blue-50 px-3 py-1.5 rounded-lg">
                        ℹ️ QRIS: Uang masuk via scan rekening pribadi pemilik. Hanya sebagai penanda status.
                    </p>
                    @endif
                </div>

                {{-- Nominal Diterima --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nominal Uang Diterima</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium">Rp</span>
                        <input type="number" wire:model.live="nominalDiterima"
                               placeholder="{{ $totalPendapatan }}"
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-[#D4831F]/30 focus:border-[#D4831F] outline-none transition-all"/>
                    </div>
                    @if($nominalDiterima && (int)$nominalDiterima >= $totalPendapatan)
                    <p class="text-xs text-emerald-600 mt-1 font-medium">
                        ✓ Pembayaran cukup
                    </p>
                    @elseif($nominalDiterima)
                    <p class="text-xs text-red-500 mt-1">
                        ✗ Kurang Rp{{ number_format($totalPendapatan - (int)$nominalDiterima, 0, ',', '.') }}
                    </p>
                    @endif
                </div>

                {{-- Tombol Simpan --}}
                <button wire:click="simpanTransaksi"
                        wire:loading.attr="disabled"
                        wire:confirm="Simpan transaksi Rp{{ number_format($totalPendapatan,0,',','.') }} dengan {{ $metodePembayaran }}?"
                        @class([
                            'w-full py-3 rounded-xl text-sm font-bold shadow-md transition-all duration-200 flex items-center justify-center gap-2',
                            'bg-[#D4831F] hover:bg-[#b86a16] text-white hover:shadow-lg' => !empty($nominalDiterima) && (int)$nominalDiterima >= $totalPendapatan,
                            'bg-gray-200 text-gray-400 cursor-not-allowed' => empty($nominalDiterima) || (int)$nominalDiterima < $totalPendapatan,
                        ])>
                    <span wire:loading wire:target="simpanTransaksi">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </span>
                    <span wire:loading.remove wire:target="simpanTransaksi">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    Simpan Transaksi
                </button>
            </div>
            @endif

        </div>{{-- end panel kanan --}}
    </div>
</div>
