<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maquina extends Model
{
    protected $table = 'maquinas';

    protected $primaryKey = 'IdMaquina';

    public $timestamps = false;

    protected $fillable = [
        'CodigoMaquina',
        'Modelo',
        'EsActivo',
        'EsVisible',
        'idCentro',
        'country',
    ];
}
