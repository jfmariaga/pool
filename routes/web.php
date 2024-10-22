<?php

use Illuminate\Support\Facades\Route;
// middleware
use App\Http\Middleware\Auth as AuthGuard;
// end middleware

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;

use App\Livewire\Pruebas\PruebaVelocidad;
use App\Livewire\Auth\Login;
use App\Livewire\Categoria\Categorias;
use App\Livewire\Compras\Compras;
use App\Livewire\Compras\FormCompra;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Productos\Productos;
use App\Livewire\Proveedores\Proveedores;
use App\Livewire\Usuarios\Usuarios;
use App\Livewire\Cuentas\Cuentas;
use App\Livewire\Movimientos\Movimientos;
use App\Livewire\AjusteInventario\AjusteInventario;
use App\Livewire\AjusteInventario\FormAjusteInventario;
use App\Livewire\Ventas\Ventas;
use App\Livewire\Ventas\FormVentas;
use App\Livewire\CierreCaja\CierreCaja;
use App\Livewire\Roles\Roles;

Route::get('/pruebas', PruebaVelocidad::class);

Route::get('/', Login::class);
Route::get('/login', Login::class);

Route::middleware([AuthGuard::class])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard')->middleware('can:ver dashboard');

    Route::get('/usuarios', Usuarios::class)->name('usuarios')->middleware('can:ver usuarios');

    Route::get('/categorias', Categorias::class)->name('categorias')->middleware('can:ver categorias');

    Route::get('/productos', Productos::class)->name('productos')->middleware('can:ver productos');

    Route::get('/proveedores', Proveedores::class)->name('proveedores')->middleware('can:ver proveedores');

    Route::get('/cuentas', Cuentas::class)->name('cuentas')->middleware('can:ver cuentas');

    Route::get('/cierre-caja', CierreCaja::class)->name('cierre-caja')->middleware('can:ver cierre-caja');

    Route::get('/compras', Compras::class)->name('compras')->middleware('can:ver compras');

    Route::get('/form-compra/{compra_id?}', FormCompra::class)->name('form-compra')->middleware('can:crear compras');

    Route::get('/ventas', Ventas::class)->name('ventas')->middleware('can:ver ventas');

    Route::get('/form-ventas/{venta_id?}', FormVentas::class)->name('form-ventas')->middleware('can:crear ventas');

    Route::get('/ajuste-inventario', AjusteInventario::class)->name('ajuste-inventario')->middleware('can:ver ajuste-inventario');

    Route::get('/form-ajuste-inventario/{ajuste_id?}', FormAjusteInventario::class)->name('form-ajuste-inventario')->middleware('can:crear ajuste-inventario');

    Route::get('/movimientos', Movimientos::class)->name('movimientos')->middleware('can:ver movimientos');

    Route::get('/roles', Roles::class)->name('roles')->middleware('can:ver roles');
});

//logout
Route::post('logout', function () {
    Auth::logout();
    Session::flush();
    Artisan::call('cache:clear');

    return redirect('/login');
})->name('cerrar-sesion');
