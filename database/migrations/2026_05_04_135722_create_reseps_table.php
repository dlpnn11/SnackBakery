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
        Schema::create('resep', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_jadi_id')->constrained('produk_jadi')->cascadeOnDelete();
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->cascadeOnDelete();
            $table->decimal('kebutuhan_satuan', 12, 2); // takaran resep dalam satuan terkecil
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resep');
    }
};
