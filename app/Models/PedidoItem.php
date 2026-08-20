<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'pedido_id', 'producto_id', 'producto_nombre', 'producto_codigo',
    'presentacion', 'molienda', 'precio_unitario', 'cantidad', 'descuento_linea', 'total',
])]
class PedidoItem extends Model
{
    use HasFactory;

    protected $table = 'pedido_items';

    public const MOLIENDAS = [
        'entero' => 'En grano',
        'fina' => 'Molienda fina',
        'media' => 'Molienda media',
        'gruesa' => 'Molienda gruesa',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:2',
            'descuento_linea' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
