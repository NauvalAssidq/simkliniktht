<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemeriksaanAudiologi extends Model
{
    protected $table = 'pemeriksaan_audiologi';
    protected $primaryKey = 'no_rawat';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function regPeriksa()
    {
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }
}
