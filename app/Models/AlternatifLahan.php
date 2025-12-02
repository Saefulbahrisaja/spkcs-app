<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlternatifLahan extends Model
{
    protected $fillable = [
    'lokasi',
    'nilai_skor',
    'nilai_total',
    'kelas_kesesuaian',
    'geojson_path',
    'lat',
    'lng',
    'geometry_type'
];

    public function nilai()
    {
        return $this->hasMany(NilaiAlternatif::class, 'alternatif_id');
    }

    public function klasifikasi()
    {
        return $this->hasOne(KlasifikasiLahan::class, 'alternatif_id', 'id');
    }

    public function vikor()
    {
        return $this->hasOne(PemeringkatanVikor::class, 'alternatif_id', 'id');
    }
}

