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
        Schema::create('izin_fitur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peran_id')->constrained('peran')->onDelete('cascade');
            $table->foreignId('fitur_id')->constrained('fitur')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['peran_id', 'fitur_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izin_fitur');
    }
};
