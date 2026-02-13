<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JnsPerawatan extends Model
{
    protected $table = 'jns_perawatan';
    protected $primaryKey = 'kd_jenis_prw';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
