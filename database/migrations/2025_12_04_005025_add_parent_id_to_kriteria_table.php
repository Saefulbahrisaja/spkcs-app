<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdToKriteriaTable extends Migration
{
    public function up()
    {
        Schema::table('kriterias', function (Blueprint $table) {

            // tambah kolom parent_id
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');

            // relasi ke tabel kriteria itu sendiri
            $table->foreign('parent_id')
                ->references('id')
                ->on('kriterias')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('kriterias', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
}
