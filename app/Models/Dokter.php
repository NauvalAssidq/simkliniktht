<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    protected $table = 'dokter';
    protected $primaryKey = 'kd_dokter';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function satuSehatMapping()
    {
        return $this->hasOne(SatuSehatMappingDokter::class, 'kd_dokter', 'kd_dokter');
    }
}
