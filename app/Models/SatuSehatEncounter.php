<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatuSehatEncounter extends Model
{
    protected $table = 'satu_sehat_encounter';
    protected $primaryKey = 'no_rawat';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
