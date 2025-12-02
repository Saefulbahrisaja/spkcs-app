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
        Schema::create('laporan_evaluasis', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->json('hasil_klasifikasi')->nullable();
            $table->json('hasil_ranking')->nullable();
            $table->string('path_pdf')->nullable();
            $table->string('path_peta')->nullable();
            $table->enum('status_draft',['draft','published'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_evaluasis');
    }
};
