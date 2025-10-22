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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peran_id')->nullable()->constrained('peran')->nullOnDelete();
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatan')->nullOnDelete();
            $table->foreignId('departemen_id')->nullable()->constrained('departemen')->nullOnDelete();
            $table->string('nama');
            $table->decimal('gaji_per_hari', 15, 2)->nullable();
            $table->string('npwp')->unique()->nullable();
            $table->string('bpjs_kesehatan')->unique()->nullable();
            $table->string('bpjs_ketenagakerjaan')->unique()->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->default('Laki-laki');
            $table->enum('status_pernikahan', ['Menikah', 'Belum Menikah'])->default('Belum Menikah');
            $table->string('password');
            $table->integer('coba_login')->default(0);
            $table->boolean('terkunci')->default(false);
            $table->string('device_token')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->foreignId('jabatan_id')->nullable()->index();
            $table->foreignId('departemen_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::enableForeignKeyConstraints();
    }
};
