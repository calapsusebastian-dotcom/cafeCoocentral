<div>
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl bg-red-50 text-red-700 text-sm px-4 py-3">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
        <div class="flex flex-col sm:flex-row gap-3 mb-5">
            <div class="relative flex-1">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o correo..." class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
            </div>
            <button type="button" wire:click="nuevo" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition-colors shrink-0">
                <x-heroicon-o-plus class="w-4 h-4" />
                Nuevo usuario
            </button>
        </div>

        <div class="overflow-x-auto -mx-2">
            <table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                        <th class="px-2 py-2 font-medium">Usuario</th>
                        <th class="px-2 py-2 font-medium">Rol</th>
                        <th class="px-2 py-2 font-medium">Acceso</th>
                        <th class="px-2 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->usuarios as $usuario)
                        <tr class="border-b border-gray-50 last:border-0">
                            <td class="px-2 py-3">
                                <p class="font-medium text-gray-800">{{ $usuario->name }}</p>
                                <p class="text-xs text-gray-400">{{ $usuario->email }}</p>
                            </td>
                            <td class="px-2 py-3 text-gray-500">{{ $usuario->role }}</td>
                            <td class="px-2 py-3">
                                @if ($usuario->is_admin)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-brand-50 text-brand-700">Administrador · todo</span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($usuario->modulos ?? [] as $clave)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-600">{{ $this->modulosDisponibles[$clave]['label'] ?? $clave }}</span>
                                        @empty
                                            <span class="text-xs text-gray-400">Sin módulos asignados</span>
                                        @endforelse
                                    </div>
                                @endif
                            </td>
                            <td class="px-2 py-3 text-right space-x-2">
                                <button type="button" wire:click="editar({{ $usuario->id }})" class="text-gray-400 hover:text-brand-600">
                                    <x-heroicon-o-pencil-square class="w-4 h-4 inline" />
                                </button>
                                <button type="button" wire:click="eliminar({{ $usuario->id }})" wire:confirm="¿Eliminar este usuario?" class="text-gray-400 hover:text-red-500">
                                    <x-heroicon-o-trash class="w-4 h-4 inline" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-2 py-10 text-center text-gray-400">No hay usuarios que coincidan con la búsqueda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $this->usuarios->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-30 flex items-center justify-center bg-gray-900/40 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[85vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $editingId ? 'Editar usuario' : 'Nuevo usuario' }}</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="block sm:col-span-2">
                        <span class="text-xs text-gray-400">Nombre</span>
                        <input type="text" wire:model="name" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Correo</span>
                        <input type="email" wire:model="email" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Rol (texto descriptivo)</span>
                        <input type="text" wire:model="role" placeholder="Vendedor, Bodega..." class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('role') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="text-xs text-gray-400">{{ $editingId ? 'Nueva contraseña (opcional)' : 'Contraseña' }}</span>
                        <input type="password" wire:model="password" placeholder="••••••••" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>

                <label class="flex items-center gap-2 mt-4">
                    <input type="checkbox" wire:model="is_admin" class="rounded border-gray-300 text-brand-600 focus:ring-brand-200" />
                    <span class="text-sm text-gray-700 font-medium">Administrador (acceso a todos los módulos)</span>
                </label>

                @unless ($is_admin)
                    <div class="mt-4">
                        <span class="text-xs text-gray-400">Módulos permitidos</span>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            @foreach ($this->modulosDisponibles as $clave => $modulo)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" wire:model="modulos" value="{{ $clave }}" class="rounded border-gray-300 text-brand-600 focus:ring-brand-200" />
                                    {{ $modulo['label'] }}
                                </label>
                            @endforeach
                        </div>
                        @error('modulos') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endunless

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Cancelar</button>
                    <button type="button" wire:click="guardar" class="px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700">Guardar</button>
                </div>
            </div>
        </div>
    @endif
</div>
