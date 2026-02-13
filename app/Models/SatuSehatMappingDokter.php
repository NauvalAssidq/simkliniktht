<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatuSehatMappingDokter extends Model
{
    protected $table = 'satu_sehat_mapping_dokter';
    protected $primaryKey = 'kd_dokter';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
