<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'categoria', 'presentacion', 'sku', 'precio', 'stock', 'imagen_path', 'activo', 'descripcion'])]
class Producto extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function pedidoItems()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    #[Scope]
    protected function activos(Builder $query): void
    {
        $query->where('activo', true);
    }
}
