<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'numero', 'nombre', 'fecha', 'user_id', 'status', 'notas', 'recibida_at',
    'conductor_nombre', 'conductor_cc', 'costo_ruta', 'centro_costo', 'despachada_at', 'entregada_at',
])]
class Ruta extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'recibida_at' => 'datetime',
            'costo_ruta' => 'decimal:2',
            'despachada_at' => 'datetime',
            'entregada_at' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function clientes()
    {
        return $this->hasMany(RutaCliente::class)->orderBy('orden');
    }
}
