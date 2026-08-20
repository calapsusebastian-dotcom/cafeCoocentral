<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'titulo', 'mensaje', 'tipo', 'leida', 'leida_at'])]
class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected function casts(): array
    {
        return [
            'leida' => 'boolean',
            'leida_at' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    #[Scope]
    protected function unread(Builder $query): void
    {
        $query->where('leida', false);
    }

    public function marcarLeida(): void
    {
        $this->update(['leida' => true, 'leida_at' => now()]);
    }
}
