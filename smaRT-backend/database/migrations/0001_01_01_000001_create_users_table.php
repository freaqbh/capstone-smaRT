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
            $table->uuid('id')->primary();
            $table->uuid('id_rt');
            $table->string('nama');
            $table->string('NIK')->unique();
            $table->enum('role', ['WARGA', 'PENGURUS', 'KETUA', 'BENDAHARA']);
            $table->string('phone')->nullable();
            $table->string('password_hash');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_rt')->references('id_rt')->on('rt')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
