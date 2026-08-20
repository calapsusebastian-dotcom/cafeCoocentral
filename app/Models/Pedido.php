<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'numero', 'destino', 'fecha_pedido', 'cliente_id', 'direccion_entrega', 'user_id', 'subtotal', 'descuento_id', 'descuento_monto',
    'transportadora_id', 'numero_guia', 'despachado_at', 'numero_factura', 'facturado_at', 'envio_costo', 'total', 'puntos_generados',
    'medio_pago', 'notas', 'status',
])]
class Pedido extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'fecha_pedido' => 'date',
            'despachado_at' => 'datetime',
            'facturado_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'descuento_monto' => 'decimal:2',
            'envio_costo' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function descuento()
    {
        return $this->belongsTo(Descuento::class);
    }

    public function transportadora()
    {
        return $this->belongsTo(Transportadora::class);
    }

    public function items()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
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

    public function destinoLabel(): string
    {
        return match ($this->destino) {
            'web' => 'Web',
            default => 'Local',
        };
    }
}
