<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->enum('metode_pembayaran', ['tunai', 'qris']);
            $table->unsignedInteger('total_pendapatan');
            $table->unsignedInteger('total_hpp');           // Harga Pokok Penjualan (modal bahan resep)
            $table->timestamp('tanggal_transaksi')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
