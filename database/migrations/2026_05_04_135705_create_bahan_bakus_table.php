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
        Schema::create('bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bahan');
            $table->string('satuan');                    // satuan terkecil: gram, ml, butir
            $table->unsignedInteger('harga_beli_persatuan'); // harga statis per satuan terkecil
            $table->decimal('stok', 12, 2)->default(0);  // stok dalam satuan terkecil
            $table->unsignedInteger('minimum_stok')->default(0);
            $table->date('tanggal_expired')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_baku');
    }
};
