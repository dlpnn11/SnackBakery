<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BahanBaku extends Model
{
    protected $table = 'bahan_baku';

    protected $fillable = [
        'nama_bahan',
        'satuan',
        'harga_beli_persatuan',
        'stok',
        'minimum_stok',
        'tanggal_expired',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli_persatuan' => 'integer',
            'stok'                => 'decimal:2',
            'minimum_stok'        => 'integer',
            'tanggal_expired'     => 'date',
        ];
    }

    /**
     * Bahan baku digunakan di banyak resep.
     */
    public function resep(): HasMany
    {
        return $this->hasMany(Resep::class, 'bahan_baku_id');
    }
}
