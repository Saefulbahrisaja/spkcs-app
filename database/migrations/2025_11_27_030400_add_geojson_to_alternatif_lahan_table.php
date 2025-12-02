<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('alternatif_lahans', function (Blueprint $table) {
            // Menyimpan lokasi file geojson
            $table->string('geojson_path')->nullable()->after('lokasi');

            // Opsional: Menyimpan centroid koordinat untuk zoom map
            $table->decimal('lat', 10, 7)->nullable()->after('geojson_path');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');

            // Opsional: Jenis geometri (Point, Polygon, MultiPolygon)
            $table->string('geometry_type')->nullable()->after('lng');
        });
    }

    public function down()
    {
        Schema::table('alternatif_lahan', function (Blueprint $table) {
            $table->dropColumn(['geojson_path', 'lat', 'lng', 'geometry_type']);
        });
    }
};
