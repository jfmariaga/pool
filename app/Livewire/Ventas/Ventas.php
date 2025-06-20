<?php

namespace App\Livewire\Ventas;

use App\Models\Categoria;
use App\Models\Credito;
use App\Models\Cuenta;
use App\Models\DetCompra;
use App\Models\DetVenta;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\User;
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
    public $usuarios;
    public $creditos;
    public $desde, $hasta;
    public $usuario_id;
    public string|null $metodo_pago = null;



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
        $this->usuarios = User::where('status', 1)->get();
        $this->creditos = Credito::where('venta_id', $this->ventaId)
            ->select('creditos.id as credito_id', 'monto', 'deudor_id', 'users.name as deudor_nombre')
            ->join('users', 'users.id', '=', 'creditos.deudor_id')
            ->get()
            ->toArray();

        if (!$this->desde && !$this->hasta) {
            $this->desde = date('Y-m-d', strtotime(date('Y-m-d') . ' - 1 month'));
            $this->hasta = date('Y-m-d');
        }
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

    // public function getTabla()
    // {
    //     $this->skipRender();

    //     $ventas = Venta::with([
    //         'detVentas.producto',
    //         'usuario',
    //         'cuenta',        // Relación para la cuenta directa
    //         'cuentas',       // Relación para pagos combinados
    //         'movimientos' => function ($query) {
    //             $query->where('tipo', 'egreso'); // Filtrar solo los egresos
    //         }
    //     ])->get()->map(function ($venta) {

    //         $metodosPago = collect();

    //         // Agregar cuenta directa si existe (casos antiguos)
    //         if ($venta->cuenta) {
    //             $metodosPago->push([
    //                 'nombre' => $venta->cuenta->nombre,
    //                 'monto' => $venta->monto_total,
    //             ]);
    //         }

    //         // Agregar cuentas combinadas de la relación pivote si existen
    //         if ($venta->cuentas && $venta->cuentas->count() > 0) {
    //             foreach ($venta->cuentas as $cuenta) {
    //                 $metodosPago->push([
    //                     'nombre' => $cuenta->nombre,
    //                     'monto' => $cuenta->pivot->monto,
    //                 ]);
    //             }
    //         }

    //         // Definir el campo `metodoPago` basado en los métodos de pago disponibles
    //         if ($metodosPago->count() > 1) {
    //             $venta->metodoPago = 'Pago Combinado';
    //         } elseif ($metodosPago->count() == 1) {
    //             $venta->metodoPago = $metodosPago->first()['nombre'];
    //         } else {
    //             $venta->metodoPago = 'N/A';
    //         }

    //         // Asigna los métodos de pago detallados para el comprobante
    //         $venta->metodosPago = $metodosPago->toArray();

    //         // Calcular el total de egresos (si existen)
    //         $venta->totalEgresos = $venta->movimientos->sum('monto');

    //         return $venta;
    //     })->toArray();

    //     return $ventas;
    // }

    public function getTabla()
    {
        $this->skipRender();

        $query = Venta::with([
            'detVentas.producto',
            'usuario',
            'cuenta',
            'cuentas',
            'movimientos' => function ($query) {
                $query->where('tipo', 'egreso');
            }
        ]);

        if ($this->desde && $this->hasta) {
            $desde = \Carbon\Carbon::parse($this->desde)->startOfDay();
            $hasta = \Carbon\Carbon::parse($this->hasta)->endOfDay();
            $query->whereBetween('fecha', [$desde, $hasta]);
        }

        if ($this->usuario_id) {
            $query->where('user_id', $this->usuario_id);
        }

        $ventas = $query->get()->filter(function ($venta) {

            $metodosPago = collect();

            if ($venta->cuenta) {
                $metodosPago->push([
                    'nombre' => $venta->cuenta->nombre,
                    'monto' => $venta->monto_total,
                ]);
            }

            if ($venta->cuentas && $venta->cuentas->count() > 0) {
                foreach ($venta->cuentas as $cuenta) {
                    $metodosPago->push([
                        'nombre' => $cuenta->nombre,
                        'monto' => $cuenta->pivot->monto,
                    ]);
                }
            }

            if ($metodosPago->count() > 1) {
                $venta->metodoPago = 'Pago Combinado';
            } elseif ($metodosPago->count() == 1) {
                $venta->metodoPago = $metodosPago->first()['nombre'];
            } else {
                // Aquí es crédito
                $venta->metodoPago = 'Crédito';
            }


            $venta->metodosPago = $metodosPago->toArray();
            $venta->totalEgresos = $venta->movimientos->sum('monto');

            if ($this->metodo_pago) {
                if ($this->metodo_pago === 'Pago Combinado') {
                    return $metodosPago->count() > 1;
                }

                if ($this->metodo_pago === 'Crédito') {
                    return $metodosPago->count() === 0;
                }
                // if ($this->metodo_pago === 'Crédito') {
                //     return $venta->cuenta_id == 0;
                //     dd($ventas);
                // }


                return $metodosPago->pluck('nombre')->contains($this->metodo_pago);
            }

            return true;
        })->values()->toArray();

        return $ventas;
    }

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
        if ($venta->cuenta && $venta->cuenta->id != 0) { // Ignorar cuenta_id = 0
            $metodosPago->push([
                'cuenta_id' => $venta->cuenta->id,
                'nombre' => $venta->cuenta->nombre,
                'monto' => $venta->monto_total,
            ]);
        }

        foreach ($venta->cuentas as $cuenta) {
            if ($cuenta->id != 0) { // Ignorar cuenta_id = 0
                $metodosPago->push([
                    'cuenta_id' => $cuenta->id,
                    'nombre' => $cuenta->nombre,
                    'monto' => $cuenta->pivot->monto,
                ]);
            }
        }


        // Agregar créditos como métodos de pago
        $creditos = Credito::where('venta_id', $ventaId)
            ->join('users', 'users.id', '=', 'creditos.deudor_id')
            ->select([
                'creditos.id as credito_id',
                'creditos.monto',
                'creditos.deudor_id',
                'users.name as deudor_nombre'
            ])
            ->get()
            ->map(function ($credito) {
                return [
                    'cuenta_id' => 0, // Crédito siempre tiene cuenta_id 0
                    'deudor_id' => $credito->deudor_id,
                    'deudor_nombre' => $credito->deudor_nombre,
                    'monto' => $credito->monto
                ];
            });
        $this->metodosPago = $metodosPago->merge($creditos)->toArray();
        // $this->metodosPago = $metodosPago->toArray();

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

        $creditosConAbonos = DB::table('abonos') // Asume que la tabla de abonos se llama 'abonos'
            ->join('creditos', 'abonos.credito_id', '=', 'creditos.id')
            ->where('creditos.venta_id', $ventaId)
            ->exists(); // Devuelve true si hay al menos un abono relacionado

        if ($creditosConAbonos) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'No se puede eliminar esta venta porque tiene créditos con abonos.'
            ]);
            return false;
        }

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

            Credito::where('venta_id', $ventaId)->delete();

            // 5. Eliminar la venta
            $venta->delete();

            $this->dispatch('showToast', [['type' => 'success', 'message' => 'Venta eliminada con éxito.']]);
        });

        return true;
    }

    public function validarYGuardar()
    {
        // Validar si existen créditos con abonos
        $creditosConAbonos = DB::table('abonos')
            ->join('creditos', 'abonos.credito_id', '=', 'creditos.id')
            ->where('creditos.venta_id', $this->ventaId)
            ->exists();

        if ($creditosConAbonos) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'No se pueden guardar cambios en esta venta porque tiene créditos con abonos.'
            ]);
            return;
        }

        // Calcular el monto total de los métodos de pago ingresados y redondearlo a 2 decimales
        $montoTotalMetodosPago = round(array_sum(array_column($this->metodosPago, 'monto')), 2);
        $montoTotalVenta = round($this->montoTotal, 2);

        if ($montoTotalMetodosPago !== $montoTotalVenta) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'El monto de los métodos de pago no coincide con el total de la venta. Ajuste los montos antes de continuar.'
            ]);
            return;
        }

        foreach ($this->metodosPago as $metodo) {
            if ((int)$metodo['cuenta_id'] === 0) {
                if (empty($metodo['deudor_id'])) {
                    $this->dispatch('showToast', [
                        'type' => 'error',
                        'message' => 'Debe seleccionar un deudor para el crédito.'
                    ]);
                    return;
                }

                if (!isset($metodo['monto']) || $metodo['monto'] <= 0) {
                    $this->dispatch('showToast', [
                        'type' => 'error',
                        'message' => 'El monto del crédito debe ser mayor a cero.'
                    ]);
                    return;
                }
            } else {
                if (empty($metodo['cuenta_id']) || !isset($metodo['monto']) || $metodo['monto'] <= 0) {
                    $this->dispatch('showToast', [
                        'type' => 'error',
                        'message' => 'Cada método de pago debe tener una cuenta válida y un monto mayor a cero.'
                    ]);
                    return;
                }
            }
        }

        // Iniciar la transacción
        DB::transaction(function () use ($montoTotalVenta) {
            $venta = Venta::find($this->ventaId) ?? new Venta();
            $venta->descripcion = $this->descripcion;
            $venta->venta_mayorista = $this->venta_mayorista;
            $venta->monto_total = $this->montoTotal;
            $venta->save();

            $productosExistentes = DetVenta::where('venta_id', $venta->id)->get();
            $productosNuevos = collect($this->productos)->keyBy('producto_id');

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

            // Eliminar todos los créditos y movimientos relacionados con esta venta
            Credito::where('venta_id', $venta->id)->delete();
            Movimiento::where('venta_id', $venta->id)->delete();

            $venta->cuentas()->detach();

            foreach ($this->metodosPago as $metodo) {
                $venta->cuentas()->attach($metodo['cuenta_id'], ['monto' => $metodo['monto']]);
                Movimiento::create([
                    'venta_id' => $venta->id,
                    'cuenta_id' => $metodo['cuenta_id'],
                    'tipo' => 'ingreso',
                    'monto' => $metodo['monto'],
                    'descripcion' => "Ingreso por modificación de venta #{$venta->id}",
                    'usuario_id' => Auth::id(),
                    'fecha' => now(),
                ]);
                if ((int)$metodo['cuenta_id'] === 0) {
                    Credito::create([
                        'deudor_id' => $metodo['deudor_id'],
                        'responsable_id' => Auth::id(),
                        'venta_id' => $venta->id,
                        'monto' => $metodo['monto'],
                        'des_monto' => $metodo['monto'],
                        'fecha' => now(),
                        'tipo' => 'Venta',
                        'estado' => 'pendiente',
                    ]);
                }
            }

            $this->productos = [];
            $this->reset('producto_id', 'cantidad');
            $this->getTabla();
        });

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Venta actualizada correctamente.']);
        $this->dispatch('closeModal');
    }
}
