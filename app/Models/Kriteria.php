<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kriteria extends Model
{
    protected $fillable = ['nama_kriteria','parent_id','tipe','bobot','bobot_global'];

    public function nilai()
    {
        return $this->hasMany(NilaiAlternatif::class);
    }

    public function matrix1()
    {
        return $this->hasMany(AhpMatrix::class, 'kriteria_1_id');
    }

    public function matrix2()
    {
        return $this->hasMany(AhpMatrix::class, 'kriteria_2_id');
    }

        public function sub()
    {
        return $this->hasMany(Kriteria::class, 'parent_id');
    }
}