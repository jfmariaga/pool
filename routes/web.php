<?php

use Illuminate\Support\Facades\Route;
// middleware
use App\Http\Middleware\Auth as AuthGuard;
// end middleware

use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Route;
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

Route::get('/pruebas', PruebaVelocidad::class);

Route::get('/', Login::class);
Route::get('/login', Login::class);
// Route::post('/logout', Login::class, 'logout')->name('logout');

Route::middleware([AuthGuard::class])->group(function () {
    Route::get('/dashboard'  , Dashboard::class)->name('dashboard');
    Route::get('/usuarios'   , Usuarios::class)->name('usuarios');
    Route::get('/categorias' , Categorias::class)->name('categorias');
    Route::get('/productos'  , Productos::class)->name('productos');
    Route::get('/proveedores', Proveedores::class)->name('proveedores');
    Route::get('/cuentas'    , Cuentas::class)->name('cuentas');
    Route::get('/compras'    , Compras::class)->name('compras');
    Route::get('/form-compra/{compra_id?}'    , FormCompra::class)->name('form-compra');
    Route::get('/movimientos', Movimientos::class)->name('movimientos');
});

//logout
Route::post('logout', function (){
    Auth::logout();
    Session::flush();
    Artisan::call('cache:clear');

    return redirect('/login');
})->name('cerrar-sesion');
