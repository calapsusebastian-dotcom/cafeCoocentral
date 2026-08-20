<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ruta_cliente_id', 'producto_id', 'producto_nombre', 'producto_codigo', 'presentacion', 'molienda', 'precio_unitario', 'cantidad'])]
class RutaClienteProducto extends Model
{
    protected $table = 'ruta_cliente_productos';

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:2',
        ];
    }

    public function rutaCliente()
    {
        return $this->belongsTo(RutaCliente::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
