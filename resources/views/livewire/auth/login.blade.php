<div class="w-full max-w-sm">
    <div class="flex flex-col items-center mb-6">
        <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-50 text-brand-600 mb-3">
            <x-icon.coffee-bean class="w-8 h-8" />
        </span>
        <p class="font-semibold text-gray-900 text-lg leading-tight">Café Coocentral</p>
        <p class="text-xs text-gray-400 leading-tight">Pasión que sabe a origen</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-8">
        <h1 class="text-lg font-semibold text-gray-900 mb-1">Iniciar sesión</h1>
        <p class="text-sm text-gray-400 mb-6">Ingresa con tu cuenta para continuar</p>

        <form wire:submit="autenticar" class="space-y-4">
            <label class="block">
                <span class="text-xs text-gray-400">Correo</span>
                <input
                    type="email"
                    wire:model="email"
                    autofocus
                    autocomplete="username"
                    placeholder="correo@cafecoocentral.com"
                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200"
                />
                @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </label>

            <label class="block">
                <span class="text-xs text-gray-400">Contraseña</span>
                <input
                    type="password"
                    wire:model="password"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200"
                />
                @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="remember" class="rounded border-gray-300 text-brand-600 focus:ring-brand-200" />
                <span class="text-sm text-gray-600">Recordarme</span>
            </label>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="autenticar"
                class="w-full flex items-center justify-center gap-2 rounded-xl bg-brand-600 text-white font-semibold py-2.5 hover:bg-brand-700 disabled:opacity-60 transition-colors"
            >
                <span wire:loading.remove wire:target="autenticar">Iniciar sesión</span>
                <span wire:loading wire:target="autenticar">Ingresando...</span>
            </button>
        </form>
    </div>

    <p class="mt-6 flex items-center justify-center gap-1.5 text-[11px] text-gray-400">
        <x-heroicon-o-lock-closed class="w-3.5 h-3.5" />
        Tu sesión está protegida y encriptada
    </p>
</div>
