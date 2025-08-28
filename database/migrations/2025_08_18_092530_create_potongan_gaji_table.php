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
        Schema::create('potongan_gaji', function (Blueprint $table) {
            $table->id();
            $table->string('nama_potongan'); // contoh: BPJS Kesehatan
            $table->decimal('persen', 5, 2)->default(0);    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('potongan_gaji');
    }
};
