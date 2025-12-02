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
        Schema::create('rekomendasi_kebijakans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('laporan_evaluasis')->onDelete('cascade');
            $table->date('tanggal');
            $table->json('wilayah_prioritas')->nullable(); 
            $table->text('daftar_intervensi')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status',['draft','reviewed','approved'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_kebijakans');
    }
};
