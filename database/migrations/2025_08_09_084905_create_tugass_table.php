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
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tugas');
            $table->time('jam_mulai');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->foreignId('departemen_id')->nullable()->constrained('departemen')->onDelete('cascade');
            $table->string('lokasi')->nullable();
            $table->text('instruksi_tugas')->nullable();
            $table->enum('status', ['Selesai', 'Proses'])->default('Proses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('tugas');
        Schema::enableForeignKeyConstraints();
    }
};
