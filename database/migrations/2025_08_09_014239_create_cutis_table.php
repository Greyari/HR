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
        Schema::create('cuti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipe_cuti', ['Sakit','Izin'])->default('Izin');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('potong_gaji')->default(false);
            $table->string('alasan')->nullable();
            $table->enum('status', ['Pending', 'Proses', 'Disetujui', 'Ditolak'])->default('Pending');
            $table->string('catatan_penolakan')->nullable();
            $table->unsignedTinyInteger('approval_step')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('cuti');
        Schema::enableForeignKeyConstraints();
    }
};
