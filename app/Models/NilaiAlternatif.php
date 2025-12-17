<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiAlternatif extends Model
{
    protected $table = 'nilai_alternatifs';
    protected $fillable = ['alternatif_id','kriteria_id','nilai','atribut_nama','nilai_input'];

   

    public function kriteria()
    {
        return $this->belongsTo(Kriteria::class);
    }

    public function alternatif()
    {
        return $this->belongsTo(
            AlternatifLahan::class,
            'alternatif_id',
            'id'
        );
    }
}

