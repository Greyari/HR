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
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->decimal('checkin_lat', 10, 7)->nullable();
            $table->decimal('checkin_lng', 10, 7)->nullable();
            $table->time('checkin_time')->nullable();
            $table->date('checkin_date')->nullable();

            $table->decimal('checkout_lat', 10, 7)->nullable();
            $table->decimal('checkout_lng', 10, 7)->nullable();
            $table->time('checkout_time')->nullable();
            $table->date('checkout_date')->nullable();

            $table->string('video_user')->nullable();
            $table->enum('status', ['Tepat Waktu', 'Terlambat'])->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('absensi');
        Schema::enableForeignKeyConstraints();
    }
};
