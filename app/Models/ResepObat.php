<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResepObat extends Model
{
    protected $table = 'resep_obat';
    protected $primaryKey = 'no_resep';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function detail()
    {
        return $this->hasMany(DetailResepObat::class, 'no_resep', 'no_resep');
    }

    public function regPeriksa()
    {
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }

    public function dokter()
    {
        return $this->belongsTo(\App\Models\Dokter::class, 'kd_dokter', 'kd_dokter');
    }
}
