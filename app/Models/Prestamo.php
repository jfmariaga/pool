<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    protected $table = 'prestamos';
    protected $guarded = [];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_corte' => 'date',
        'fecha_cierre' => 'date',
        'monto' => 'decimal:2',
        'saldo_capital' => 'decimal:2',
        'saldo_interes_favor' => 'decimal:2',
        'tasa_interes' => 'decimal:4',
    ];

    protected $appends = [
        'meses_corridos',
        'meses_causados',
        'interes_mensual',
        'interes_causado',
        'fecha_vencimiento',
        'esta_vencido',
        'estado_mostrar',
        'promedio',
        'total_adeudado',
        'total_abonado',
        'meses_pagados',
        'interes_inversionista_mensual',
    ];

    /* ------------------------------------------------------------------ */
    /* Relaciones                                                         */
    /* ------------------------------------------------------------------ */

    public function cliente()
    {
        return $this->belongsTo(PrestamoCliente::class, 'cliente_id');
    }

    public function inversionista()
    {
        return $this->belongsTo(PrestamoInversionista::class, 'inversionista_id');
    }

    public function movimientos()
    {
        return $this->hasMany(PrestamoMovimiento::class)->orderBy('fecha')->orderBy('id');
    }

    /* ------------------------------------------------------------------ */
    /* Scopes                                                            */
    /* ------------------------------------------------------------------ */

    public function scopeModalidad($query, $modalidad)
    {
        return $query->where('modalidad', $modalidad);
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /* ------------------------------------------------------------------ */
    /* Cálculo de interés / plazo                                        */
    /* ------------------------------------------------------------------ */

    /** Fecha de referencia hasta la que se calcula el interés. */
    private function fechaReferencia(): Carbon
    {
        if ($this->fecha_cierre) {
            return Carbon::parse($this->fecha_cierre);
        }

        return Carbon::today();
    }

    /** Meses calendario completos transcurridos desde el último corte. */
    public function getMesesCorridosAttribute(): int
    {
        if (! $this->fecha_corte) {
            return 0;
        }

        $corte = Carbon::parse($this->fecha_corte)->startOfDay();
        $ref = $this->fechaReferencia()->startOfDay();

        if ($ref->lessThanOrEqualTo($corte)) {
            return 0;
        }

        return $corte->diffInMonths($ref);
    }

    /**
     * Meses de interés YA CAUSADOS.
     * Al entregar el dinero, el primer mes se causa de inmediato (por eso el +1),
     * aunque el préstamo todavía no esté vencido.
     * Si el corte está a futuro (interés prepagado) devuelve 0.
     * En retroventa se limita al plazo (a los N meses se deben N meses, no más).
     */
    public function getMesesCausadosAttribute(): int
    {
        if (! $this->fecha_corte) {
            return 0;
        }

        $corte = Carbon::parse($this->fecha_corte)->startOfDay();
        $ref = $this->fechaReferencia()->startOfDay();

        if ($ref->lessThan($corte)) {
            return 0;
        }

        $causados = $corte->diffInMonths($ref) + 1;

        if ($this->modalidad === 'retroventa') {
            $causados = min($causados, (int) ($this->plazo_meses ?: 4));
        }

        return $causados;
    }

    /** Interés de un mes sobre el capital vigente. */
    public function getInteresMensualAttribute(): float
    {
        return round(((float) $this->saldo_capital) * ((float) $this->tasa_interes), 2);
    }

    /** Interés acumulado y aún no pagado a la fecha de referencia. */
    public function getInteresCausadoAttribute(): float
    {
        $bruto = $this->interes_mensual * $this->meses_causados;
        $neto = $bruto - (float) $this->saldo_interes_favor;

        return round(max(0, $neto), 2);
    }

    /** Fecha de vencimiento del plazo (solo retroventa). */
    public function getFechaVencimientoAttribute(): ?string
    {
        if ($this->modalidad !== 'retroventa' || ! $this->fecha_corte) {
            return null;
        }

        return Carbon::parse($this->fecha_corte)
            ->addMonths((int) ($this->plazo_meses ?: 4))
            ->toDateString();
    }

    public function getEstaVencidoAttribute(): bool
    {
        if ($this->modalidad !== 'retroventa' || $this->estado !== 'activo' || ! $this->fecha_vencimiento) {
            return false;
        }

        return Carbon::today()->greaterThan(Carbon::parse($this->fecha_vencimiento));
    }

    /** Etiqueta de estado para la tabla (deriva "vencido"). */
    public function getEstadoMostrarAttribute(): string
    {
        if (in_array($this->estado, ['pagado', 'adjudicado'], true)) {
            return $this->estado;
        }

        return $this->esta_vencido ? 'vencido' : 'activo';
    }

    /** Precio por gramo (retroventa). */
    public function getPromedioAttribute(): float
    {
        $peso = (float) $this->peso;

        return $peso > 0 ? round(((float) $this->monto) / $peso, 2) : 0.0;
    }

    public function getTotalAdeudadoAttribute(): float
    {
        return round(((float) $this->saldo_capital) + $this->interes_causado, 2);
    }

    public function getTotalAbonadoAttribute(): float
    {
        return round((float) $this->movimientos->sum(fn ($m) => (float) $m->monto_interes + (float) $m->monto_capital), 2);
    }

    /** Nº de meses de interés que el cliente ha pagado (equivale a la columna MESES del Excel). */
    public function getMesesPagadosAttribute(): int
    {
        return (int) $this->movimientos->sum('meses_cubiertos');
    }

    /** Interés mensual que le corresponde al inversionista sobre el capital vigente. */
    public function getInteresInversionistaMensualAttribute(): float
    {
        $tasa = (float) ($this->inversionista?->tasa ?? 0);

        return round(((float) $this->saldo_capital) * $tasa, 2);
    }
}
