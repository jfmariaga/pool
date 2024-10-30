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

    public function mount()
    {
        // Inicialización de los productos y otras variables
        $this->productosall = Producto::all();
        $this->categorias  = Categoria::where('status', 1)->get();
        $this->descripcion = '';
        $this->montoTotal = 0;
        $this->cuentas = Cuenta::all();
    }

    public function render()
    {
        return view('livewire.ventas.ventas');
    }

    // public function getTabla()
    // {
    //     $this->skipRender();
    //     $ventas = Venta::with('detVentas.producto', 'usuario', 'cuenta')->get()->toArray();
    //     return $ventas;
    // }
    public function getTabla()
    {
        $this->skipRender();

        // Cargar las ventas y verificar tanto la relación `cuenta_id` como la tabla pivote `venta_cuenta`
        $ventas = Venta::with([
            'detVentas.producto',
            'usuario',
            'cuenta', // Cargar cuenta directa si existe
            'cuentas' // Cargar múltiples cuentas de la tabla pivote
        ])->get()->map(function ($venta) {
            // Consolidar los métodos de pago en un solo campo
            $metodosPago = collect();

            // Agregar cuenta directa si existe (casos antiguos)
            if ($venta->cuenta) {
                $metodosPago->push([
                    'nombre' => $venta->cuenta->nombre,
                    'monto' => $venta->monto_total,
                ]);
            }

            // Agregar las cuentas de la tabla pivote si existen
            if (!is_null($venta->cuentas)) { // Verifica que la relación esté cargada
                foreach ($venta->cuentas as $cuenta) {
                    $metodosPago->push([
                        'nombre' => $cuenta->nombre,
                        'monto' => $cuenta->pivot->monto, // Monto desde la relación pivote
                    ]);
                }
            }

            // Añadir el campo `metodosPago` al arreglo de datos de la venta
            $venta->metodosPago = $metodosPago;

            return $venta;
        })->toArray();

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

            // Obtener las DetCompras más antiguas (ordenadas por fecha de creación)
            $detCompras = DetCompra::where('producto_id', $producto->id)
                ->where('stock', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            DB::transaction(function () use ($detCompras, $producto) {
                $cantidadRestante = $this->cantidad;
                $montoNuevoProducto = 0;

                foreach ($detCompras as $detCompra) {
                    if ($cantidadRestante <= 0) break;

                    $stockDisponible = $detCompra->stock;
                    $cantidadAUtilizar = min($cantidadRestante, $stockDisponible);

                    $detCompra->stock -= $cantidadAUtilizar;
                    $detCompra->save();

                    // Usar el precio mayorista si está activo
                    $precio = $this->venta_mayorista && $producto->precio_mayorista > 0 ? $producto->precio_mayorista : $producto->precio_base;

                    DetVenta::create([
                        'venta_id' => $this->ventaId,
                        'producto_id' => $producto->id,
                        'cant' => $cantidadAUtilizar,
                        'precio_venta' => $precio,
                        'det_compra_id' => $detCompra->id,
                    ]);

                    $cantidadRestante -= $cantidadAUtilizar;

                    $this->productos[] = [
                        'producto_id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'precio' => $precio,
                        'cantidad' => $cantidadAUtilizar,
                    ];

                    $montoNuevoProducto += $precio * $cantidadAUtilizar;
                }

                $this->montoTotal += $montoNuevoProducto;
            });
        } else {
            // Si el producto tiene stock infinito
            DB::transaction(function () use ($producto) {
                $precio = $this->venta_mayorista && $producto->precio_mayorista > 0 ? $producto->precio_mayorista : $producto->precio_base;

                DetVenta::create([
                    'venta_id' => $this->ventaId,
                    'producto_id' => $producto->id,
                    'cant' => $this->cantidad,
                    'precio_venta' => $precio,
                    'det_compra_id' => 0,
                ]);

                $this->productos[] = [
                    'producto_id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'precio' => $precio,
                    'cantidad' => $this->cantidad,
                ];

                $this->montoTotal += $precio * $this->cantidad;
            });
        }

        $this->cantidad = 1;
    }

    // public function updateVenta()
    // {
    //     DB::transaction(function () {
    //         $venta = Venta::find($this->ventaId);

    //         if (!$venta) {
    //             $this->dispatch('showToast', [['type' => 'error', 'message' => 'Venta no encontrada.']]);
    //             return;
    //         }

    //         // Actualiza la información de la venta
    //         $venta->descripcion = $this->descripcion;
    //         $venta->monto_total = $this->montoTotal;
    //         $venta->cuenta_id = $this->cuenta_id;
    //         $venta->venta_mayorista = $this->venta_mayorista; // Guardar si es una venta mayorista
    //         $venta->save();

    //         // Actualiza los productos en la venta
    //         foreach ($this->productos as $producto) {
    //             $detVenta = DetVenta::where('venta_id', $this->ventaId)
    //                 ->where('producto_id', $producto['producto_id'])
    //                 ->first();

    //             if ($detVenta) {
    //                 $detVenta->cant = $producto['cantidad'];
    //                 $detVenta->precio_venta = $producto['precio'];
    //                 $detVenta->save();
    //             } else {
    //                 DetVenta::create([
    //                     'venta_id' => $this->ventaId,
    //                     'producto_id' => $producto['producto_id'],
    //                     'cant' => $producto['cantidad'],
    //                     'precio_venta' => $producto['precio'],
    //                 ]);
    //             }
    //         }

    //         // Elimina los productos que ya no están en la lista
    //         $productosIds = collect($this->productos)->pluck('producto_id');
    //         DetVenta::where('venta_id', $this->ventaId)->whereNotIn('producto_id', $productosIds)->delete();

    //         // Actualiza el movimiento de la cuenta
    //         $movimiento = Movimiento::where('venta_id', $this->ventaId)->first();
    //         if ($movimiento) {
    //             $movimiento->monto = $this->montoTotal;
    //             $movimiento->save();
    //         }

    //         // Actualiza el saldo de la cuenta asociada
    //         $cuenta = Cuenta::find($this->cuenta_id);
    //         if ($cuenta) {
    //             $cuenta->saldo += $this->montoTotal;
    //             $cuenta->save();
    //         }

    //         $this->productos = [];
    //         $this->reset('producto_id', 'cantidad');
    //         $this->getTabla();
    //     });

    //     $this->dispatch('closeModal');
    // }
    public function updateVenta()
    {
        DB::transaction(function () {
            $venta = Venta::find($this->ventaId);

            if (!$venta) {
                $this->dispatch('showToast', [['type' => 'error', 'message' => 'Venta no encontrada.']]);
                return;
            }

            // Actualiza la información de la venta
            $venta->descripcion = $this->descripcion;
            $venta->cuenta_id = $this->cuenta_id;
            $venta->venta_mayorista = $this->venta_mayorista; // Guardar si es una venta mayorista
            $venta->save();

            // Inicializa el monto total
            $nuevoMontoTotal = 0;

            // Actualiza los productos en la venta
            foreach ($this->productos as $producto) {
                $detVenta = DetVenta::where('venta_id', $this->ventaId)
                    ->where('producto_id', $producto['producto_id'])
                    ->first();

                if ($detVenta) {
                    // Calcular el cambio en cantidad
                    $cantidadAnterior = $detVenta->cant;
                    $nuevaCantidad = $producto['cantidad'];

                    // Actualizar el stock de DetCompra
                    if ($cantidadAnterior !== $nuevaCantidad) {
                        // Si hay un cambio, ajustar el stock
                        if ($nuevaCantidad > $cantidadAnterior) {
                            // Aumentar stock
                            $diferencia = $nuevaCantidad - $cantidadAnterior;
                            $detCompra = DetCompra::find($detVenta->det_compra_id);
                            if ($detCompra) {
                                $detCompra->stock -= $diferencia; // Restar del stock
                                $detCompra->save();
                            }
                        } else {
                            // Disminuir stock
                            $diferencia = $cantidadAnterior - $nuevaCantidad;
                            $detCompra = DetCompra::find($detVenta->det_compra_id);
                            if ($detCompra) {
                                $detCompra->stock += $diferencia; // Aumentar al stock
                                $detCompra->save();
                            }
                        }
                    }

                    $detVenta->cant = $nuevaCantidad;
                    $detVenta->precio_venta = $producto['precio'];
                    $detVenta->save();
                } else {
                    DetVenta::create([
                        'venta_id' => $this->ventaId,
                        'producto_id' => $producto['producto_id'],
                        'cant' => $producto['cantidad'],
                        'precio_venta' => $producto['precio'],
                    ]);
                }

                // Recalcular el monto total
                $nuevoMontoTotal += $producto['precio'] * $producto['cantidad'];
            }

            // Elimina los productos que ya no están en la lista
            $productosIds = collect($this->productos)->pluck('producto_id');
            DetVenta::where('venta_id', $this->ventaId)->whereNotIn('producto_id', $productosIds)->delete();

            // Actualiza el movimiento de la cuenta
            $movimiento = Movimiento::where('venta_id', $this->ventaId)->first();
            if ($movimiento) {
                $movimiento->monto = $nuevoMontoTotal;
                $movimiento->save();
            }

            // Actualiza el monto total de la venta
            $venta->monto_total = $nuevoMontoTotal;
            $venta->save();

            $this->productos = [];
            $this->reset('producto_id', 'cantidad');
            $this->getTabla();
        });

        $this->dispatch('closeModal');
    }


    public function editVenta($ventaId)
    {
        $venta = Venta::with('detVentas.producto', 'cuenta')->find($ventaId);

        if (!$venta) {
            $this->dispatch('showToast', [['type' => 'error', 'message' => 'Venta no encontrada.']]);
            return;
        }

        $this->ventaId = $venta->id;
        $this->descripcion = $venta->descripcion;
        $this->montoTotal = $venta->monto_total;
        $this->cuenta_id = $venta->cuenta_id;
        $this->venta_mayorista = $venta->venta_mayorista; // Asegúrate de capturar el tipo de venta

        $this->productos = $venta->detVentas->map(function ($detVenta) {
            return [
                'producto_id' => $detVenta->producto_id,
                'nombre' => $detVenta->producto->nombre,
                'precio' => $detVenta->precio_venta,
                'precio_mayorista' => $detVenta->producto->precio_mayorista,
                'cantidad' => $detVenta->cant,
            ];
        })->toArray();

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
        $detVenta = DetVenta::where('venta_id', $this->ventaId)
            ->where('producto_id', $producto['producto_id'])
            ->first();

        if ($detVenta) {
            if ($detVenta->det_compra_id) {
                $detCompra = DetCompra::find($detVenta->det_compra_id);
                if ($detCompra) {
                    $detCompra->increment('stock', $producto['cantidad']);
                }
            }

            $detVenta->delete();

            unset($this->productos[$index]);

            $this->montoTotal = array_sum(array_map(function ($producto) {
                return $producto['precio'] * $producto['cantidad'];
            }, $this->productos));

            Venta::find($this->ventaId)
                ->update(['monto_total' => $this->montoTotal]);

            $this->dispatch('showToast', [['type' => 'success', 'message' => 'Producto eliminado y stock actualizado.']]);
        } else {
            $this->dispatch('showToast', [['type' => 'error', 'message' => 'El producto no fue encontrado en la base de datos.']]);
        }
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
}
