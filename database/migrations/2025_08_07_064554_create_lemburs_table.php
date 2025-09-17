<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('lembur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('deskripsi')->nullable();
            $table->string('catatan_penolakan')->nullable();
            $table->enum('status', [
                'Pending',
                'Proses',
                'Disetujui',
                'Ditolak'
            ])->default('Pending');
            $table->unsignedTinyInteger('approval_step')
                  ->default(0);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('lembur');
        Schema::enableForeignKeyConstraints();
    }
};
