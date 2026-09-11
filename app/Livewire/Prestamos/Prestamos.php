<?php

namespace App\Livewire\Prestamos;

use App\Models\Prestamo;
use App\Models\PrestamoCliente;
use App\Models\PrestamoInversionista;
use App\Models\PrestamoMovimiento;
use App\Traits\General;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Prestamos extends Component
{
    use General;

    public $tab = 'dash_retro';

    public $inversionistas = [];

    // ---- filtros ----
    public $desde, $hasta, $estado_filter, $inversionista_filter, $cartera_filter, $buscar;

    // ---- formulario prestamo ----
    public $prestamo_id;
    public $cliente_nombre, $cliente_cedula, $cliente_telefono;
    public $inversionista_id, $cartera, $num_contrato, $peso, $descripcion_prenda;
    public $fecha_inicio, $monto, $tasa_interes, $observacion;

    // ---- formulario pago ----
    public $pago_prestamo_id, $pago_monto, $pago_fecha, $pago_observacion;
    public $mov_saldo_capital, $mov_interes_causado, $mov_fecha_vencimiento, $mov_cliente, $mov_estado;
    public $mov_info = [];
    public $movimientos = [];

    // ---- formulario inversionista ----
    public $inv_id, $inv_nombre, $inv_tasa, $inv_telefono, $inv_activo = 1;

    public $carteras = ['General', 'Laura', 'Mamá'];

    public function mount()
    {
        if (! $this->desde && ! $this->hasta) {
            $this->desde = date('Y-m-d', strtotime('-1 year'));
            $this->hasta = date('Y-m-d');
        }
        $this->cargarInversionistas();
    }

    public function cargarInversionistas()
    {
        $this->inversionistas = PrestamoInversionista::orderBy('nombre')->get();
    }

    /* ================================================================= */
    /* Listado                                                           */
    /* ================================================================= */

    public function getPrestamos()
    {
        $this->skipRender();

        $query = Prestamo::query()
            ->with(['cliente', 'inversionista', 'movimientos'])
            ->modalidad($this->tab);

        if ($this->desde && $this->hasta) {
            $query->whereBetween('fecha_inicio', [$this->desde, $this->hasta]);
        }

        if ($this->tab === 'retroventa' && $this->inversionista_filter && $this->inversionista_filter !== '0') {
            $query->where('inversionista_id', $this->inversionista_filter);
        }

        if ($this->tab === 'personal' && $this->cartera_filter && $this->cartera_filter !== '0') {
            $query->where('cartera', $this->cartera_filter);
        }

        if ($this->buscar) {
            $b = trim($this->buscar);
            $query->whereHas('cliente', function ($q) use ($b) {
                $q->where('nombre', 'like', "%{$b}%")->orWhere('cedula', 'like', "%{$b}%");
            });
        }

        $prestamos = $query->orderByDesc('fecha_inicio')->orderByDesc('id')->get();

        if ($this->estado_filter && $this->estado_filter !== '0') {
            $prestamos = $prestamos->filter(fn ($p) => $p->estado_mostrar === $this->estado_filter)->values();
        }

        return $prestamos;
    }

    /**
     * Dashboard de la línea RETROVENTA (empeño con prenda).
     */
    public function metricasRetroventa()
    {
        $this->skipRender();

        $retro = Prestamo::with('inversionista', 'movimientos', 'cliente')->modalidad('retroventa')->get();
        $act = $retro->where('estado', 'activo');

        $vencidos = $act->filter(fn ($p) => $p->esta_vencido);
        $porVencer = $act->filter(function ($p) {
            if ($p->esta_vencido || ! $p->fecha_vencimiento) {
                return false;
            }
            $dias = Carbon::today()->diffInDays(Carbon::parse($p->fecha_vencimiento), false);

            return $dias >= 0 && $dias <= 15;
        });

        $capital = (float) $act->sum('saldo_capital');
        $interesMes = (float) $act->sum('interes_mensual');
        $interesInvMes = (float) $act->sum('interes_inversionista_mensual');

        $movs = $retro->flatMap->movimientos;
        $recuperado = (float) $movs->whereIn('tipo', ['abono_capital', 'mixto', 'cancelacion'])->sum('monto_capital');
        $interesCobrado = (float) $movs->sum('monto_interes');

        $proximos = $act
            ->filter(fn ($p) => $p->fecha_vencimiento)
            ->sortBy('fecha_vencimiento')
            ->take(10)
            ->map(fn ($p) => [
                'cliente' => $p->cliente->nombre ?? '-',
                'inversionista' => $p->inversionista->nombre ?? '-',
                'vence' => $p->fecha_vencimiento,
                'saldo_capital' => round((float) $p->saldo_capital, 2),
                'interes_causado' => $p->interes_causado,
                'vencido' => $p->esta_vencido,
            ])
            ->values();

        return [
            'capital' => round($capital, 2),
            'interes_mes' => round($interesMes, 2),
            'pago_inversionistas_mes' => round($interesInvMes, 2),
            'ganancia_casa_mes' => round($interesMes - $interesInvMes, 2),
            'interes_causado' => round((float) $act->sum('interes_causado'), 2),
            'recuperado_capital' => round($recuperado, 2),
            'interes_cobrado' => round($interesCobrado, 2),
            'contratos' => $retro->count(),
            'activos' => $act->count(),
            'vencidos' => $vencidos->count(),
            'por_vencer' => $porVencer->count(),
            'adjudicadas' => $retro->where('estado', 'adjudicado')->count(),
            'clientes' => $retro->pluck('cliente_id')->unique()->count(),
            'proximos_vencimientos' => $proximos,
        ];
    }

    /**
     * Dashboard de la línea PRÉSTAMOS PERSONALES.
     */
    public function metricasPersonal()
    {
        $this->skipRender();

        $pers = Prestamo::with('movimientos')->modalidad('personal')->get();
        $act = $pers->where('estado', 'activo');

        $capital = (float) $act->sum('saldo_capital');
        $interesMes = (float) $act->sum('interes_mensual');

        $movs = $pers->flatMap->movimientos;
        $recuperado = (float) $movs->whereIn('tipo', ['abono_capital', 'mixto', 'cancelacion'])->sum('monto_capital');
        $interesCobrado = (float) $movs->sum('monto_interes');

        return [
            'capital' => round($capital, 2),
            'interes_mes' => round($interesMes, 2),
            'interes_causado' => round((float) $act->sum('interes_causado'), 2),
            'recuperado_capital' => round($recuperado, 2),
            'interes_cobrado' => round($interesCobrado, 2),
            'prestamos' => $pers->count(),
            'activos' => $act->count(),
            'pagados' => $pers->where('estado', 'pagado')->count(),
            'clientes' => $pers->pluck('cliente_id')->unique()->count(),
            'por_cartera' => $this->resumenCarteras(),
        ];
    }

    private function resumenCarteras()
    {
        $activos = Prestamo::modalidad('personal')->where('estado', 'activo')->get();

        $filas = [];
        foreach ($activos->groupBy('cartera') as $cartera => $grupo) {
            $filas[] = [
                'nombre' => $cartera ?: 'Sin cartera',
                'contratos' => $grupo->count(),
                'capital' => round((float) $grupo->sum('saldo_capital'), 2),
                'interes_mes' => round((float) $grupo->sum('interes_mensual'), 2),
                'interes_causado' => round((float) $grupo->sum('interes_causado'), 2),
            ];
        }

        return $filas;
    }

    /* ================================================================= */
    /* Crear / editar préstamo                                           */
    /* ================================================================= */

    private function tasaPorDefecto(string $modalidad): float
    {
        if ($modalidad === 'retroventa') {
            return 0.07;
        }

        return $this->cartera === 'Laura' ? 0.03 : 0.05;
    }

    public function savePrestamo()
    {
        $modalidad = in_array($this->tab, ['retroventa', 'personal'], true) ? $this->tab : 'retroventa';

        $reglas = [
            'cliente_nombre' => 'required|string|max:255',
            'monto' => 'required',
            'fecha_inicio' => 'required|date',
        ];
        if ($modalidad === 'retroventa') {
            $reglas['inversionista_id'] = 'required|exists:prestamo_inversionistas,id';
            $reglas['cliente_cedula'] = 'required|string|max:30';
        } else {
            $reglas['cartera'] = 'required|string';
        }
        $this->validate($reglas, [
            'cliente_cedula.required' => 'La cédula del cliente es obligatoria.',
        ]);

        $montoLimpio = (float) $this->__limpiarNumDecimales($this->monto);
        if ($montoLimpio <= 0) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'El monto debe ser mayor a cero.']);
            return false;
        }

        $tasa = $this->tasa_interes !== null && $this->tasa_interes !== ''
            ? (float) $this->tasa_interes
            : $this->tasaPorDefecto($modalidad);
        // permitir ingresar la tasa como porcentaje (7) o como fracción (0.07)
        if ($tasa > 1) {
            $tasa = $tasa / 100;
        }

        $cliente = PrestamoCliente::firstOrCreate(
            $this->cliente_cedula
                ? ['cedula' => trim($this->cliente_cedula)]
                : ['nombre' => trim($this->cliente_nombre)],
            ['nombre' => trim($this->cliente_nombre), 'telefono' => $this->cliente_telefono]
        );

        $base = [
            'cliente_id' => $cliente->id,
            'inversionista_id' => $modalidad === 'retroventa' ? $this->inversionista_id : null,
            'cartera' => $modalidad === 'personal' ? $this->cartera : null,
            'num_contrato' => $this->num_contrato,
            'peso' => $this->peso ?: null,
            'descripcion_prenda' => $this->descripcion_prenda,
            'fecha_inicio' => $this->fecha_inicio,
            'observacion' => $this->observacion,
        ];

        if ($this->prestamo_id) {
            $prestamo = Prestamo::with('movimientos')->find($this->prestamo_id);
            if (! $prestamo) {
                return false;
            }

            $tienePagos = $prestamo->movimientos->where('tipo', '!=', 'desembolso')->isNotEmpty();
            if (! $tienePagos) {
                $base['monto'] = $montoLimpio;
                $base['saldo_capital'] = $montoLimpio;
                $base['tasa_interes'] = $tasa;
                $base['fecha_corte'] = $this->fecha_inicio;
            }
            $prestamo->update($base);

            if (! $tienePagos) {
                PrestamoMovimiento::where('prestamo_id', $prestamo->id)
                    ->where('tipo', 'desembolso')
                    ->update([
                        'fecha' => $this->fecha_inicio,
                        'monto_capital' => $montoLimpio,
                        'capital_despues' => $montoLimpio,
                    ]);
            }
        } else {
            $prestamo = Prestamo::create(array_merge($base, [
                'modalidad' => $modalidad,
                'fecha_corte' => $this->fecha_inicio,
                'monto' => $montoLimpio,
                'saldo_capital' => $montoLimpio,
                'tasa_interes' => $tasa,
                'plazo_meses' => 4,
                'estado' => 'activo',
                'usuario_id' => Auth::id(),
            ]));

            PrestamoMovimiento::create([
                'prestamo_id' => $prestamo->id,
                'fecha' => $this->fecha_inicio,
                'tipo' => 'desembolso',
                'monto_capital' => $montoLimpio,
                'capital_antes' => 0,
                'capital_despues' => $montoLimpio,
                'interes_causado' => 0,
                'usuario_id' => Auth::id(),
                'created_at' => now(),
            ]);
        }

        $prestamo->load(['cliente', 'inversionista', 'movimientos']);
        $this->resetForm();

        return $prestamo->toArray();
    }

    public function deletePrestamo($id)
    {
        $prestamo = Prestamo::with('movimientos')->find($id);
        if (! $prestamo) {
            return false;
        }

        if ($prestamo->movimientos->where('tipo', '!=', 'desembolso')->isNotEmpty()) {
            return false;
        }

        $prestamo->movimientos()->delete();
        $prestamo->delete();

        return true;
    }

    public function resetForm()
    {
        $this->reset([
            'prestamo_id', 'cliente_nombre', 'cliente_cedula', 'cliente_telefono',
            'inversionista_id', 'cartera', 'num_contrato', 'peso', 'descripcion_prenda',
            'fecha_inicio', 'monto', 'tasa_interes', 'observacion',
        ]);
        $this->resetValidation();
    }

    /* ================================================================= */
    /* Pagos / trazabilidad                                             */
    /* ================================================================= */

    public function getPago($id)
    {
        $prestamo = Prestamo::with(['cliente', 'inversionista', 'movimientos'])->find($id);
        if (! $prestamo) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Préstamo no encontrado.']);
            return;
        }

        $this->pago_prestamo_id = $prestamo->id;
        $this->pago_monto = null;
        $this->pago_fecha = date('Y-m-d');
        $this->pago_observacion = null;
        $this->mov_saldo_capital = (float) $prestamo->saldo_capital;
        $this->mov_interes_causado = $prestamo->interes_causado;
        $this->mov_fecha_vencimiento = $prestamo->fecha_vencimiento;
        $this->mov_cliente = $prestamo->cliente->nombre ?? '';
        $this->mov_estado = $prestamo->estado_mostrar;
        $this->movimientos = $prestamo->movimientos()->get();

        $this->mov_info = [
            'modalidad' => $prestamo->modalidad,
            'cedula' => $prestamo->cliente->cedula ?? '',
            'inversionista' => $prestamo->inversionista->nombre ?? '',
            'cartera' => $prestamo->cartera ?? '',
            'num_contrato' => $prestamo->num_contrato ?? '',
            'peso' => $prestamo->peso,
            'prenda' => $prestamo->descripcion_prenda ?? '',
            'promedio' => $prestamo->promedio,
            'tasa' => (float) $prestamo->tasa_interes,
            'monto' => (float) $prestamo->monto,
            'fecha_inicio' => optional($prestamo->fecha_inicio)->toDateString(),
            'meses_pagados' => $prestamo->meses_pagados,
            'meses_causados' => $prestamo->meses_causados,
            'total_adeudado' => $prestamo->total_adeudado,
            'observacion' => $prestamo->observacion ?? '',
        ];

        $this->dispatch('openPagoModal');
    }

    public function registrarPago()
    {
        $this->validate([
            'pago_monto' => 'required',
            'pago_fecha' => 'required|date',
        ]);

        $prestamo = Prestamo::with('movimientos')->find($this->pago_prestamo_id);
        if (! $prestamo || $prestamo->estado !== 'activo') {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'El préstamo no está activo.']);
            return;
        }

        $montoPago = (float) $this->__limpiarNumDecimales($this->pago_monto);
        if ($montoPago <= 0) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'El monto del pago debe ser mayor a cero.']);
            return;
        }

        $fechaPago = Carbon::parse($this->pago_fecha)->startOfDay();
        $corte = Carbon::parse($prestamo->fecha_corte)->startOfDay();

        // El primer mes se causa desde la entrega del dinero (por eso el +1).
        // Si el corte está a futuro (interés prepagado) no hay meses causados.
        $mesesCausados = $fechaPago->greaterThanOrEqualTo($corte)
            ? $corte->diffInMonths($fechaPago) + 1
            : 0;
        if ($prestamo->modalidad === 'retroventa' && $mesesCausados > 0) {
            $mesesCausados = min($mesesCausados, (int) ($prestamo->plazo_meses ?: 4));
        }

        $interesMensual = round((float) $prestamo->saldo_capital * (float) $prestamo->tasa_interes, 2);
        $interesBruto = $interesMensual * $mesesCausados;
        $interesCausado = max(0, round($interesBruto - (float) $prestamo->saldo_interes_favor, 2));

        $montoInteres = min($montoPago, $interesCausado);
        $montoCapital = round($montoPago - $montoInteres, 2);

        if ($montoCapital > (float) $prestamo->saldo_capital + 0.01) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'El pago supera el saldo de capital más el interés causado.',
            ]);
            return;
        }
        $montoCapital = min($montoCapital, (float) $prestamo->saldo_capital);

        // meses completos de interés que cubre el pago (favor previo + pago actual)
        $mesesCubiertos = 0;
        $nuevoFavor = (float) $prestamo->saldo_interes_favor + $montoInteres;
        if ($interesMensual > 0) {
            $mesesCubiertos = (int) floor($nuevoFavor / $interesMensual);
            $mesesCubiertos = min($mesesCubiertos, $mesesCausados);
            $nuevoFavor = round($nuevoFavor - ($mesesCubiertos * $interesMensual), 2);
        }

        $fechaCorteAntes = $prestamo->fecha_corte->toDateString();
        $fechaCorteDespues = $mesesCubiertos > 0
            ? $corte->copy()->addMonths($mesesCubiertos)->toDateString()
            : $fechaCorteAntes;

        $capitalAntes = (float) $prestamo->saldo_capital;
        $capitalDespues = round($capitalAntes - $montoCapital, 2);

        $tipo = $montoInteres > 0 && $montoCapital > 0
            ? 'mixto'
            : ($montoCapital > 0 ? 'abono_capital' : 'pago_interes');

        DB::transaction(function () use (
            $prestamo, $fechaPago, $tipo, $montoInteres, $montoCapital,
            $capitalAntes, $capitalDespues, $fechaCorteAntes, $fechaCorteDespues,
            $mesesCubiertos, $interesCausado, $nuevoFavor
        ) {
            PrestamoMovimiento::create([
                'prestamo_id' => $prestamo->id,
                'fecha' => $fechaPago->toDateString(),
                'tipo' => $tipo,
                'monto_interes' => $montoInteres,
                'monto_capital' => $montoCapital,
                'capital_antes' => $capitalAntes,
                'capital_despues' => $capitalDespues,
                'fecha_corte_antes' => $fechaCorteAntes,
                'fecha_corte_despues' => $fechaCorteDespues,
                'meses_cubiertos' => $mesesCubiertos,
                'interes_causado' => $interesCausado,
                'observacion' => $this->pago_observacion,
                'usuario_id' => Auth::id(),
                'created_at' => now(),
            ]);

            $prestamo->saldo_capital = $capitalDespues;
            $prestamo->fecha_corte = $fechaCorteDespues;
            $prestamo->saldo_interes_favor = $nuevoFavor;

            $interesPendiente = $interesCausado - $montoInteres;
            if ($capitalDespues <= 0 && $interesPendiente <= 0.01) {
                $prestamo->estado = 'pagado';
                $prestamo->fecha_cierre = $fechaPago->toDateString();
            }
            $prestamo->save();
        });

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Pago registrado correctamente.']);
        $this->dispatch('closePagoModal');
    }

    public function adjudicarPrenda($id)
    {
        $prestamo = Prestamo::with('movimientos')->find($id);
        if (! $prestamo || $prestamo->modalidad !== 'retroventa' || $prestamo->estado !== 'activo') {
            return false;
        }
        if (! $prestamo->esta_vencido) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'El contrato aún no está vencido; no se puede adjudicar.',
            ]);
            return false;
        }

        $interesCausado = $prestamo->interes_causado;

        DB::transaction(function () use ($prestamo, $interesCausado) {
            PrestamoMovimiento::create([
                'prestamo_id' => $prestamo->id,
                'fecha' => now()->toDateString(),
                'tipo' => 'adjudicacion',
                'monto_interes' => 0,
                'monto_capital' => 0,
                'capital_antes' => (float) $prestamo->saldo_capital,
                'capital_despues' => (float) $prestamo->saldo_capital,
                'fecha_corte_antes' => $prestamo->fecha_corte->toDateString(),
                'fecha_corte_despues' => $prestamo->fecha_corte->toDateString(),
                'meses_cubiertos' => 0,
                'interes_causado' => $interesCausado,
                'observacion' => 'Prenda adjudicada a la casa por vencimiento del plazo.',
                'usuario_id' => Auth::id(),
                'created_at' => now(),
            ]);

            $prestamo->estado = 'adjudicado';
            $prestamo->fecha_cierre = now()->toDateString();
            $prestamo->save();
        });

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Prenda adjudicada a la casa.']);
        $this->dispatch('closePagoModal');

        return true;
    }

    public function cancelarPrestamo($id)
    {
        $prestamo = Prestamo::with('movimientos')->find($id);
        if (! $prestamo || $prestamo->estado !== 'activo') {
            return false;
        }

        $interesCausado = $prestamo->interes_causado;
        $capitalAntes = (float) $prestamo->saldo_capital;

        DB::transaction(function () use ($prestamo, $interesCausado, $capitalAntes) {
            PrestamoMovimiento::create([
                'prestamo_id' => $prestamo->id,
                'fecha' => now()->toDateString(),
                'tipo' => 'cancelacion',
                'monto_interes' => $interesCausado,
                'monto_capital' => $capitalAntes,
                'capital_antes' => $capitalAntes,
                'capital_despues' => 0,
                'fecha_corte_antes' => $prestamo->fecha_corte->toDateString(),
                'fecha_corte_despues' => now()->toDateString(),
                'meses_cubiertos' => 0,
                'interes_causado' => $interesCausado,
                'observacion' => 'Cancelación total del contrato.',
                'usuario_id' => Auth::id(),
                'created_at' => now(),
            ]);

            $prestamo->saldo_capital = 0;
            $prestamo->saldo_interes_favor = 0;
            $prestamo->estado = 'pagado';
            $prestamo->fecha_cierre = now()->toDateString();
            $prestamo->save();
        });

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Contrato cancelado.']);
        $this->dispatch('closePagoModal');

        return true;
    }

    /* ================================================================= */
    /* Inversionistas                                                   */
    /* ================================================================= */

    public function getInversionistas()
    {
        $this->skipRender();

        return PrestamoInversionista::withCount('prestamos')->orderBy('nombre')->get();
    }

    public function getInversionista($id)
    {
        $inv = PrestamoInversionista::find($id);
        if (! $inv) {
            return;
        }
        $this->inv_id = $inv->id;
        $this->inv_nombre = $inv->nombre;
        $this->inv_tasa = $inv->tasa;
        $this->inv_telefono = $inv->telefono;
        $this->inv_activo = $inv->activo;
        $this->dispatch('openInversionistaModal');
    }

    public function saveInversionista()
    {
        $this->validate([
            'inv_nombre' => 'required|string|max:255',
            'inv_tasa' => 'required|numeric|min:0',
        ]);

        $tasa = (float) $this->inv_tasa;
        if ($tasa > 1) {
            $tasa = $tasa / 100;
        }

        PrestamoInversionista::updateOrCreate(
            ['id' => $this->inv_id],
            [
                'nombre' => trim($this->inv_nombre),
                'tasa' => $tasa,
                'telefono' => $this->inv_telefono,
                'activo' => $this->inv_activo ? 1 : 0,
            ]
        );

        $this->reset(['inv_id', 'inv_nombre', 'inv_tasa', 'inv_telefono']);
        $this->inv_activo = 1;
        $this->resetValidation();
        $this->cargarInversionistas();

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Inversionista guardado.']);
        $this->dispatch('closeInversionistaModal');
    }

    public function deleteInversionista($id)
    {
        $inv = PrestamoInversionista::withCount('prestamos')->find($id);
        if (! $inv || $inv->prestamos_count > 0) {
            return false;
        }
        $inv->delete();
        $this->cargarInversionistas();

        return true;
    }

    public function render()
    {
        return view('livewire.prestamos.prestamos');
    }
}
