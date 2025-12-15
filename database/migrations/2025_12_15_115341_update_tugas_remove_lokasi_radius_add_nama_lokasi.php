<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tugas', function (Blueprint $table) {

            // HAPUS kolom lama
            $table->dropColumn([
                'radius_meter',
                'tugas_lat',
                'tugas_lng',
            ]);

            // TAMBAH kolom baru
            $table->string('nama_lokasi_penugasan')->nullable()->after('batas_penugasan');
        });
    }

    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {

            // Kembalikan kolom yang dihapus
            $table->integer('radius_meter')->default(100);
            $table->decimal('tugas_lat', 10, 7)->nullable();
            $table->decimal('tugas_lng', 10, 7)->nullable();

            // Hapus kolom baru
            $table->dropColumn('nama_lokasi_penugasan');
        });
    }
};
