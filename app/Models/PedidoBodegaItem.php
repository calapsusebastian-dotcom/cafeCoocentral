<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['pedido_bodega_id', 'producto_id', 'producto_nombre', 'producto_codigo', 'presentacion', 'molienda', 'cantidad'])]
class PedidoBodegaItem extends Model
{
    use HasFactory;

    protected $table = 'pedido_bodega_items';

    public function pedidoBodega()
    {
        return $this->belongsTo(PedidoBodega::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
