<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Expert extends Model
{
    protected $fillable = ['user_id','name','weight'];

    public function matrices()
    {
        return $this->hasMany(AhpMatrix::class,'expert_id');
    }
}
