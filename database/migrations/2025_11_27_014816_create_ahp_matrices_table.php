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
            $table->unsignedBigInteger('expert_id')->nullable(); // AGREGASI atau pakar
            $table->unsignedBigInteger('kriteria_1_id');
            $table->unsignedBigInteger('kriteria_2_id');
            $table->decimal('nilai_perbandingan', 10, 6)->default(1.0);
            $table->timestamps();

            $table->unique(['expert_id','kriteria_1_id','kriteria_2_id'], 'ahp_unique_expert_pairs');

            // foreign keys (expert nullable)
            $table->foreign('expert_id')->references('id')->on('experts')->nullOnDelete();
            // assume tabel kriterias exists
            $table->foreign('kriteria_1_id')->references('id')->on('kriterias')->cascadeOnDelete();
            $table->foreign('kriteria_2_id')->references('id')->on('kriterias')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ahp_matrices');
    }
};