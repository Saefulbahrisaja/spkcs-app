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
        Schema::create('batas_kesesuaians', function (Blueprint $table) {
        $table->id();
        $table->decimal('batas_s1', 5, 2)->default(0.80);
        $table->decimal('batas_s2', 5, 2)->default(0.60);
        $table->decimal('batas_s3', 5, 2)->default(0.40);
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
