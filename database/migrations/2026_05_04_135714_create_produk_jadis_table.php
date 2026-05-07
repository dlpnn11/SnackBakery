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
        Schema::create('produk_jadi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produk');
            $table->unsignedInteger('harga_jual');
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('minimum_stok')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk_jadi');
    }
};
