<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ruta_id', 'cliente_id', 'numero_factura', 'facturado_at'])]
class RutaCliente extends Model
{
    protected function casts(): array
    {
        return [
            'facturado_at' => 'datetime',
        ];
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function productos()
    {
        return $this->hasMany(RutaClienteProducto::class);
    }
}
