<?php

namespace App\Livewire\Ventas;

use Livewire\Component;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Movimiento;
use App\Models\Cuenta;
use App\Models\DetCompra;
use App\Models\DetVenta;
use App\Models\DetVentasTemporales;
use App\Models\VentaTemporal;
use App\Models\ProductoVentaTemporal;
use App\Models\VentaProducto;
use App\Models\VentasTemporales;
use App\Models\Categoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FormVentas extends Component
{
    public $ventas = [];
    public $descripcion;
    public $montoTotal = 0;
    public $productosall = [];
    public $producto_id;
    public $cantidad = 1;
    public $currentVenta = null;
    public $cuentas;
    public $cuenta_id;
    public $categorias = [];

    protected $rules = [
        'descripcion' => 'required|string|max:255',
        'producto_id' => 'required|exists:productos,id',
        'cantidad' => 'required|integer|min:1',
    ];

    public function mount()
    {
        $this->cuentas = Cuenta::all();
        $this->productosall = Producto::all();
        $this->categorias  = Categoria::where('status', 1)->get();
        $this->cargarVentasTemporales();
    }


    public function getProductos()
    {
        $productos = Producto::with('categoria')->get()->toArray();
        return $productos;
    }


    public function cargarVentasTemporales()
    {
        $ventasTemporales = VentasTemporales::where('user_id', Auth::id())
            ->where('estado', 'abierta')
            ->with('productos')
            ->get();

        $this->ventas = [];

        foreach ($ventasTemporales as $ventaIndex => $venta) {
            $productos = $venta->productos->map(function ($productoVenta) {
                return [
                    'producto_id' => $productoVenta->producto_id,
                    'nombre' => $productoVenta->producto->nombre,
                    'precio' => $productoVenta->precio_unitario,
                    'cantidad' => $productoVenta->cantidad,
                ];
            })->toArray();

            $totalVenta = collect($productos)->sum(function ($producto) {
                return $producto['precio'] * $producto['cantidad'];
            });

            $this->ventas[] = [
                'descripcion' => $venta->descripcion,
                'monto' => $totalVenta,
                'productos' => $productos,
                'cuenta_id' => $venta->cuenta_id,
                'venta_temporal_id' => $venta->id,
                'venta_mayorista' => $venta->venta_mayorista, // Añadimos el campo venta_mayorista
            ];

            // Recalcula los precios si la venta es mayorista
            if ($venta->venta_mayorista) {
                $this->recalcularPreciosVenta($ventaIndex);
            }
        }

        $this->montoTotal = array_sum(array_column($this->ventas, 'monto'));
    }

    public function abrirNuevaVenta()
    {
        $this->validate([
            'descripcion' => 'required|string|max:255',
        ]);

        DB::transaction(function () {
            $ventaTemporal = VentasTemporales::create([
                'descripcion' => $this->descripcion,
                'monto_total' => 0,
                'user_id' => Auth::id(),
            ]);

            $this->ventas[] = [
                'descripcion' => $this->descripcion,
                'monto' => 0,
                'productos' => [],
                'cuenta_id' => null,
                'venta_temporal_id' => $ventaTemporal->id,
                'venta_mayorista' => false, // Por defecto no es mayorista
            ];

            $this->descripcion = '';
        });
    }

    public function seleccionarVenta($index)
    {
        $this->currentVenta = $index;
    }

    public function agregarProducto()
    {

        $this->validate([
            'producto_id' => 'required'
        ]);
        $producto = Producto::find($this->producto_id);

        // dd( $producto );

        if (!$producto->stock_infinito) {
            // Solo si el producto no tiene stock infinito, se revisa el stock disponible
            $stockDisponible = DetCompra::where('producto_id', $producto->id)
                ->sum('stock');

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

                    // Guardar los detalles en DetVentasTemporales
                    DetVentasTemporales::create([
                        'venta_temporal_id' => $this->ventas[$this->currentVenta]['venta_temporal_id'],
                        'producto_id' => $producto->id,
                        'cant' => $cantidadAUtilizar,
                        'precio_venta' => $producto->precio_base,
                        'det_compra_id' => $detCompra->id,
                    ]);

                    ProductoVentaTemporal::create([
                        'venta_temporal_id' => $this->ventas[$this->currentVenta]['venta_temporal_id'],
                        'producto_id' => $producto->id,
                        'precio_unitario' => $producto->precio_base,
                        'cantidad' => $cantidadAUtilizar,
                    ]);

                    $cantidadRestante -= $cantidadAUtilizar;

                    $this->ventas[$this->currentVenta]['productos'][] = [
                        'producto_id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'precio' => $producto->precio_base,
                        'cantidad' => $cantidadAUtilizar,
                    ];

                    $montoNuevoProducto += $producto->precio_base * $cantidadAUtilizar;
                }

                $this->ventas[$this->currentVenta]['monto'] += $montoNuevoProducto;
                VentasTemporales::find($this->ventas[$this->currentVenta]['venta_temporal_id'])
                    ->update(['monto_total' => $this->ventas[$this->currentVenta]['monto']]);
            });
        } else {
            // Lógica para productos con stock infinito
            DB::transaction(function () use ($producto) {
                ProductoVentaTemporal::create([
                    'venta_temporal_id' => $this->ventas[$this->currentVenta]['venta_temporal_id'],
                    'producto_id' => $producto->id,
                    'precio_unitario' => $producto->precio_base,
                    'cantidad' => $this->cantidad,
                ]);

                DetVentasTemporales::create([
                    'venta_temporal_id' => $this->ventas[$this->currentVenta]['venta_temporal_id'],
                    'producto_id' => $producto->id,
                    'cant' => $this->cantidad,
                    'precio_venta' => $producto->precio_base,
                    'det_compra_id' => 0,
                ]);


                $this->ventas[$this->currentVenta]['productos'][] = [
                    'producto_id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'precio' => $producto->precio_base,
                    'cantidad' => $this->cantidad,
                ];

                $this->ventas[$this->currentVenta]['monto'] += $producto->precio_base * $this->cantidad;

                VentasTemporales::find($this->ventas[$this->currentVenta]['venta_temporal_id'])
                    ->update(['monto_total' => $this->ventas[$this->currentVenta]['monto']]);
            });
        }

        // $this->producto_id = ''; // ya no lo vaciamos ya que el select queda cargado con este prod
        $this->cantidad = 1;
    }

    public function toggleVentaMayorista($index)
    {
        if (isset($this->ventas[$index])) {
            $this->ventas[$index]['venta_mayorista'] = !$this->ventas[$index]['venta_mayorista'];
            $this->recalcularPreciosVenta($index);

            VentasTemporales::find($this->ventas[$index]['venta_temporal_id'])
                ->update(['venta_mayorista' => $this->ventas[$index]['venta_mayorista'], 'monto_total' => $this->ventas[$index]['monto']]);
        }
    }

    public function recalcularPreciosVenta($index)
    {
        foreach ($this->ventas[$index]['productos'] as &$producto) {
            $productoModel = Producto::find($producto['producto_id']);
            $nuevoPrecio = $this->ventas[$index]['venta_mayorista'] && $productoModel->precio_mayorista > 0
                ? $productoModel->precio_mayorista
                : $productoModel->precio_base;

            $producto['precio'] = $nuevoPrecio;

            DetVentasTemporales::where('venta_temporal_id', $this->ventas[$index]['venta_temporal_id'])
                ->where('producto_id', $producto['producto_id'])
                ->update(['precio_venta' => $nuevoPrecio]); // Asegúrate de usar el campo correcto

            // Actualizar el precio en la tabla producto_venta_temporal
            ProductoVentaTemporal::where('venta_temporal_id', $this->ventas[$index]['venta_temporal_id'])
                ->where('producto_id', $producto['producto_id'])
                ->update(['precio_unitario' => $nuevoPrecio]);
        }

        $this->ventas[$index]['monto'] = array_sum(array_map(function ($producto) {
            return $producto['precio'] * $producto['cantidad'];
        }, $this->ventas[$index]['productos']));
    }

    public function eliminarProducto($ventaIndex, $productoIndex)
    {
        // Validar si la venta y el producto existen en la posición especificada
        if (!isset($this->ventas[$ventaIndex]) || !isset($this->ventas[$ventaIndex]['productos'][$productoIndex])) {
            $this->dispatch('showToast', [['type' => 'error', 'message' => 'Producto no encontrado.']]);
            return;
        }

        $venta = $this->ventas[$ventaIndex];
        $producto = $venta['productos'][$productoIndex];

        // Validamos que el producto tenga su 'producto_id' y 'cantidad'
        if (!isset($producto['producto_id']) || !isset($producto['cantidad']) || !isset($producto['precio'])) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Producto incompleto.']);
            return;
        }

        // Buscamos el registro exacto en 'DetVentasTemporales' usando el producto_id, la cantidad y el precio
        $detVentaTemporal = DetVentasTemporales::where('venta_temporal_id', $venta['venta_temporal_id'])
            ->where('producto_id', $producto['producto_id'])
            ->where('cant', $producto['cantidad'])
            ->where('precio_venta', $producto['precio'])
            ->first();

        if ($detVentaTemporal) {
            if ($detVentaTemporal->det_compra_id) {
                $detCompra = DetCompra::find($detVentaTemporal->det_compra_id);
                if ($detCompra) {
                    $detCompra->increment('stock', $producto['cantidad']);
                }
            }

            DetVentasTemporales::where('id', $detVentaTemporal->id)->delete();

            ProductoVentaTemporal::where('venta_temporal_id', $venta['venta_temporal_id'])
                ->where('producto_id', $producto['producto_id'])
                ->where('cantidad', $producto['cantidad'])
                ->where('precio_unitario', $producto['precio'])
                ->limit(1)  // Asegurar que eliminamos solo la instancia específica
                ->delete();

            // Eliminar el producto específico del array en Livewire
            unset($this->ventas[$ventaIndex]['productos'][$productoIndex]);

            $this->ventas[$ventaIndex]['monto'] = array_sum(array_map(function ($producto) {
                return $producto['precio'] * $producto['cantidad'];
            }, $this->ventas[$ventaIndex]['productos']));

            VentasTemporales::find($venta['venta_temporal_id'])
                ->update(['monto_total' => $this->ventas[$ventaIndex]['monto']]);

            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Producto eliminado y stock actualizado.']);
        } else {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'El producto no fue encontrado en la base de datos.']);
        }
    }

    public function cerrarVenta($index)
    {

        $detVentasTemporales = DetVentasTemporales::where('venta_temporal_id', $this->ventas[$index]['venta_temporal_id'])->get();

        if ($detVentasTemporales->isEmpty()) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => "La venta no tiene productos asociados."]);
            return;
        }
        if (!$this->ventas[$index]['cuenta_id']) {
            $this->addError('ventas.' . $index . '.cuenta_id', 'Debes seleccionar una cuenta para cerrar la venta.');
            return;
        }


        DB::transaction(function () use ($index) {
            $venta = Venta::create([
                'descripcion' => $this->ventas[$index]['descripcion'],
                'monto_total' => $this->ventas[$index]['monto'],
                'estado' => 'cerrada',
                'cuenta_id' => $this->ventas[$index]['cuenta_id'],
                'user_id' => Auth::id(),
                'fecha' => now(),
                'venta_mayorista' => $this->ventas[$index]['venta_mayorista']
            ]);

            // Obtener todos los registros de DetVentasTemporales correspondientes
            $detVentasTemporales = DetVentasTemporales::where('venta_temporal_id', $this->ventas[$index]['venta_temporal_id'])->get();

            // Crear un arreglo para los nuevos registros en DetVenta
            $nuevosRegistrosDetVenta = $detVentasTemporales->map(function ($detVentaTemporal) use ($venta) {
                return [
                    'venta_id' => $venta->id,
                    'producto_id' => $detVentaTemporal->producto_id,
                    'cant' => $detVentaTemporal->cant,
                    'precio_venta' => $detVentaTemporal->precio_venta,
                    'det_compra_id' => $detVentaTemporal->det_compra_id,
                ];
            });

            // Insertar los nuevos registros en DetVenta
            DetVenta::insert($nuevosRegistrosDetVenta->toArray());

            Movimiento::create([
                'venta_id' => $venta->id,
                'cuenta_id' => $this->ventas[$index]['cuenta_id'],
                'usuario_id' => Auth::id(),
                'tipo' => 'ingreso',
                'monto' => $this->ventas[$index]['monto'],
                'fecha' => now(),
            ]);

            // Eliminar los registros de las tablas temporales
            DetVentasTemporales::where('venta_temporal_id', $this->ventas[$index]['venta_temporal_id'])->delete();
            ProductoVentaTemporal::where('venta_temporal_id', $this->ventas[$index]['venta_temporal_id'])->delete();
            VentasTemporales::find($this->ventas[$index]['venta_temporal_id'])->delete();

            // Descartar la venta temporal de la variable de Livewire
            unset($this->ventas[$index]);

            // Esto es opcional para samir
            $this->montoTotal = array_sum(array_column($this->ventas, 'monto'));
        });
        return true;
    }


    public function cancelarVenta($index)
    {
        DB::transaction(function () use ($index) {
            // Recuperar los detalles de los productos de la venta temporal
            $productosVentaTemporales = DetVentasTemporales::where('venta_temporal_id', $this->ventas[$index]['venta_temporal_id'])->get();

            foreach ($productosVentaTemporales as $productoVentaTemporal) {
                $detCompra = DetCompra::find($productoVentaTemporal->det_compra_id);

                // Devolver el stock a la entrada original de DetCompra
                if ($detCompra) {
                    $detCompra->stock += $productoVentaTemporal->cant;
                    $detCompra->save();
                }
            }

            DetVentasTemporales::where('venta_temporal_id', $this->ventas[$index]['venta_temporal_id'])->delete();
            ProductoVentaTemporal::where('venta_temporal_id', $this->ventas[$index]['venta_temporal_id'])->delete();
            VentasTemporales::find($this->ventas[$index]['venta_temporal_id'])->delete();

            // Quitar la venta de la lista en Livewire
            unset($this->ventas[$index]);

            $this->montoTotal = array_sum(array_column($this->ventas, 'monto'));
        });

        return true;
    }


    public function render()
    {
        return view('livewire.ventas.form-ventas', [
            'ventas' => $this->ventas,
            'cuentas' => $this->cuentas,
            'productosall' => $this->productosall,
        ]);
    }
}
