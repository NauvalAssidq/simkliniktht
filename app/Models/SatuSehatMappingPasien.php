<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatuSehatMappingPasien extends Model
{
    protected $table = 'satu_sehat_mapping_pasien';
    protected $primaryKey = 'no_rkm_medis';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
