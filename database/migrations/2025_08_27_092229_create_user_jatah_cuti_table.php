<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_jatah_cuti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('kantor_id')
                ->default(1)
                ->constrained('kantor')
                ->onDelete('cascade');
            $table->year('tahun');
            $table->integer('jatah')->default(12);
            $table->integer('terpakai')->default(0);
            $table->integer('sisa')->default(12); 
            $table->timestamps();

            $table->unique(['user_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_jatah_cuti');
        Schema::enableForeignKeyConstraints();
    }
};
