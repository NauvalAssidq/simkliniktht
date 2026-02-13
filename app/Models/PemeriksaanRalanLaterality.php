<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemeriksaanRalanLaterality extends Model
{
    protected $table = 'pemeriksaan_ralan_laterality';
    protected $guarded = [];

    public function regPeriksa()
    {
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }
}
