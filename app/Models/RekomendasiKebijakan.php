<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekomendasiKebijakan extends Model
{
    protected $fillable = [
        'laporan_id',
        'tanggal',
        'wilayah_prioritas',
        'daftar_intervensi',
        'catatan',
        'status'
    ];
    protected $casts = [
        'wilayah_prioritas' => 'array',
    ];

    public function laporan()
    {
        return $this->belongsTo(LaporanEvaluasi::class);
    }
}

