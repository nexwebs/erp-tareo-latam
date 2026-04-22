<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduccionPeru extends Model
{
    protected $table = 'produccionperu';

    protected $primaryKey = 'idProduccion';

    public $timestamps = false;

    protected $fillable = [
        'idMaquina',
        'FechaProduccion',
        'HoraProduccion',
        'idCentro',
        'ProduccionFinal',
        'cant05',
        'cMon1',
        'cMon2',
        'cMon5',
        'cBill10',
        'cBill20',
        'Cierre',
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
