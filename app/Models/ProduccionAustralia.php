<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduccionAustralia extends Model
{
    protected $table = 'produccionaustralia';

    protected $primaryKey = 'idProduccion';

    public $timestamps = false;

    protected $fillable = [
        'idMaquina',
        'FechaProduccion',
        'HoraProduccion',
        'idCentro',
        'ProduccionFinal',
        'Mon1',
        'Mon2',
        'Bill5',
        'Bill10',
        'Bill20',
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
