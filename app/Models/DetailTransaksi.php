<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';

    protected $fillable = [
        'transaksi_id',
        'produk_jadi_id',
        'qty',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'qty'      => 'integer',
            'subtotal' => 'integer',
        ];
    }

    /**
     * Detail ini milik satu transaksi induk
     */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    /**
     * Detail ini merujuk ke satu produk jadi
     */
    public function produkJadi(): BelongsTo
    {
        return $this->belongsTo(ProdukJadi::class, 'produk_jadi_id');
    }
}
