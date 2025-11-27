<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AhpMatrix extends Model
{
    protected $fillable = ['kriteria_1_id','kriteria_2_id','nilai_perbandingan'];

    public function kriteria1() {
        return $this->belongsTo(Kriteria::class, 'kriteria_1_id');
    }

    public function kriteria2() {
        return $this->belongsTo(Kriteria::class, 'kriteria_2_id');
    }
}
