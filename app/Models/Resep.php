<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resep extends Model
{
    protected $table = 'resep';

    protected $fillable = [
        'produk_jadi_id',
        'bahan_baku_id',
        'kebutuhan_satuan',
    ];

    protected function casts(): array
    {
        return [
            'kebutuhan_satuan' => 'decimal:2',
        ];
    }

    /**
     * Resep ini milik satu produk jadi.
     */
    public function produkJadi(): BelongsTo
    {
        return $this->belongsTo(ProdukJadi::class, 'produk_jadi_id');
    }

    /**
     * Resep ini menggunakan satu bahan baku.
     */
    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}
