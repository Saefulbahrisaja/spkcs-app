<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlternatifLahan extends Model
{
    protected $fillable = [
    'lokasi',
    'geojson_path',
    'lat',
    'lng',
    'geometry_type',
    'status_validasi',
    'rekomendasi_dinas',
    'nilai_total'
];

 public function nilaiAlternatif()
    {
        return $this->hasMany(
            NilaiAlternatif::class,
            'alternatif_id',
            'id'
        );
    }
public function klasifikasi()
    {
        return $this->hasOne(
            KlasifikasiLahan::class,
            'alternatif_id',
            'id'
        );
    }

    public function nilai()
    {
        return $this->hasMany(NilaiAlternatif::class, 'alternatif_id');
    }

    public function vikor()
    {
        return $this->hasOne(
            PemeringkatanVikor::class,
            'alternatif_id',
            'id'
        );
    }

    public function nilaiDinamis()
    {
        return $this->hasMany(NilaiAlternatif::class, 'alternatif_id')
                    ->whereNull('kriteria_id'); 
    }
    
}

