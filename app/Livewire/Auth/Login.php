<?php

namespace App\Livewire\Auth;

use App\Support\Modulos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::guest', ['title' => 'Iniciar sesión'])]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = true;

    public function autenticar()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [], ['email' => 'correo', 'password' => 'contraseña']);

        $clave = Str::lower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($clave, 5)) {
            $segundos = RateLimiter::availableIn($clave);
            $this->addError('email', "Demasiados intentos. Intenta de nuevo en {$segundos} segundos.");

            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($clave, 60);
            $this->addError('email', 'Las credenciales no coinciden con ningún registro.');

            return;
        }

        RateLimiter::clear($clave);
        request()->session()->regenerate();

        $primerModulo = collect(Modulos::claves())->first(fn ($clave) => Auth::user()->puedeVer($clave));

        if (! $primerModulo) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            $this->addError('email', 'Tu usuario no tiene módulos asignados. Contacta a un administrador.');

            return;
        }

        // No usamos redirect()->intended() aquí: Laravel guarda como "intended"
        // la última URL protegida que el usuario visitó sin sesión (con mucha
        // frecuencia "/", que requiere pedidos.nuevo). Si el usuario no tiene
        // permiso sobre esa URL, intended() lo mandaría de vuelta ahí y vería
        // "Acceso restringido" en vez de su primer módulo habilitado.
        return redirect()->to(route($primerModulo));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
