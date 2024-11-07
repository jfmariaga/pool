<?php

namespace App\Livewire\Ventas;

use App\Models\Categoria;
use App\Models\Cuenta;
use App\Models\DetCompra;
use App\Models\DetVenta;
use App\Models\Movimiento;
use App\Models\Producto;
use Livewire\Component;
use App\Models\Venta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Ventas extends Component
{
    public $ventaId;
    public $productos = [];
    public $descripcion;
    public $montoTotal = 0;
    public $cuenta_id;
    public $cuentas;
    public $categorias;
    public $productosall;
    public $producto_id;
    public $cantidad = 1;
    public $venta_mayorista = false;
    public $metodosPago = [];
    public $totalEgresos;

    public $listeners = [
        'metodo-monto-actualizado' => 'actualizarMontoMetodoPago',
    ];

    public function mount()
    {
        // Inicialización de los productos y otras variables
        $this->productosall = Producto::all();
        $this->categorias  = Categoria::where('status', 1)->get();
        $this->descripcion = '';
        $this->montoTotal = 0;
        $this->cuentas = Cuenta::all();
        $this->metodosPago = [];
    }

    public function actualizarMontoMetodoPago($index, $monto)
    {
        // Actualizar el monto en la posición correcta
        $this->metodosPago[$index]['monto'] = $monto;
    }

    public function render()
    {
        return view('livewire.ventas.ventas');
    }

    public function getTabla()
    {
        $this->skipRender();

        $ventas = Venta::with([
            'detVentas.producto',
            'usuario',
            'cuenta',        // Relación para la cuenta directa
            'cuentas',       // Relación para pagos combinados
            'movimientos' => function ($query) {
                $query->where('tipo', 'egreso'); // Filtrar solo los egresos
            }
        ])->get()->map(function ($venta) {

            $metodosPago = collect();

            // Agregar cuenta directa si existe (casos antiguos)
            if ($venta->cuenta) {
                $metodosPago->push([
                    'nombre' => $venta->cuenta->nombre,
                    'monto' => $venta->monto_total,
                ]);
            }

            // Agregar cuentas combinadas de la relación pivote si existen
            if ($venta->cuentas && $venta->cuentas->count() > 0) {
                foreach ($venta->cuentas as $cuenta) {
                    $metodosPago->push([
                        'nombre' => $cuenta->nombre,
                        'monto' => $cuenta->pivot->monto,
                    ]);
                }
            }

            // Definir el campo `metodoPago` basado en los métodos de pago disponibles
            if ($metodosPago->count() > 1) {
                $venta->metodoPago = 'Pago Combinado';
            } elseif ($metodosPago->count() == 1) {
                $venta->metodoPago = $metodosPago->first()['nombre'];
            } else {
                $venta->metodoPago = 'N/A';
            }

            // Asigna los métodos de pago detallados para el comprobante
            $venta->metodosPago = $metodosPago->toArray();

            // Calcular el total de egresos (si existen)
            $venta->totalEgresos = $venta->movimientos->sum('monto');

            return $venta;
        })->toArray();

        return $ventas;
    }


    // public function agregarProducto()
    // {
    //     $this->validate([
    //         'producto_id' => 'required',
    //     ]);

    //     $producto = Producto::find($this->producto_id);

    //     if (!$producto) {
    //         $this->dispatch('showToast', ['type' => 'error', 'message' => 'Producto no encontrado.']);
    //         return;
    //     }

    //     if (!$producto->stock_infinito) {
    //         // Si el producto tiene stock limitado
    //         $stockDisponible = DetCompra::where('producto_id', $producto->id)->sum('stock');

    //         if ($this->cantidad > $stockDisponible) {
    //             $this->dispatch('showToast', ['type' => 'error', 'message' => "No hay suficiente stock. Disponible: $stockDisponible unidades."]);
    //             return;
    //         }

    //         // Obtener las DetCompras más antiguas (ordenadas por fecha de creación)
    //         $detCompras = DetCompra::where('producto_id', $producto->id)
    //             ->where('stock', '>', 0)
    //             ->orderBy('created_at', 'asc')
    //             ->get();

    //         DB::transaction(function () use ($detCompras, $producto) {
    //             $cantidadRestante = $this->cantidad;
    //             $montoNuevoProducto = 0;

    //             foreach ($detCompras as $detCompra) {
    //                 if ($cantidadRestante <= 0) break;

    //                 $stockDisponible = $detCompra->stock;
    //                 $cantidadAUtilizar = min($cantidadRestante, $stockDisponible);

    //                 $detCompra->stock -= $cantidadAUtilizar;
    //                 $detCompra->save();

    //                 // Usar el precio mayorista si está activo
    //                 $precio = $this->venta_mayorista && $producto->precio_mayorista > 0 ? $producto->precio_mayorista : $producto->precio_base;

    //                 DetVenta::create([
    //                     'venta_id' => $this->ventaId,
    //                     'producto_id' => $producto->id,
    //                     'cant' => $cantidadAUtilizar,
    //                     'precio_venta' => $precio,
    //                     'det_compra_id' => $detCompra->id,
    //                 ]);

    //                 $cantidadRestante -= $cantidadAUtilizar;

    //                 $this->productos[] = [
    //                     'producto_id' => $producto->id,
    //                     'nombre' => $producto->nombre,
    //                     'precio' => $precio,
    //                     'cantidad' => $cantidadAUtilizar,
    //                 ];

    //                 $montoNuevoProducto += $precio * $cantidadAUtilizar;
    //             }

    //             $this->montoTotal += $montoNuevoProducto;
    //             $this->dispatch('showToast', [
    //                 'type' => 'success',
    //                 'message' => 'Se agregó correctamente el producto'
    //             ]);
    //         });
    //     } else {
    //         // Si el producto tiene stock infinito
    //         DB::transaction(function () use ($producto) {
    //             $precio = $this->venta_mayorista && $producto->precio_mayorista > 0 ? $producto->precio_mayorista : $producto->precio_base;

    //             DetVenta::create([
    //                 'venta_id' => $this->ventaId,
    //                 'producto_id' => $producto->id,
    //                 'cant' => $this->cantidad,
    //                 'precio_venta' => $precio,
    //                 'det_compra_id' => 0,
    //             ]);

    //             $this->productos[] = [
    //                 'producto_id' => $producto->id,
    //                 'nombre' => $producto->nombre,
    //                 'precio' => $precio,
    //                 'cantidad' => $this->cantidad,
    //             ];

    //             $this->montoTotal += $precio * $this->cantidad;
    //             $this->dispatch('showToast', [
    //                 'type' => 'success',
    //                 'message' => 'Se agregó correctamente el producto'
    //             ]);
    //         });
    //     }

    //     $this->cantidad = 1;
    // }

    public function agregarProducto()
    {
        $this->validate([
            'producto_id' => 'required',
        ]);

        $producto = Producto::find($this->producto_id);

        if (!$producto) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Producto no encontrado.']);
            return;
        }

        if (!$producto->stock_infinito) {
            // Si el producto tiene stock limitado
            $stockDisponible = DetCompra::where('producto_id', $producto->id)->sum('stock');

            if ($this->cantidad > $stockDisponible) {
                $this->dispatch('showToast', ['type' => 'error', 'message' => "No hay suficiente stock. Disponible: $stockDisponible unidades."]);
                return;
            }

            $detCompras = DetCompra::where('producto_id', $producto->id)
                ->where('stock', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            $cantidadRestante = $this->cantidad;
            $montoNuevoProducto = 0;

            foreach ($detCompras as $detCompra) {
                if ($cantidadRestante <= 0) break;

                $cantidadAUtilizar = min($cantidadRestante, $detCompra->stock);

                // Actualiza solo en la memoria de Livewire, no en la base de datos
                $detCompra->stock -= $cantidadAUtilizar;

                $precio = $this->venta_mayorista && $producto->precio_mayorista > 0 ? $producto->precio_mayorista : $producto->precio_base;

                $this->productos[] = [
                    'producto_id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'precio' => $precio,
                    'cantidad' => $cantidadAUtilizar,
                    'det_compra_id' => $detCompra->id
                ];

                $montoNuevoProducto += $precio * $cantidadAUtilizar;
                $cantidadRestante -= $cantidadAUtilizar;
            }

            $this->montoTotal += $montoNuevoProducto;
            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => 'Se agregó correctamente el producto'
            ]);
        } else {
            // Si el producto tiene stock infinito
            $precio = $this->venta_mayorista && $producto->precio_mayorista > 0 ? $producto->precio_mayorista : $producto->precio_base;

            $this->productos[] = [
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio' => $precio,
                'cantidad' => $this->cantidad,
                'det_compra_id' => 0
            ];

            $this->montoTotal += $precio * $this->cantidad;
            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => 'Se agregó correctamente el producto'
            ]);
        }

        $this->cantidad = 1;
    }

    public function updateVenta()
    {
        // Calcular el monto total de los métodos de pago ingresados
        $montoTotalMetodosPago = array_sum(array_column($this->metodosPago, 'monto'));

        // Validar que la suma de los métodos de pago coincida con el monto total de la venta
        if ($montoTotalMetodosPago != $this->montoTotal) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'El monto de los métodos de pago no coincide con el total de la venta. Ajuste los montos antes de continuar.'
            ]);
            return; // Detener si la validación falla
        }

        // Iniciar la transacción de actualización solo si la validación pasa
        DB::transaction(function () {
            $venta = Venta::find($this->ventaId);

            if (!$venta) {
                $this->dispatch('showToast', [['type' => 'error', 'message' => 'Venta no encontrada.']]);
                return;
            }

            // Actualiza la información de la venta
            $venta->descripcion = $this->descripcion;
            $venta->venta_mayorista = $this->venta_mayorista;
            $venta->monto_total = $this->montoTotal;
            $venta->save();

            // Inicializa el monto total de la venta basado en los productos
            $nuevoMontoTotal = 0;

            // Actualiza los productos en la venta y ajusta inventario
            foreach ($this->productos as $producto) {
                $detVenta = DetVenta::where('venta_id', $this->ventaId)
                    ->where('producto_id', $producto['producto_id'])
                    ->first();

                if ($detVenta) {
                    $cantidadAnterior = $detVenta->cant;
                    $nuevaCantidad = $producto['cantidad'];

                    // Ajusta el inventario si hay cambios en la cantidad
                    if ($cantidadAnterior !== $nuevaCantidad) {
                        $diferencia = abs($nuevaCantidad - $cantidadAnterior);
                        $detCompra = DetCompra::find($detVenta->det_compra_id);

                        if ($detCompra) {
                            if ($nuevaCantidad > $cantidadAnterior) {
                                // Restar del inventario si se incrementa la cantidad en la venta
                                $detCompra->stock -= $diferencia;
                            } else {
                                // Devolver al inventario si se disminuye la cantidad en la venta
                                $detCompra->stock += $diferencia;
                            }
                            $detCompra->save();
                        }
                    }

                    // Actualiza los detalles de la venta
                    $detVenta->cant = $nuevaCantidad;
                    $detVenta->precio_venta = $producto['precio'];
                    $detVenta->save();
                } else {
                    // Crear un nuevo detalle de venta si no existe
                    DetVenta::create([
                        'venta_id' => $this->ventaId,
                        'producto_id' => $producto['producto_id'],
                        'cant' => $producto['cantidad'],
                        'precio_venta' => $producto['precio'],
                    ]);
                }

                // Acumula el monto total basado en los productos
                $nuevoMontoTotal += $producto['precio'] * $producto['cantidad'];
            }

            // Elimina los productos que ya no están en la lista
            $productosIds = collect($this->productos)->pluck('producto_id');
            DetVenta::where('venta_id', $this->ventaId)->whereNotIn('producto_id', $productosIds)->delete();

            // Actualiza los métodos de pago combinados
            $venta->cuentas()->detach(); // Elimina las cuentas asociadas actuales

            foreach ($this->metodosPago as $metodo) {
                if (isset($metodo['cuenta_id'])) {
                    $venta->cuentas()->attach($metodo['cuenta_id'], ['monto' => $metodo['monto']]);
                }
            }

            // Ajustar los movimientos relacionados con la venta, como egresos, si existen
            $movimiento = Movimiento::where('venta_id', $this->ventaId)->first();
            if ($movimiento) {
                $movimiento->monto = $nuevoMontoTotal;
                $movimiento->save();
            }

            // Actualiza el monto total de la venta
            $venta->monto_total = $nuevoMontoTotal;
            $venta->save();

            // Resetear productos y otros campos para una nueva edición
            $this->productos = [];
            $this->reset('producto_id', 'cantidad');
            $this->getTabla();
        });

        $this->dispatch('closeModal');
    }


    public function editVenta($ventaId)
    {
        $venta = Venta::with('detVentas.producto', 'cuenta', 'cuentas', 'movimientos')->find($ventaId);

        if (!$venta) {
            $this->dispatch('showToast', [['type' => 'error', 'message' => 'Venta no encontrada.']]);
            return;
        }

        $this->ventaId = $venta->id;
        $this->descripcion = $venta->descripcion;
        $this->montoTotal = $venta->monto_total;
        $this->cuenta_id = $venta->cuenta_id;
        $this->venta_mayorista = $venta->venta_mayorista;

        // Cargar los productos de la venta
        $this->productos = $venta->detVentas->map(function ($detVenta) {
            return [
                'producto_id' => $detVenta->producto_id,
                'nombre' => $detVenta->producto->nombre,
                'precio' => $detVenta->precio_venta,
                'cantidad' => $detVenta->cant,
            ];
        })->toArray();

        // Cargar los métodos de pago
        $metodosPago = collect();
        if ($venta->cuenta) {
            $metodosPago->push([
                'cuenta_id' => $venta->cuenta->id,
                'nombre' => $venta->cuenta->nombre,
                'monto' => $venta->monto_total,
            ]);
        }
        foreach ($venta->cuentas as $cuenta) {
            $metodosPago->push([
                'cuenta_id' => $cuenta->id,
                'nombre' => $cuenta->nombre,
                'monto' => $cuenta->pivot->monto,
            ]);
        }

        $this->metodosPago = $metodosPago->toArray();

        // Cargar el total de egresos (devoluciones) relacionados con esta venta
        $this->totalEgresos = $venta->movimientos->where('tipo', 'egreso')->sum('monto');

        $this->recalcularPreciosVenta();
        $this->dispatch('openEditModal');
    }


    public function toggleVentaMayorista()
    {
        $this->venta_mayorista = !$this->venta_mayorista;
        $this->recalcularPreciosVenta();
    }

    public function recalcularPreciosVenta()
    {
        foreach ($this->productos as &$producto) {
            $productoModel = Producto::find($producto['producto_id']);
            $nuevoPrecio = $this->venta_mayorista && $productoModel->precio_mayorista > 0
                ? $productoModel->precio_mayorista
                : $productoModel->precio_base;

            $producto['precio'] = $nuevoPrecio;
        }

        $this->montoTotal = array_sum(array_map(function ($producto) {
            return $producto['precio'] * $producto['cantidad'];
        }, $this->productos));
    }


    // public function eliminarProducto($index)
    // {
    //     if (!isset($this->productos[$index])) {
    //         $this->dispatch('showToast', [['type' => 'error', 'message' => 'Producto no encontrado.']]);
    //         return;
    //     }

    //     $producto = $this->productos[$index];
    //     $detVenta = DetVenta::where('venta_id', $this->ventaId)
    //         ->where('producto_id', $producto['producto_id'])
    //         ->first();

    //     if ($detVenta) {
    //         if ($detVenta->det_compra_id) {
    //             $detCompra = DetCompra::find($detVenta->det_compra_id);
    //             if ($detCompra) {
    //                 $detCompra->increment('stock', $producto['cantidad']);
    //             }
    //         }

    //         $detVenta->delete();

    //         unset($this->productos[$index]);

    //         $this->montoTotal = array_sum(array_map(function ($producto) {
    //             return $producto['precio'] * $producto['cantidad'];
    //         }, $this->productos));

    //         Venta::find($this->ventaId)
    //             ->update(['monto_total' => $this->montoTotal]);

    //         $this->dispatch('showToast', ['type' => 'success', 'message' => 'Producto eliminado y stock actualizado.']);
    //     } else {
    //         $this->dispatch('showToast', ['type' => 'error', 'message' => 'El producto no fue encontrado en la base de datos.']);
    //     }
    // }
    public function eliminarProducto($index)
    {
        if (!isset($this->productos[$index])) {
            $this->dispatch('showToast', [['type' => 'error', 'message' => 'Producto no encontrado.']]);
            return;
        }

        $producto = $this->productos[$index];

        // Validar que el índice 'det_compra_id' exista y sea diferente de 0
        if (isset($producto['det_compra_id']) && $producto['det_compra_id'] !== 0) {
            $detCompra = DetCompra::find($producto['det_compra_id']);
            if ($detCompra) {
                $detCompra->stock += $producto['cantidad'];
                // $detCompra->save();
            }
        }

        // Remover el producto de la lista en memoria
        unset($this->productos[$index]);

        // Actualizar el monto total en memoria
        $this->montoTotal = array_sum(array_map(function ($producto) {
            return $producto['precio'] * $producto['cantidad'];
        }, $this->productos));

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Producto eliminado y stock actualizado.']);
    }


    public function deleteVenta($ventaId)
    {
        $this->skipRender(); // Evita renderizado

        DB::transaction(function () use ($ventaId) {
            // Obtener la venta con sus productos y movimiento asociado
            $venta = Venta::with('detVentas')->find($ventaId);

            if (!$venta) {
                $this->dispatch('showToast', [['type' => 'error', 'message' => 'Venta no encontrada.']]);
                return false;
            }

            // 1. Devolver productos al inventario
            foreach ($venta->detVentas as $detVenta) {
                if ($detVenta->det_compra_id) {
                    $detCompra = DetCompra::find($detVenta->det_compra_id);
                    if ($detCompra) {
                        $detCompra->increment('stock', $detVenta->cant);
                    }
                }
            }

            // 2. Ajustar el saldo de la cuenta
            $cuenta = Cuenta::find($venta->cuenta_id);
            if ($cuenta) {
                $cuenta->saldo -= $venta->monto_total;  // Se resta el monto total de la venta
                $cuenta->save();
            }

            // 3. Eliminar los detalles de la venta
            DetVenta::where('venta_id', $ventaId)->delete();

            // 4. Eliminar el movimiento relacionado con la venta
            Movimiento::where('venta_id', $ventaId)->delete();

            // 5. Eliminar la venta
            $venta->delete();

            $this->dispatch('showToast', [['type' => 'success', 'message' => 'Venta eliminada con éxito.']]);
        });

        return true;
    }


    public function validarYGuardar()
    {
        // dd($this->metodosPago);
        // Calcular el monto total de los métodos de pago ingresados y redondearlo a 2 decimales
        $montoTotalMetodosPago = round(array_sum(array_column($this->metodosPago, 'monto')), 2);
        $montoTotalVenta = round($this->montoTotal, 2);

        // Validar que la suma de los métodos de pago coincida con el monto total de la venta
        if ($montoTotalMetodosPago !== $montoTotalVenta) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'El monto de los métodos de pago no coincide con el total de la venta. Ajuste los montos antes de continuar.'
            ]);
            return; // Detener si la validación falla
        }

        // Validar que cada método de pago tenga cuenta_id, nombre y monto
        foreach ($this->metodosPago as $metodo) {
            if (empty($metodo['cuenta_id']) || !isset($metodo['monto'])) {
                $this->dispatch('showToast', [
                    'type' => 'error',
                    'message' => 'Cada método de pago debe tener una cuenta, nombre y monto específicos.'
                ]);
                return;
            }
        }

        // Iniciar la transacción
        DB::transaction(function () use ($montoTotalVenta) {
            // Crear o buscar la venta en la base de datos
            $venta = Venta::find($this->ventaId) ?? new Venta();
            $venta->descripcion = $this->descripcion;
            $venta->venta_mayorista = $this->venta_mayorista;
            $venta->monto_total = $this->montoTotal;
            $venta->save();

            // Gestionar los detalles de la venta y el inventario
            $productosExistentes = DetVenta::where('venta_id', $venta->id)->get();
            $productosNuevos = collect($this->productos)->keyBy('producto_id');

            // Ajustar inventario para productos eliminados
            foreach ($productosExistentes as $detalleExistente) {
                if (!$productosNuevos->has($detalleExistente->producto_id)) {
                    if ($detalleExistente->det_compra_id) {
                        $detCompra = DetCompra::find($detalleExistente->det_compra_id);
                        if ($detCompra) {
                            $detCompra->increment('stock', $detalleExistente->cant);
                        }
                    }
                    $detalleExistente->delete();
                }
            }

            // Crear o actualizar los detalles de venta y ajustar inventario para productos nuevos
            foreach ($this->productos as $producto) {
                $detVenta = DetVenta::where('venta_id', $venta->id)
                    ->where('producto_id', $producto['producto_id'])
                    ->first();

                $cantidadNueva = $producto['cantidad'];
                $detCompra = isset($producto['det_compra_id']) && $producto['det_compra_id'] != 0
                    ? DetCompra::find($producto['det_compra_id'])
                    : null;

                if ($detVenta) {
                    $cantidadAnterior = $detVenta->cant;
                    if ($cantidadAnterior !== $cantidadNueva && $detCompra) {
                        $ajuste = $cantidadNueva - $cantidadAnterior;
                        $detCompra->decrement('stock', $ajuste);
                    }
                    $detVenta->update([
                        'cant' => $cantidadNueva,
                        'precio_venta' => $producto['precio'],
                    ]);
                } else {
                    DetVenta::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $producto['producto_id'],
                        'cant' => $cantidadNueva,
                        'precio_venta' => $producto['precio'],
                        'det_compra_id' => $producto['det_compra_id'] ?? 0,
                    ]);
                    if ($detCompra) {
                        $detCompra->decrement('stock', $cantidadNueva);
                    }
                }
            }

            // Actualizar los métodos de pago de manera precisa sin sobrescribir erróneamente
            $venta->cuentas()->detach();
            foreach ($this->metodosPago as $metodo) {
                if (isset($metodo['cuenta_id'])) {
                    $venta->cuentas()->attach($metodo['cuenta_id'], ['monto' => $metodo['monto']]);
                }
            }

            // Eliminar movimientos existentes asociados a la venta
            Movimiento::where('venta_id', $venta->id)->delete();

            // Crear un movimiento para cada cuenta en los métodos de pago
            foreach ($this->metodosPago as $metodo) {
                if (isset($metodo['cuenta_id'])) {
                    Movimiento::create([
                        'venta_id' => $venta->id,
                        'cuenta_id' => $metodo['cuenta_id'],
                        'tipo' => 'ingreso',
                        'monto' => $metodo['monto'],
                        'descripcion' => "Ingreso por modificación de venta #{$venta->id} en cuenta {$metodo['nombre']}",
                        'usuario_id' => Auth::id(),
                        'fecha' => now()
                    ]);
                }
            }

            // Resetear la lista de productos y otros campos de venta
            $this->productos = [];
            $this->reset('producto_id', 'cantidad');
            $this->getTabla();
        });

        // Notificar éxito y cerrar el modal
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Venta actualizada correctamente.']);
        $this->dispatch('closeModal');
    }
}
