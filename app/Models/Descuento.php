<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'tipo', 'valor', 'activo'])]
class Descuento extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function montoPara(float $subtotal): float
    {
        return $this->tipo === 'porcentaje'
            ? round($subtotal * ((float) $this->valor / 100), 2)
            : (float) $this->valor;
    }
}
