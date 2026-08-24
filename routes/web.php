<?php

use App\Livewire\Auth\Login;
use App\Livewire\Bodegas;
use App\Livewire\Clientes;
use App\Livewire\Descuentos;
use App\Livewire\EditarPedido;
use App\Livewire\EditarPedidoBodega;
use App\Livewire\EditarRuta;
use App\Livewire\Facturacion;
use App\Livewire\Inventario;
use App\Livewire\Notificaciones;
use App\Livewire\NuevaRuta;
use App\Livewire\NuevoPedido;
use App\Livewire\NuevoPedidoBodega;
use App\Livewire\Pedidos;
use App\Livewire\PedidosBodega;
use App\Livewire\PedidosWeb;
use App\Livewire\Productos;
use App\Livewire\Reportes;
use App\Livewire\Rutas;
use App\Livewire\Usuarios;
use App\Models\Pedido;
use App\Models\PedidoBodega;
use App\Models\Ruta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/login', Login::class)->name('login')->middleware('guest');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::livewire('/', NuevoPedido::class)->name('pedidos.nuevo')->middleware('modulo:pedidos.nuevo');

    Route::middleware('modulo:pedidos.index')->group(function () {
        Route::livewire('/pedidos', Pedidos\Index::class)->name('pedidos.index');
        Route::livewire('/pedidos/{pedido}/editar', EditarPedido::class)->name('pedidos.editar');
        Route::get('/pedidos/{pedido}/imprimir', function (Pedido $pedido) {
            $pedido->load(['items', 'cliente', 'vendedor', 'transportadora', 'descuento']);

            return view('pedidos.imprimir', ['pedido' => $pedido]);
        })->name('pedidos.imprimir');
    });

    Route::livewire('/pedidos-web', PedidosWeb\Index::class)->name('pedidos-web.index')->middleware('modulo:pedidos-web.index');
    Route::livewire('/facturacion', Facturacion\Index::class)->name('facturacion.index')->middleware('modulo:facturacion.index');
    Route::livewire('/clientes', Clientes\Index::class)->name('clientes.index')->middleware('modulo:clientes.index');
    Route::livewire('/productos', Productos\Index::class)->name('productos.index')->middleware('modulo:productos.index');
    Route::livewire('/inventario', Inventario\Index::class)->name('inventario.index')->middleware('modulo:inventario.index');

    Route::middleware('modulo:pedidos-bodega.index')->group(function () {
        Route::livewire('/pedidos-bodega', PedidosBodega\Index::class)->name('pedidos-bodega.index');
        Route::livewire('/pedidos-bodega/nuevo', NuevoPedidoBodega::class)->name('pedidos-bodega.nuevo');
        Route::livewire('/pedidos-bodega/{pedidoBodega}/editar', EditarPedidoBodega::class)->name('pedidos-bodega.editar');
        Route::get('/pedidos-bodega/{pedidoBodega}/imprimir', function (PedidoBodega $pedidoBodega) {
            $pedidoBodega->load(['items', 'bodega', 'usuario']);

            return view('pedidos-bodega.imprimir', ['pedidoBodega' => $pedidoBodega]);
        })->name('pedidos-bodega.imprimir');
    });

    Route::livewire('/bodegas', Bodegas\Index::class)->name('bodegas.index')->middleware('modulo:bodegas.index');

    Route::middleware('modulo:rutas.index')->group(function () {
        Route::livewire('/rutas', Rutas\Index::class)->name('rutas.index');
        Route::livewire('/rutas/nueva', NuevaRuta::class)->name('rutas.nueva');
        Route::livewire('/rutas/{ruta}/editar', EditarRuta::class)->name('rutas.editar');
        Route::get('/rutas/{ruta}/imprimir', function (Ruta $ruta) {
            $ruta->load(['usuario', 'clientes.cliente', 'clientes.productos']);

            $porProducto = $ruta->clientes
                ->flatMap(fn ($rutaCliente) => $rutaCliente->productos->map(fn ($producto) => (object) [
                    'producto_nombre' => $producto->producto_nombre,
                    'producto_codigo' => $producto->producto_codigo,
                    'presentacion' => $producto->presentacion,
                    'molienda' => $producto->molienda,
                    'cantidad' => $producto->cantidad,
                ]))
                ->groupBy(fn ($linea) => $linea->producto_nombre.'|'.$linea->presentacion)
                ->map(fn ($lineas) => [
                    'nombre' => $lineas->first()->producto_nombre,
                    'codigo' => $lineas->first()->producto_codigo,
                    'presentacion' => $lineas->first()->presentacion,
                    'total' => $lineas->sum('cantidad'),
                    'moliendas' => $lineas->groupBy('molienda')->map(fn ($grupo) => [
                        'total' => $grupo->sum('cantidad'),
                    ]),
                ])
                ->values();

            return view('rutas.imprimir', ['ruta' => $ruta, 'porProducto' => $porProducto]);
        })->name('rutas.imprimir');
    });

    Route::livewire('/reportes', Reportes\Index::class)->name('reportes.index')->middleware('modulo:reportes.index');
    Route::livewire('/descuentos', Descuentos\Index::class)->name('descuentos.index')->middleware('modulo:descuentos.index');
    Route::livewire('/notificaciones', Notificaciones\Index::class)->name('notificaciones.index');

    Route::livewire('/usuarios', Usuarios\Index::class)->name('usuarios.index')->middleware('admin');
});
