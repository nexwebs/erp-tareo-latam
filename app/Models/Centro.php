<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Centro extends Model
{
    protected $table = 'centros';

    protected $primaryKey = 'IdCentro';

    public $timestamps = false;

    protected $fillable = [
        'NombreCentro',
        'EsActivo',
        'EsProvincia',
        'EsChile',
        'EsColombia',
        'EsAustralia',
        'Color1',
        'Color2',
        'zona',
    ];
}
