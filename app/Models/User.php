<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'avatar_path', 'is_admin', 'solo_lectura', 'modulos'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'solo_lectura' => 'boolean',
            'modulos' => 'array',
        ];
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function puedeVer(string $moduloClave): bool
    {
        return $this->is_admin || in_array($moduloClave, $this->modulos ?? [], true);
    }

    /**
     * Un usuario de solo lectura puede navegar y ver cualquier módulo al
     * que tenga acceso, pero ninguna acción que cree, edite o elimine
     * información debe ejecutarse para él (ver [[GuardaSoloLectura]]).
     */
    public function puedeEscribir(): bool
    {
        return ! $this->solo_lectura;
    }
}
