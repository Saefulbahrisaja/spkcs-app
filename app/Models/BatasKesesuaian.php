<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatasKesesuaian extends Model
{
    protected $table = 'batas_kesesuaians';

    protected $fillable = [
        'batas_s1',
        'batas_s2',
        'batas_s3',
    ];
}
