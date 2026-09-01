<?php

namespace App\Livewire\Usuarios;

use App\Models\User;
use App\Support\Modulos;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app', ['title' => 'Usuarios', 'subtitle' => 'Administra las cuentas y permisos de tu equipo', 'icon' => 'user-group'])]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'Vendedor';

    public bool $is_admin = false;

    public bool $solo_lectura = false;

    public array $modulos = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function usuarios()
    {
        return User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(20);
    }

    #[Computed]
    public function modulosDisponibles()
    {
        return Modulos::todos();
    }

    public function nuevo(): void
    {
        $this->reset(['editingId', 'name', 'email', 'password', 'modulos']);
        $this->role = 'Vendedor';
        $this->is_admin = false;
        $this->solo_lectura = false;
        $this->showModal = true;
    }

    public function editar(int $id): void
    {
        $usuario = User::findOrFail($id);
        $this->editingId = $usuario->id;
        $this->name = $usuario->name;
        $this->email = $usuario->email;
        $this->password = '';
        $this->role = $usuario->role ?? 'Vendedor';
        $this->is_admin = $usuario->is_admin;
        $this->solo_lectura = $usuario->solo_lectura;
        $this->modulos = $usuario->modulos ?? [];
        $this->showModal = true;
    }

    public function guardar(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'password' => $this->editingId ? 'nullable|string|min:6' : 'required|string|min:6',
            'role' => 'required|string|max:255',
            'is_admin' => 'boolean',
            'solo_lectura' => 'boolean',
            'modulos' => 'array',
            'modulos.*' => Rule::in(Modulos::claves()),
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_admin' => $data['is_admin'],
            // Un admin siempre tiene acceso total; "solo lectura" no aplica.
            'solo_lectura' => $data['is_admin'] ? false : $data['solo_lectura'],
            'modulos' => $data['is_admin'] ? [] : $data['modulos'],
        ];

        if ($data['password']) {
            $payload['password'] = Hash::make($data['password']);
        }

        User::updateOrCreate(['id' => $this->editingId], $payload);

        $this->showModal = false;
        session()->flash('success', 'Usuario guardado correctamente.');
    }

    public function eliminar(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propia cuenta.');

            return;
        }

        User::whereKey($id)->delete();
    }

    public function render()
    {
        return view('livewire.usuarios.index');
    }
}
