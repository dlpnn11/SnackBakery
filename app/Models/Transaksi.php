<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'metode_pembayaran',
        'total_pendapatan',
        'total_hpp',
        'tanggal_transaksi',
    ];

    protected function casts(): array
    {
        return [
            'total_pendapatan'   => 'integer',
            'total_hpp'          => 'integer',
            'tanggal_transaksi'  => 'datetime',
        ];
    }

    /**
     * Satu transaksi memiliki banyak detail (item yang dibeli)
     */
    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'transaksi_id');
    }
}
