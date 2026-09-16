<x-modal id="form_pago" size="lg">
    <x-slot name="title">
        <span>Detalle del préstamo — {{ $mov_cliente }}</span>
    </x-slot>

    <div class="row">
        <div class="col-md-3 mb-1"><small class="text-muted d-block">Préstamo</small>#{{ $pago_prestamo_id }}</div>
        <div class="col-md-3 mb-1"><small class="text-muted d-block">Estado</small><span class="text-uppercase">{{ $mov_estado }}</span></div>
        <div class="col-md-3 mb-1"><small class="text-muted d-block">Saldo capital</small>${{ number_format((float) $mov_saldo_capital, 0) }}</div>
        <div class="col-md-3 mb-1"><small class="text-muted d-block">Interés causado</small>${{ number_format((float) $mov_interes_causado, 0) }}</div>

        @if (($mov_info['modalidad'] ?? '') === 'retroventa')
            <div class="col-md-3 mb-1"><small class="text-muted d-block">Inversionista</small>{{ ($mov_info['inversionista'] ?? '') ?: '-' }}</div>
            <div class="col-md-3 mb-1"><small class="text-muted d-block"># Contrato</small>{{ ($mov_info['num_contrato'] ?? '') ?: '-' }}</div>
            <div class="col-md-3 mb-1"><small class="text-muted d-block">Vence</small>{{ $mov_fecha_vencimiento ?: '-' }}</div>
            <div class="col-md-3 mb-1"><small class="text-muted d-block">Meses pagados</small>{{ $mov_info['meses_pagados'] ?? 0 }}</div>
            <div class="col-md-3 mb-1"><small class="text-muted d-block">Cédula</small>{{ ($mov_info['cedula'] ?? '') ?: '-' }}</div>
            <div class="col-md-3 mb-1"><small class="text-muted d-block">Peso (gr)</small>{{ ($mov_info['peso'] ?? '') ?: '-' }}</div>
            <div class="col-md-3 mb-1"><small class="text-muted d-block">Promedio ($/gr)</small>${{ number_format((float) ($mov_info['promedio'] ?? 0), 0) }}</div>
            <div class="col-md-3 mb-1"><small class="text-muted d-block">Tasa total cliente</small>{{ number_format((float) ($mov_info['tasa'] ?? 0) * 100, 2) }}%</div>
            <div class="col-md-3 mb-1">
                <small class="text-muted d-block">Interés inversionista/mes</small>
                ${{ number_format((float) ($mov_info['interes_inversionista_mensual'] ?? 0), 0) }}
                <span class="text-muted">({{ number_format((float) ($mov_info['tasa_inversionista'] ?? 0) * 100, 2) }}%)</span>
            </div>
            <div class="col-md-3 mb-1">
                <small class="text-muted d-block">Interés casa/mes</small>
                ${{ number_format((float) ($mov_info['interes_casa_mensual'] ?? 0), 0) }}
                <span class="text-muted">({{ number_format((float) ($mov_info['tasa_casa'] ?? 0) * 100, 2) }}%)</span>
            </div>
            <div class="col-12 mb-1"><small class="text-muted d-block">Prenda</small>{{ ($mov_info['prenda'] ?? '') ?: '-' }}</div>
            @if (!empty($mov_info['imagen_prenda']))
                <div class="col-12 mb-1">
                    <small class="text-muted d-block">Foto de la prenda</small>
                    <a href="{{ asset('storage/prestamos/'.$mov_info['imagen_prenda']) }}" target="_blank">
                        <img src="{{ asset('storage/prestamos/'.$mov_info['imagen_prenda']) }}" alt="Foto de la prenda"
                            style="max-height:120px;border-radius:6px;">
                    </a>
                </div>
            @endif
        @else
            <div class="col-md-3 mb-1"><small class="text-muted d-block">Cartera</small>{{ ($mov_info['cartera'] ?? '') ?: '-' }}</div>
            <div class="col-md-3 mb-1"><small class="text-muted d-block">Cédula</small>{{ ($mov_info['cedula'] ?? '') ?: '-' }}</div>
            <div class="col-md-3 mb-1"><small class="text-muted d-block">Tasa total cliente</small>{{ number_format((float) ($mov_info['tasa'] ?? 0) * 100, 2) }}%</div>
            <div class="col-md-3 mb-1"><small class="text-muted d-block">Meses pagados</small>{{ $mov_info['meses_pagados'] ?? 0 }}</div>
            <div class="col-md-3 mb-1">
                <small class="text-muted d-block">Interés cartera/mes</small>
                ${{ number_format((float) ($mov_info['interes_cartera_mensual'] ?? 0), 0) }}
                <span class="text-muted">({{ number_format((float) ($mov_info['tasa_cartera'] ?? 0) * 100, 2) }}%)</span>
            </div>
            <div class="col-md-3 mb-1">
                <small class="text-muted d-block">Interés casa/mes</small>
                ${{ number_format((float) ($mov_info['interes_casa_mensual'] ?? 0), 0) }}
                <span class="text-muted">({{ number_format((float) ($mov_info['tasa_casa'] ?? 0) * 100, 2) }}%)</span>
            </div>
        @endif

        <div class="col-md-3 mb-1"><small class="text-muted d-block">Monto original</small>${{ number_format((float) ($mov_info['monto'] ?? 0), 0) }}</div>
        <div class="col-md-3 mb-1"><small class="text-muted d-block">Inicio</small>{{ $mov_info['fecha_inicio'] ?? '-' }}</div>
        <div class="col-md-3 mb-1"><small class="text-muted d-block">Total adeudado hoy</small>${{ number_format((float) ($mov_info['total_adeudado'] ?? 0), 0) }}</div>
        @if (!empty($mov_info['observacion']))
            <div class="col-12 mb-1"><small class="text-muted d-block">Observación</small>{{ $mov_info['observacion'] }}</div>
        @endif
        <div class="col-12"><hr></div>
    </div>

    @if ($pago_resultado)
        <div class="row">
            <div class="col-12">
                @if (($pago_resultado['tipo'] ?? 'pago') === 'cancelacion')
                    <div class="alert alert-success mb-2">
                        <b>Contrato cancelado.</b> Total recibido: ${{ number_format((float) ($pago_resultado['total_recibido'] ?? 0), 0) }}
                        (interés: ${{ number_format((float) ($pago_resultado['monto_interes'] ?? 0), 0) }}
                        + capital: ${{ number_format((float) ($pago_resultado['monto_capital'] ?? 0), 0) }})
                    </div>
                @else
                    <div class="alert alert-success mb-2">
                        <b>Pago registrado.</b> A interés: ${{ number_format((float) ($pago_resultado['monto_interes'] ?? 0), 0) }}
                        · A capital: ${{ number_format((float) ($pago_resultado['monto_capital'] ?? 0), 0) }}
                        · Meses cubiertos: {{ $pago_resultado['meses_cubiertos'] ?? 0 }}
                        · Nuevo saldo capital: ${{ number_format((float) ($pago_resultado['saldo_capital'] ?? 0), 0) }}
                        · Nueva fecha de corte: {{ \Illuminate\Support\Carbon::parse($pago_resultado['fecha_corte'])->format('d/m/Y') }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if (in_array($mov_estado, ['activo', 'vencido']))
        <div class="row">
            <div class="col-md-12"><h5>Registrar pago</h5></div>
            <div class="col-md-4 mt-1">
                <x-input model="$wire.pago_monto" label="Monto del pago" required="true" class="mask_decimales"></x-input>
            </div>
            <div class="col-md-4 mt-1">
                <x-input type="date" model="$wire.pago_fecha" label="Fecha del pago" required="true"></x-input>
            </div>
            <div class="col-md-4 mt-1">
                <x-input model="$wire.pago_observacion" label="Observación"></x-input>
            </div>
            <div class="col-12 mt-1">
                <small class="text-muted">El pago cubre primero el interés causado; el excedente abona a capital. Cada
                    mes completo de interés pagado corre la fecha de corte y, en retroventa, renueva el plazo de 4
                    meses.</small>
            </div>
            <div class="col-12 mt-1 d-flex" style="gap:8px;">
                <button type="button" class="btn btn-outline-primary" x-on:click="registrarPago()">Registrar pago</button>
                <button type="button" class="btn btn-outline-dark" x-on:click="cancelar('{{ $pago_prestamo_id }}')">
                    Cancelar contrato (pago total)</button>
                @if ($mov_estado === 'vencido')
                    @if ($mov_info['puede_adjudicar'] ?? false)
                        <button type="button" class="btn btn-outline-danger"
                            x-on:click="adjudicar('{{ $pago_prestamo_id }}')">Adjudicar prenda a la casa</button>
                    @else
                        <small class="text-muted align-self-center">
                            En periodo de gracia hasta
                            {{ !empty($mov_info['fecha_limite_adjudicacion']) ? \Illuminate\Support\Carbon::parse($mov_info['fecha_limite_adjudicacion'])->format('d/m/Y') : '-' }}
                        </small>
                    @endif
                @endif
            </div>
        </div>
        <hr>
    @endif

    <div class="row">
        <div class="col-md-12">
            <h5>Movimientos</h5>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th class="text-right">A interés</th>
                            <th class="text-right">A capital</th>
                            <th class="text-right">Capital antes</th>
                            <th class="text-right">Capital después</th>
                            <th class="text-center">Meses cub.</th>
                            <th>Nuevo corte</th>
                            <th>Obs.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movimientos as $m)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($m->fecha)->format('d/m/Y') }}</td>
                                <td>{{ str_replace('_', ' ', $m->tipo) }}</td>
                                <td class="text-right">{{ number_format((float) $m->monto_interes) }}</td>
                                <td class="text-right">{{ number_format((float) $m->monto_capital) }}</td>
                                <td class="text-right">{{ number_format((float) $m->capital_antes) }}</td>
                                <td class="text-right">{{ number_format((float) $m->capital_despues) }}</td>
                                <td class="text-center">{{ $m->meses_cubiertos }}</td>
                                <td>{{ $m->fecha_corte_despues ? \Illuminate\Support\Carbon::parse($m->fecha_corte_despues)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $m->observacion }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">Sin movimientos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Cerrar</button>
    </x-slot>
</x-modal>
