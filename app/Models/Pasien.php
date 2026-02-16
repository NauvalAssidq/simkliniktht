<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    protected $table = 'pasien';
    protected $primaryKey = 'no_rkm_medis';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function satuSehatMapping()
    {
        return $this->hasOne(SatuSehatMappingPasien::class, 'no_rkm_medis', 'no_rkm_medis');
    }
}
