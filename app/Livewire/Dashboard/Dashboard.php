<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $desde, $hasta;
    public $gastos_totales, $ventas_totales;
    public $productos_mas_vendidos = [];
    public $valor_inventario, $ranking_productos;

    public function mount()
    {
        // Fecha inicial por defecto (últimos 30 días)
        $this->desde = now()->subDays(30)->format('Y-m-d');
        $this->hasta = now()->format('Y-m-d');

        // Llamamos a la función que obtiene los datos del dashboard
        $this->actualizarDatos();
    }

    public function updatedDesde()
    {
        $this->actualizarDatos();
    }

    public function updatedHasta()
    {
        $this->actualizarDatos();
    }

    public function actualizarDatos()
    {
        // Obtener gastos totales de la tabla "movimientos" donde el tipo sea "egreso"
        $this->gastos_totales = DB::table('movimientos')
            ->where('tipo', 'egreso') // Filtrar por tipo "egreso"
            ->whereBetween('fecha', [$this->desde, $this->hasta])
            ->sum('monto');

        // Obtener ventas totales en el rango de fechas
        $this->ventas_totales = DB::table('ventas')
            ->whereBetween('fecha', [$this->desde, $this->hasta])
            ->sum('monto_total');

        // Obtener los productos más vendidos haciendo un join entre "det_ventas" y "ventas" para obtener la fecha desde "ventas"
        $this->productos_mas_vendidos = DB::table('det_ventas')
            ->select('producto_id', DB::raw('SUM(cant) as total_vendido'))
            ->join('ventas', 'ventas.id', '=', 'det_ventas.venta_id') // Hacer el join con la tabla ventas
            ->join('productos', 'productos.id', '=', 'det_ventas.producto_id')
            ->whereBetween('ventas.fecha', [$this->desde, $this->hasta]) // Usar la fecha de ventas
            ->groupBy('producto_id')
            ->orderBy('total_vendido', 'desc')
            ->limit(5)
            ->pluck('total_vendido', 'producto_id');

        // Obtener valor total del inventario
        $this->valor_inventario = DB::table('det_compras')
            ->sum(DB::raw('stock * precio_compra'));

        // Obtener ranking de productos por ganancia haciendo un join con la tabla "ventas"
        $this->ranking_productos = DB::table('det_ventas')
            ->select('producto_id', DB::raw('SUM(precio_venta) as total_ganancia'))
            ->join('ventas', 'ventas.id', '=', 'det_ventas.venta_id') // Hacer el join con la tabla ventas
            ->join('productos', 'productos.id', '=', 'det_ventas.producto_id')
            ->whereBetween('ventas.fecha', [$this->desde, $this->hasta]) // Usar la fecha de ventas
            ->groupBy('producto_id')
            ->orderBy('total_ganancia', 'desc')
            ->limit(5)
            ->pluck('total_ganancia', 'producto_id');
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard', [
            'gastos_totales' => $this->gastos_totales,
            'ventas_totales' => $this->ventas_totales,
            'productos_mas_vendidos' => $this->productos_mas_vendidos,
            'valor_inventario' => $this->valor_inventario,
            'ranking_productos' => $this->ranking_productos,
        ])->title('Dashboard');
    }
}
