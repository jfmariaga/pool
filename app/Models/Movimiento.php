<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table   = 'movimientos';
    protected $guarded = [];
    public $timestamps = false;

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class);
    }

    public function usuario(){
        return $this->belongsTo(User::class);
    }

        /**
     * Scope para filtrar por un rango de fechas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $periodo ('diario', 'semanal', 'mensual', 'anual')
     * @param string|null $fechaInicio Fecha de inicio en formato 'YYYY-MM-DD'
     * @param string|null $fechaFin Fecha de fin en formato 'YYYY-MM-DD'
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorFecha($query, $periodo = null, $fechaInicio = null, $fechaFin = null)
    {
        // Si se pasan $fechaInicio y $fechaFin, ignorar el periodo predefinido
        if ($fechaInicio && $fechaFin) {
            return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }

        // Aplicar filtros según el periodo predefinido
        switch ($periodo) {
            case 'diario':
                return $query->whereDate('fecha', today());
            case 'semanal':
                return $query->whereBetween('fecha', [now()->startOfWeek(), now()->endOfWeek()]);
            case 'mensual':
                return $query->whereMonth('fecha', now()->month)
                             ->whereYear('fecha', now()->year);
            case 'anual':
                return $query->whereYear('fecha', now()->year);
            default:
                return $query;  // No aplicar ningún filtro si no se proporciona un periodo válido
        }
    }

    /**
     * Scope para filtrar las ventas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $periodo (opcional: 'diario', 'semanal', 'mensual', 'anual')
     * @param string|null $fechaInicio Fecha de inicio en formato 'YYYY-MM-DD'
     * @param string|null $fechaFin Fecha de fin en formato 'YYYY-MM-DD'
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVentas($query, $periodo = null, $fechaInicio = null, $fechaFin = null)
    {
        $query = $query->where('tipo', 'ingreso')->where('categoria', 'venta');
        if ($periodo || ($fechaInicio && $fechaFin)) {
            return $query->porFecha($periodo, $fechaInicio, $fechaFin);
        }
        return $query;
    }

    /**
     * Scope para filtrar las compras.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $periodo (opcional: 'diario', 'semanal', 'mensual', 'anual')
     * @param string|null $fechaInicio Fecha de inicio en formato 'YYYY-MM-DD'
     * @param string|null $fechaFin Fecha de fin en formato 'YYYY-MM-DD'
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompras($query, $periodo = null, $fechaInicio = null, $fechaFin = null)
    {
        $query = $query->where('tipo', 'egreso')->where('categoria', 'compra');
        if ($periodo || ($fechaInicio && $fechaFin)) {
            return $query->porFecha($periodo, $fechaInicio, $fechaFin);
        }
        return $query;
    }

    /**
     * Scope para filtrar los gastos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $periodo (opcional: 'diario', 'semanal', 'mensual', 'anual')
     * @param string|null $fechaInicio Fecha de inicio en formato 'YYYY-MM-DD'
     * @param string|null $fechaFin Fecha de fin en formato 'YYYY-MM-DD'
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGastos($query, $periodo = null, $fechaInicio = null, $fechaFin = null)
    {
        $query = $query->where('tipo', 'egreso')->where('categoria', 'gasto');
        if ($periodo || ($fechaInicio && $fechaFin)) {
            return $query->porFecha($periodo, $fechaInicio, $fechaFin);
        }
        return $query;
    }

    /**
     * Scope para filtrar los ingresos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $periodo (opcional: 'diario', 'semanal', 'mensual', 'anual')
     * @param string|null $fechaInicio Fecha de inicio en formato 'YYYY-MM-DD'
     * @param string|null $fechaFin Fecha de fin en formato 'YYYY-MM-DD'
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIngresos($query, $periodo = null, $fechaInicio = null, $fechaFin = null)
    {
        $query = $query->where('tipo', 'ingreso');
        if ($periodo || ($fechaInicio && $fechaFin)) {
            return $query->porFecha($periodo, $fechaInicio, $fechaFin);
        }
        return $query;
    }

    /**
     * Scope para filtrar los egresos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $periodo (opcional: 'diario', 'semanal', 'mensual', 'anual')
     * @param string|null $fechaInicio Fecha de inicio en formato 'YYYY-MM-DD'
     * @param string|null $fechaFin Fecha de fin en formato 'YYYY-MM-DD'
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEgresos($query, $periodo = null, $fechaInicio = null, $fechaFin = null)
    {
        $query = $query->where('tipo', 'egreso');
        if ($periodo || ($fechaInicio && $fechaFin)) {
            return $query->porFecha($periodo, $fechaInicio, $fechaFin);
        }
        return $query;
    }

}
