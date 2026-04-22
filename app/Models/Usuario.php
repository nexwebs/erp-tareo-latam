<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuario';

    protected $primaryKey = 'IdUsuario';

    public $timestamps = false;

    protected $fillable = [
        'Nombres',
        'Apellidos',
        'Tipo',
        'Usu',
        'Pass',
        'Activo',
        'rol_id',
    ];

    protected $hidden = [
        'Pass',
    ];

    protected function casts(): array
    {
        return [
            'Activo' => 'boolean',
        ];
    }

    public function getAuthPassword()
    {
        return $this->Pass;
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'id');
    }
}
