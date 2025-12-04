<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemeringkatanVikor extends Model
{
    
    protected $fillable = ['alternatif_id','v_value','q_value','hasil_ranking'];

    public function alternatif()
    {
        return $this->belongsTo(AlternatifLahan::class, 'alternatif_id', 'id');
    }
}

