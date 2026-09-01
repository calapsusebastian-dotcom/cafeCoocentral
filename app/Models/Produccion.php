<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['numero', 'fecha_produccion', 'user_id', 'status', 'numero_imov', 'notas', 'trasladado_at'])]
class Produccion extends Model
{
    use HasFactory;

    protected $table = 'producciones';

    protected function casts(): array
    {
        return [
            'fecha_produccion' => 'date',
            'trasladado_at' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(ProduccionItem::class);
    }
}
