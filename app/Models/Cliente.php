<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'tipo_persona', 'documento', 'codigo', 'telefono', 'email', 'direccion', 'ciudad', 'tipo_precio', 'puntos'])]
class Cliente extends Model
{
    use HasFactory;

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}
