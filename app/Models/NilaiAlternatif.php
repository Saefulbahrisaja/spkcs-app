<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiAlternatif extends Model
{
    protected $fillable = ['alternatif_id','kriteria_id','nilai','skor'];

    public function alternatif()
    {
        return $this->belongsTo(AlternatifLahan::class);
    }

    public function kriteria()
    {
        return $this->belongsTo(Kriteria::class);
    }
}

