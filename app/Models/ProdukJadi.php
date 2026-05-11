<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdukJadi extends Model
{
    protected $table = 'produk_jadi';

    protected $fillable = [
        'nama_produk',
        'harga_jual',
        'stok',
        'minimum_stok',
    ];

    protected function casts(): array
    {
        return [
            'harga_jual'   => 'integer',
            'stok'         => 'integer',
            'minimum_stok' => 'integer',
        ];
    }

    /**
     * Satu produk jadi memiliki banyak baris resep (kebutuhan bahan)
     */
    public function resep(): HasMany
    {
        return $this->hasMany(Resep::class, 'produk_jadi_id');
    }

    /**
     * Satu produk jadi bisa muncul di banyak detail transaksi
     */
    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'produk_jadi_id');
    }
}
