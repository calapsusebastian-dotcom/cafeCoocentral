<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ruta_id', 'cliente_id', 'orden', 'medio_pago', 'numero_factura', 'facturado_at'])]
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

    public function medioPagoLabel(): string
    {
        return match ($this->medio_pago) {
            'pendiente' => 'Pendiente',
            'efectivo' => 'Efectivo',
            'transferencia' => 'Transferencia',
            'credito_30' => 'Crédito 30 días',
            'credito_45' => 'Crédito 45 días',
            'credito_60' => 'Crédito 60 días',
            'credito_90' => 'Crédito 90 días',
            default => ucfirst((string) $this->medio_pago),
        };
    }
}
