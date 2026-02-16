<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Icd9 extends Model
{
    protected $table = 'icd9';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
