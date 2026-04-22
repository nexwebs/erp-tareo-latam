<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduccionChile extends Model
{
    protected $table = 'produccionchile';

    protected $primaryKey = 'idProduccion';

    public $timestamps = false;

    protected $fillable = [
        'idMaquina',
        'FechaProduccion',
        'HoraProduccion',
        'idCentro',
        'ProduccionFinal',
        'Cant05',
        'CantBill1',
        'CantBill2',
        'CantBill5',
        'CantBill10',
        'Cierre',
        'numJug05',
        'numJug1',
        'numJug2',
    ];

    public function centro()
    {
        return $this->belongsTo(Centro::class, 'idCentro', 'IdCentro');
    }

    public function maquina()
    {
        return $this->belongsTo(Maquina::class, 'idMaquina', 'IdMaquina');
    }
}
