<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduccionColombia extends Model
{
    protected $table = 'produccioncolombia';

    protected $primaryKey = 'idProduccion';

    public $timestamps = false;

    protected $fillable = [
        'idMaquina',
        'FechaProduccion',
        'HoraProduccion',
        'idCentro',
        'ProduccionFinal',
        'Mon1',
        'Bill2',
        'Bill5',
        'Bill10',
        'Bill20',
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
