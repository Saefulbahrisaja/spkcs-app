<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AhpMatrix extends Model
{
     protected $fillable = ['expert_id','kriteria_1_id','kriteria_2_id',
        'mu','nu','pi','nilai_perbandingan'];

   public function expert()
    {
        return $this->belongsTo(Expert::class,'expert_id');
    }

    public function kriteria1() {
        return $this->belongsTo(Kriteria::class, 'kriteria_1_id');
    }

    public function kriteria2() {
        return $this->belongsTo(Kriteria::class, 'kriteria_2_id');
    }
}
