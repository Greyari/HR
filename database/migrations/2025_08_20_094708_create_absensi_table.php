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
            $table->foreignId('tugas_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('kantor_id')->nullable()->constrained('kantor')->onDelete('set null');

            $table->decimal('checkin_lat', 10, 7)->nullable();
            $table->decimal('checkin_lng', 10, 7)->nullable();
            $table->timestamp('checkin_time')->nullable();

            $table->decimal('checkout_lat', 10, 7)->nullable();
            $table->decimal('checkout_lng', 10, 7)->nullable();
            $table->timestamp('checkout_time')->nullable();

            $table->enum('status', ['checkin', 'checkout'])->default('checkin');

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
