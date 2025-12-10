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
        Schema::table('nilai_alternatifs', function (Blueprint $table) {
            $table->string('atribut_nama')->nullable(); // nama bebas: C-Org, Lereng, pH
            $table->string('nilai')->nullable();        // nilai bebas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_alternatifs', function (Blueprint $table) {
            //
        });
    }
};
