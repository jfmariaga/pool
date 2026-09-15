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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;
use Livewire\Attributes\Url;
use Livewire\Component;

class Prestamos extends Component
{
    use General;

    #[Url]
    public $tab = 'dash_retro';

    public $inversionistas = [];

    // ---- filtros ----
    public $desde, $hasta, $estado_filter, $inversionista_filter, $cartera_filter, $buscar;

    // ---- formulario prestamo ----
    public $prestamo_id;
    public $cliente_nombre, $cliente_cedula, $cliente_telefono;
    public $inversionista_id, $cartera, $num_contrato, $peso, $descripcion_prenda;
    public $fecha_inicio, $monto, $tasa_interes, $observacion;
    public $tasa_interes_inversionista, $tasa_interes_casa;
    public $foto_prenda, $foto_prenda_change = false;

    // ---- formulario pago ----
    public $pago_prestamo_id, $pago_monto, $pago_fecha, $pago_observacion;
    public $mov_saldo_capital, $mov_interes_causado, $mov_fecha_vencimiento, $mov_cliente, $mov_estado;
    public $mov_info = [];
    public $movimientos = [];
    public $pago_resultado = null;

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
        $interesCasaMes = (float) $act->sum('interes_casa_mensual');

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
            'ganancia_casa_mes' => round($interesCasaMes, 2),
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
            'gramos_por_inversionista' => $this->resumenGramosInversionista(),
        ];
    }

    /**
     * Total de gramos empeñados y precio promedio por gramo, agrupados por inversionista
     * (solo retroventa activos). El promedio sigue la misma definición que el accessor
     * `promedio` del modelo: monto / peso (no gramos por contrato).
     */
    private function resumenGramosInversionista()
    {
        $activos = Prestamo::with('inversionista')->modalidad('retroventa')->where('estado', 'activo')->get();

        $filas = [];
        foreach ($activos->groupBy('inversionista_id') as $invId => $grupo) {
            $totalGramos = (float) $grupo->sum(fn ($p) => (float) $p->peso);
            $totalMonto = (float) $grupo->sum(fn ($p) => (float) $p->monto);
            $filas[] = [
                'inversionista' => $grupo->first()->inversionista->nombre ?? 'Sin inversionista',
                'contratos' => $grupo->count(),
                'total_gramos' => round($totalGramos, 2),
                'promedio' => $totalGramos > 0 ? round($totalMonto / $totalGramos, 2) : 0,
            ];
        }

        return $filas;
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

    /** Normaliza una tasa ingresada como porcentaje (7) o fracción (0.07) a fracción. */
    private function normalizarTasa($valor): float
    {
        $tasa = (float) ($valor ?: 0);

        return $tasa > 1 ? $tasa / 100 : $tasa;
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
            $reglas['tasa_interes_inversionista'] = 'required|numeric|min:0';
            $reglas['tasa_interes_casa'] = 'required|numeric|min:0';
        } else {
            $reglas['cartera'] = 'required|string';
        }
        $this->validate($reglas, [
            'cliente_cedula.required' => 'La cédula del cliente es obligatoria.',
            'tasa_interes_inversionista.required' => 'La tasa del inversionista es obligatoria.',
            'tasa_interes_casa.required' => 'La tasa de la casa es obligatoria.',
        ]);

        $montoLimpio = (float) $this->__limpiarNumDecimales($this->monto);
        if ($montoLimpio <= 0) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'El monto debe ser mayor a cero.']);
            return false;
        }

        // Retroventa: el interés del inversionista y de la casa son explícitos por
        // préstamo; la tasa que paga el cliente es la suma de ambos (no se edita directo).
        // Personal: sigue siendo una sola tasa editable.
        if ($modalidad === 'retroventa') {
            $tasaInversionista = $this->normalizarTasa($this->tasa_interes_inversionista);
            $tasaCasa = $this->normalizarTasa($this->tasa_interes_casa);
            $tasa = round($tasaInversionista + $tasaCasa, 4);
        } else {
            $tasaInversionista = null;
            $tasaCasa = null;
            $tasa = $this->tasa_interes !== null && $this->tasa_interes !== ''
                ? $this->normalizarTasa($this->tasa_interes)
                : $this->tasaPorDefecto($modalidad);
        }

        $cliente = PrestamoCliente::firstOrCreate(
            $this->cliente_cedula
                ? ['cedula' => trim($this->cliente_cedula)]
                : ['nombre' => trim($this->cliente_nombre)],
            ['nombre' => trim($this->cliente_nombre), 'telefono' => $this->cliente_telefono]
        );

        $prestamoExistente = $this->prestamo_id ? Prestamo::find($this->prestamo_id) : null;

        $imagenPrenda = $prestamoExistente->imagen_prenda ?? null;
        if ($modalidad === 'retroventa' && $this->foto_prenda_change && $this->foto_prenda) {
            $imagenPrenda = $this->processImagenPrenda($this->foto_prenda);
            if ($prestamoExistente?->imagen_prenda) {
                Storage::disk('public')->delete('prestamos/'.$prestamoExistente->imagen_prenda);
            }
        } elseif ($modalidad !== 'retroventa') {
            $imagenPrenda = null;
        }

        $base = [
            'cliente_id' => $cliente->id,
            'inversionista_id' => $modalidad === 'retroventa' ? $this->inversionista_id : null,
            'cartera' => $modalidad === 'personal' ? $this->cartera : null,
            'num_contrato' => $this->num_contrato,
            'peso' => $this->peso ?: null,
            'descripcion_prenda' => $this->descripcion_prenda,
            'imagen_prenda' => $imagenPrenda,
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
                $base['tasa_interes_inversionista'] = $tasaInversionista;
                $base['tasa_interes_casa'] = $tasaCasa;
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
                'tasa_interes_inversionista' => $tasaInversionista,
                'tasa_interes_casa' => $tasaCasa,
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

        if ($prestamo->estado === 'adjudicado') {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'No se puede eliminar: la prenda ya fue adjudicada a la casa.',
            ]);
            return false;
        }

        if ($prestamo->movimientos->where('tipo', '!=', 'desembolso')->isNotEmpty()) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'No se puede eliminar: el préstamo ya tiene abonos u otros movimientos registrados.',
            ]);
            return false;
        }

        $prestamo->movimientos()->delete();
        $prestamo->delete();

        return true;
    }

    /** Decodifica una imagen base64 (data URL), la redimensiona y la guarda en storage/prestamos. */
    private function processImagenPrenda(string $base64): string
    {
        $partes = explode(';base64,', $base64);
        $tipo = explode('image/', $partes[0])[1] ?? 'jpg';
        $binario = base64_decode($partes[1] ?? '');

        $nombre = 'prenda-'.date('Ymdhis').Str::random(5).'.'.$tipo;

        $img = Image::make($binario)->widen(700, function ($constraint) {
            $constraint->upsize();
        })->encode($tipo);
        Storage::disk('public')->put('prestamos/'.$nombre, $img);

        return $nombre;
    }

    public function resetForm()
    {
        $this->reset([
            'prestamo_id', 'cliente_nombre', 'cliente_cedula', 'cliente_telefono',
            'inversionista_id', 'cartera', 'num_contrato', 'peso', 'descripcion_prenda',
            'fecha_inicio', 'monto', 'tasa_interes', 'tasa_interes_inversionista', 'tasa_interes_casa',
            'observacion', 'foto_prenda', 'foto_prenda_change',
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
        $this->pago_resultado = null;

        $this->mov_info = [
            'modalidad' => $prestamo->modalidad,
            'cedula' => $prestamo->cliente->cedula ?? '',
            'inversionista' => $prestamo->inversionista->nombre ?? '',
            'cartera' => $prestamo->cartera ?? '',
            'num_contrato' => $prestamo->num_contrato ?? '',
            'peso' => $prestamo->peso,
            'prenda' => $prestamo->descripcion_prenda ?? '',
            'imagen_prenda' => $prestamo->imagen_prenda ?? '',
            'promedio' => $prestamo->promedio,
            'tasa' => (float) $prestamo->tasa_interes,
            'tasa_inversionista' => (float) ($prestamo->tasa_interes_inversionista ?? 0),
            'tasa_casa' => (float) ($prestamo->tasa_interes_casa ?? 0),
            'interes_inversionista_mensual' => $prestamo->interes_inversionista_mensual,
            'interes_casa_mensual' => $prestamo->interes_casa_mensual,
            'monto' => (float) $prestamo->monto,
            'fecha_inicio' => optional($prestamo->fecha_inicio)->toDateString(),
            'meses_pagados' => $prestamo->meses_pagados,
            'meses_causados' => $prestamo->meses_causados,
            'total_adeudado' => $prestamo->total_adeudado,
            'observacion' => $prestamo->observacion ?? '',
            'puede_adjudicar' => $prestamo->puede_adjudicar,
            'fecha_limite_adjudicacion' => $prestamo->fecha_limite_adjudicacion,
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
        // Carbon 3 devuelve diffInMonths() como float; truncar a meses completos.
        $mesesCausados = $fechaPago->greaterThanOrEqualTo($corte)
            ? (int) $corte->diffInMonths($fechaPago) + 1
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

        $resultado = [
            'tipo' => 'pago',
            'monto_interes' => $montoInteres,
            'monto_capital' => $montoCapital,
            'meses_cubiertos' => $mesesCubiertos,
            'saldo_capital' => $capitalDespues,
            'fecha_corte' => $fechaCorteDespues,
            'saldo_interes_favor' => $nuevoFavor,
        ];

        // Se refresca el modal (saldo, movimientos, estado) en vez de cerrarlo,
        // para que el usuario vea el desglose del pago antes de cerrar manualmente.
        $this->getPago($prestamo->id);
        $this->pago_resultado = $resultado;
    }

    public function adjudicarPrenda($id)
    {
        $prestamo = Prestamo::with('movimientos')->find($id);
        if (! $prestamo || $prestamo->modalidad !== 'retroventa' || $prestamo->estado !== 'activo') {
            return false;
        }
        if (! $prestamo->puede_adjudicar) {
            $mensaje = $prestamo->esta_vencido
                ? 'El contrato está en periodo de gracia (10 días) hasta '
                    .Carbon::parse($prestamo->fecha_limite_adjudicacion)->format('d/m/Y').'.'
                : 'El contrato aún no está vencido; no se puede adjudicar.';
            $this->dispatch('showToast', ['type' => 'error', 'message' => $mensaje]);
            return false;
        }

        $interesCausado = $prestamo->interes_causado;
        $capitalAntes = (float) $prestamo->saldo_capital;

        DB::transaction(function () use ($prestamo, $interesCausado, $capitalAntes) {
            PrestamoMovimiento::create([
                'prestamo_id' => $prestamo->id,
                'fecha' => now()->toDateString(),
                'tipo' => 'adjudicacion',
                'monto_interes' => 0,
                'monto_capital' => $capitalAntes,
                'capital_antes' => $capitalAntes,
                'capital_despues' => 0,
                'fecha_corte_antes' => $prestamo->fecha_corte->toDateString(),
                'fecha_corte_despues' => $prestamo->fecha_corte->toDateString(),
                'meses_cubiertos' => 0,
                'interes_causado' => $interesCausado,
                'observacion' => 'Prenda adjudicada a la casa por vencimiento del plazo.',
                'usuario_id' => Auth::id(),
                'created_at' => now(),
            ]);

            $prestamo->saldo_capital = 0;
            $prestamo->saldo_interes_favor = 0;
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

        $resultado = [
            'tipo' => 'cancelacion',
            'monto_interes' => $interesCausado,
            'monto_capital' => $capitalAntes,
            'total_recibido' => round($interesCausado + $capitalAntes, 2),
        ];

        // Se refresca el modal en vez de cerrarlo, para que el usuario vea el total
        // recibido (capital + interés causado) antes de cerrar manualmente.
        $this->getPago($prestamo->id);
        $this->pago_resultado = $resultado;

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
