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
         Schema::create('ahp_matrices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expert_id')->index();
            $table->unsignedBigInteger('kriteria_1_id')->index();
            $table->unsignedBigInteger('kriteria_2_id')->index();
            $table->decimal('nilai_perbandingan', 8, 4);
            $table->timestamps();

            $table->foreign('expert_id')->references('id')->on('experts')->onDelete('cascade');
            // optionally foreign keys to kriteria
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ahp_matrices');
    }
};
