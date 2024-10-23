<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $startDate, $endDate;
    public $gastos_totales, $ventas_totales, $compras_totales, $ganancia_real, $ventas_diarias;
    public $productos_mas_vendidos = [];
    public $valor_inventario, $ranking_productos;

    public function mount()
    {
        // Definir fechas por defecto
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->actualizarDatos();
    }

    public function actualizarDatos()
    {
        // Validar que las fechas estén presentes
        if (!$this->startDate || !$this->endDate) {
            return;
        }

        // Asegurarnos de que el rango cubra todo el día
        $start = $this->startDate . ' 00:00:00'; // Inicio del día
        $end = $this->endDate . ' 23:59:59'; // Fin del día

        // Consultar los datos según el rango de fechas
        $this->gastos_totales = DB::table('movimientos')
            ->where('tipo', 'egreso')
            ->whereNull('compra_id')
            ->whereBetween('fecha', [$start, $end])
            ->sum('monto');

        $this->compras_totales = DB::table('movimientos')
            ->where('tipo', 'egreso')
            ->whereNotNull('compra_id')
            ->whereBetween('fecha', [$start, $end])
            ->sum('monto');

        $this->ventas_totales = DB::table('ventas')
            ->whereBetween('fecha', [$start, $end])
            ->sum('monto_total');

        // Filtrar productos más vendidos por fecha
        $this->productos_mas_vendidos = DB::table('det_ventas')
            ->select('productos.nombre', DB::raw('SUM(det_ventas.cant) as total_vendido'))
            ->join('ventas', 'ventas.id', '=', 'det_ventas.venta_id')
            ->join('productos', 'productos.id', '=', 'det_ventas.producto_id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->groupBy('productos.nombre')
            ->orderBy('total_vendido', 'desc')
            ->limit(5)
            ->pluck('total_vendido', 'productos.nombre');

            $this->ranking_productos = DB::table('det_ventas as dv')
            ->select('p.nombre', DB::raw('SUM(
                CASE
                    WHEN dc.precio_compra IS NULL OR dc.precio_compra = 0 THEN
                        dv.cant * dv.precio_venta
                    ELSE
                        dv.cant * (dv.precio_venta - dc.precio_compra)
                END) AS total_ganancia'))
            ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
            ->join('productos as p', 'p.id', '=', 'dv.producto_id')
            ->leftJoin('det_compras as dc', 'dc.id', '=', 'dv.det_compra_id') // Asegúrate de usar el campo correcto
            ->whereBetween('v.fecha', [$start, $end])
            ->groupBy('p.id', 'p.nombre') // Agrupamos por ID y nombre del producto
            ->orderBy('total_ganancia', 'desc')
            ->limit(5)
            ->pluck('total_ganancia', 'p.nombre'); // Mostrar el total ganancia por nombre del producto



        $this->ganancia_real = DB::table('det_ventas')
            ->select(DB::raw('SUM(
                CASE
                    WHEN dc.precio_compra IS NULL OR dc.precio_compra = 0 THEN
                        det_ventas.cant * det_ventas.precio_venta
                    ELSE
                        det_ventas.cant * (det_ventas.precio_venta - dc.precio_compra)
                END) as total_ganancia'))
            ->join('ventas', 'ventas.id', '=', 'det_ventas.venta_id')
            ->join('productos', 'productos.id', '=', 'det_ventas.producto_id')
            ->leftJoin('det_compras as dc', 'dc.id', '=', 'det_ventas.det_compra_id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->groupBy('productos.id') // Agrupando por ID del producto para evitar duplicados
            ->get()
            ->sum('total_ganancia'); // Sumar todas las ganancias de los productos

        // Valor del inventario no depende del rango de fechas, es el stock actual
        $this->valor_inventario = DB::table('det_compras')
            ->sum(DB::raw('stock * precio_compra'));

        $this->ventas_diarias = DB::table('ventas')
            ->select(DB::raw('DATE(fecha) as fecha, SUM(monto_total) as total_ventas'))
            ->whereBetween('fecha', [$start, $end])
            ->groupBy(DB::raw('DATE(fecha)'))
            ->orderBy('fecha') // Asegúrate de ordenar por fecha
            ->pluck('total_ventas', 'fecha'); // Obtener total de ventas por fecha


        $this->dispatch('chartDataUpdated', [
            'productosMasVendidos' => $this->productos_mas_vendidos,
            'rankingGanancias' => $this->ranking_productos,
            'ventasPorDia' => $this->ventas_diarias,
        ]);
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard', [
            'gastos_totales'         => $this->gastos_totales,
            'compras_totales'        => $this->compras_totales,
            'ventas_totales'         => $this->ventas_totales,
            'productos_mas_vendidos' => $this->productos_mas_vendidos,
            'valor_inventario'       => $this->valor_inventario,
            'ranking_productos'      => $this->ranking_productos,
            'ganancia_real'          => $this->ganancia_real,
            'ventas_diarias'         => $this->ventas_diarias,
        ])->title('Dashboard');
    }
}
