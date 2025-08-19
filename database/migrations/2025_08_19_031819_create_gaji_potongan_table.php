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
        Schema::create('gaji_potongan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gaji_id'); // relasi ke tabel gaji
            $table->unsignedBigInteger('potongan_gaji_id'); // relasi ke master potongan
            $table->decimal('nominal', 15, 2)->default(0); // nilai potongan untuk gaji ini
            $table->timestamps();

            // Foreign keys
            $table->foreign('gaji_id')->references('id')->on('gaji')->onDelete('cascade');
            $table->foreign('potongan_gaji_id')->references('id')->on('potongan_gaji')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gaji_potongan');
    }
};
