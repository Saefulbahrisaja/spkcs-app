<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KlasifikasiLahan extends Model
{
    protected $fillable = ['alternatif_id','skor_normalisasi','kelas_kesesuaian'];

    public function alternatif()
    {
        return $this->belongsTo(AlternatifLahan::class, 'alternatif_id', 'id');
    }
}

