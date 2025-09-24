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
        Schema::create('gaji', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('bulan'); // 1-12
            $table->unsignedSmallInteger('tahun'); // ex: 2025
            $table->integer('total_kehadiran')->default(0);
            $table->decimal('gaji_kotor', 15, 2)->default(0);
            $table->decimal('total_potongan', 15, 2)->default(0);
            $table->json('detail_potongan')->nullable();
            $table->decimal('gaji_bersih', 15, 2)->default(0);
            $table->enum('status', ['Sudah Dibayar', 'Belum Dibayar'])->default('Belum Dibayar');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Supaya tiap user hanya punya 1 data gaji per bulan + tahun
            $table->unique(['user_id', 'bulan', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gaji');
    }
};
