<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Databarang extends Model
{
    protected $table = 'databarang';
    protected $primaryKey = 'kd_brng';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
