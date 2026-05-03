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
        Schema::create('broadcast', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pengurus_id');
            $table->string('judul');
            $table->text('isi_pesan');
            $table->enum('kategori', ['INFORMASI', 'DARURAT', 'KEGIATAN']);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('pengurus_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcast');
    }
};
