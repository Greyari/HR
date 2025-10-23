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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_tugas');
            $table->dateTime('tanggal_penugasan');
            $table->dateTime('batas_penugasan');
            $table->integer('radius_meter')->default(100);
            $table->decimal('tugas_lat', 10, 7)->nullable();
            $table->decimal('tugas_lng', 10, 7)->nullable();
            $table->decimal('lampiran_lat', 10, 7)->nullable();
            $table->decimal('lampiran_lng', 10, 7)->nullable();
            $table->text('instruksi_tugas')->nullable();
            $table->enum('status', ['Proses', 'Menunggu Admin', 'Selesai'])->default('Proses');
            $table->dateTime('waktu_upload')->nullable();
            $table->integer('menit_terlambat')->nullable();
            $table->boolean('terlambat')->nullable()->default(null);
            $table->string('lampiran')->nullable();
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
