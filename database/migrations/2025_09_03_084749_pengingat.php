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
        Schema::create('pengingat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peran_id')->constrained('peran')->onDelete('cascade');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->dateTime('tanggal_jatuh_tempo');
            $table->enum('status', ['Pending', 'Selesai', 'Terlambat'])->default('Pending');
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pengingat');
        Schema::enableForeignKeyConstraints();
    }
};
