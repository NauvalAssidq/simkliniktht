<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatuSehatMappingLokasiRalan extends Model
{
    protected $table = 'satu_sehat_mapping_lokasi_ralan';
    protected $primaryKey = 'kd_poli';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
