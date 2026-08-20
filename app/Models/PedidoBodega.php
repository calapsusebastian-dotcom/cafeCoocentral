<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['numero', 'fecha_pedido', 'bodega_id', 'user_id', 'status', 'notas', 'recibido_at'])]
class PedidoBodega extends Model
{
    use HasFactory;

    protected $table = 'pedidos_bodega';

    protected function casts(): array
    {
        return [
            'fecha_pedido' => 'date',
            'recibido_at' => 'datetime',
        ];
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(PedidoBodegaItem::class);
    }
}
