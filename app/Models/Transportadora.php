<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'costo', 'activo'])]
class Transportadora extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'costo' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}
