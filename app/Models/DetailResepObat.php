<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailResepObat extends Model
{
    protected $table = 'detail_resep_obat';
    protected $guarded = [];

    public function resep()
    {
        return $this->belongsTo(ResepObat::class, 'no_resep', 'no_resep');
    }

    public function barang()
    {
        return $this->belongsTo(Databarang::class, 'kd_brng', 'kd_brng');
    }

    public function getNamaObatAttribute()
    {
        if ($this->barang) {
            return $this->barang->nm_brng;
        }
        return $this->nm_obat_manual ?? '-';
    }
}
