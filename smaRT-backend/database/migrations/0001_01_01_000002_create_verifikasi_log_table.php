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
        Schema::create('verifikasi_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('id_transaksi')->nullable();
            $table->enum('jenis_pengecekan', ['HASH_CHECK', 'INTEGRITY_CHECK']);
            $table->enum('status', ['VALID', 'INVALID']);
            $table->string('hash_tersimpan', 64);
            $table->string('hash_terhitung', 64);
            $table->timestamp('waktu_cek')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifikasi_log');
    }
};
