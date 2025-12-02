<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanEvaluasi extends Model
{
    protected $fillable = [
        'tanggal','hasil_klasifikasi','hasil_ranking','path_pdf','path_peta','status_draft'
    ];

    public function rekomendasi()
    {
        return $this->hasMany(RekomendasiKebijakan::class, 'laporan_id');
    }
}

