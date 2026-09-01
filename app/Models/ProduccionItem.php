<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['produccion_id', 'producto_id', 'producto_nombre', 'producto_codigo', 'presentacion', 'molienda', 'cantidad'])]
class ProduccionItem extends Model
{
    use HasFactory;

    public function produccion()
    {
        return $this->belongsTo(Produccion::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
