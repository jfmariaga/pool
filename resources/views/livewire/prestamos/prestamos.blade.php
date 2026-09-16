<div x-data="prestamos">
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Préstamos</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        @can('crear prestamos')
                            <a href="javascript:" x-show="tab === 'retroventa' || tab === 'personal'"
                                x-on:click="openForm()" class="btn btn-dark"><i class="la la-plus"></i> Nuevo préstamo</a>
                        @endcan
                        @can('gestionar prestamo-inversionistas')
                            <a href="javascript:" x-show="tab === 'inversionistas'" x-on:click="openInversionista()"
                                class="btn btn-dark"><i class="la la-plus"></i> Nuevo inversionista</a>
                        @endcan
                        @can('gestionar prestamo-carteras')
                            <a href="javascript:" x-show="tab === 'carteras'" x-on:click="openCartera()"
                                class="btn btn-dark"><i class="la la-plus"></i> Nueva cartera</a>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="content-body">
                <!-- Pestañas -->
                <ul class="nav nav-tabs mb-1">
                    <li class="nav-item">
                        <a class="nav-link" :class="{ 'active': tab === 'dash_retro' }" href="javascript:"
                            x-on:click="setTab('dash_retro')"><i class="la la-dashboard"></i> Dashboard retroventa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" :class="{ 'active': tab === 'dash_personal' }" href="javascript:"
                            x-on:click="setTab('dash_personal')"><i class="la la-dashboard"></i> Dashboard préstamos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" :class="{ 'active': tab === 'retroventa' }" href="javascript:"
                            x-on:click="setTab('retroventa')">Retroventa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" :class="{ 'active': tab === 'personal' }" href="javascript:"
                            x-on:click="setTab('personal')">Préstamos personales</a>
                    </li>
                    @can('ver prestamo-inversionistas')
                        <li class="nav-item">
                            <a class="nav-link" :class="{ 'active': tab === 'inversionistas' }" href="javascript:"
                                x-on:click="setTab('inversionistas')">Inversionistas</a>
                        </li>
                    @endcan
                    @can('ver prestamo-carteras')
                        <li class="nav-item">
                            <a class="nav-link" :class="{ 'active': tab === 'carteras' }" href="javascript:"
                                x-on:click="setTab('carteras')">Carteras</a>
                        </li>
                    @endcan
                </ul>

                <!-- ===================== DASHBOARD RETROVENTA ===================== -->
                <div x-show="tab === 'dash_retro'">
                    <div x-show="loading"><x-spinner></x-spinner></div>
                    <div x-show="!loading">
                        <div class="row">
                            <template x-for="k in kpisRetro()" :key="k.label">
                                <div class="col-xl-3 col-md-4 col-sm-6 mb-1">
                                    <div class="kpi-card">
                                        <div class="kpi-label" x-text="k.label"></div>
                                        <div class="kpi-value" x-text="k.value"></div>
                                        <div class="kpi-sub" x-text="k.sub"></div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h5>Próximos vencimientos</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Cliente</th>
                                                <th>Inversionista</th>
                                                <th>Vence</th>
                                                <th class="text-right">Saldo capital</th>
                                                <th class="text-right">Interés causado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(v, i) in (dashRetro.proximos_vencimientos || [])" :key="i">
                                                <tr :class="v.vencido ? 'table-warning' : ''">
                                                    <td x-text="v.cliente"></td>
                                                    <td x-text="v.inversionista"></td>
                                                    <td>
                                                        <span x-text="fFecha(v.vence)"></span>
                                                        <span x-show="v.vencido" class="badge badge-danger ml-1">vencido</span>
                                                    </td>
                                                    <td class="text-right" x-text="__numberFormat(v.saldo_capital)"></td>
                                                    <td class="text-right" x-text="__numberFormat(v.interes_causado)"></td>
                                                </tr>
                                            </template>
                                            <tr x-show="!(dashRetro.proximos_vencimientos || []).length">
                                                <td colspan="5" class="text-center text-muted">Sin contratos con vencimiento</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h5>Gramos por inversionista</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Inversionista</th>
                                                <th class="text-right">Contratos</th>
                                                <th class="text-right">Total gramos</th>
                                                <th class="text-right">Promedio ($/gr)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="f in (dashRetro.gramos_por_inversionista || [])" :key="f.inversionista">
                                                <tr>
                                                    <td x-text="f.inversionista"></td>
                                                    <td class="text-right" x-text="f.contratos"></td>
                                                    <td class="text-right" x-text="__numberFormat(f.total_gramos)"></td>
                                                    <td class="text-right" x-text="__numberFormat(f.promedio)"></td>
                                                </tr>
                                            </template>
                                            <tr x-show="!(dashRetro.gramos_por_inversionista || []).length">
                                                <td colspan="4" class="text-center text-muted">Sin contratos activos</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== DASHBOARD PRÉSTAMOS PERSONALES ===================== -->
                <div x-show="tab === 'dash_personal'">
                    <div x-show="loading"><x-spinner></x-spinner></div>
                    <div x-show="!loading">
                        <div class="row">
                            <template x-for="k in kpisPersonal()" :key="k.label">
                                <div class="col-xl-3 col-md-4 col-sm-6 mb-1">
                                    <div class="kpi-card">
                                        <div class="kpi-label" x-text="k.label"></div>
                                        <div class="kpi-value" x-text="k.value"></div>
                                        <div class="kpi-sub" x-text="k.sub"></div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h5>Resumen por cartera</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Cartera</th>
                                                <th class="text-right">Préstamos</th>
                                                <th class="text-right">Capital</th>
                                                <th class="text-right">Interés / mes</th>
                                                <th class="text-right">Interés causado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="f in (dashPersonal.por_cartera || [])" :key="f.nombre">
                                                <tr>
                                                    <td x-text="f.nombre"></td>
                                                    <td class="text-right" x-text="f.contratos"></td>
                                                    <td class="text-right" x-text="__numberFormat(f.capital)"></td>
                                                    <td class="text-right" x-text="__numberFormat(f.interes_mes)"></td>
                                                    <td class="text-right" x-text="__numberFormat(f.interes_causado)"></td>
                                                </tr>
                                            </template>
                                            <tr x-show="!(dashPersonal.por_cartera || []).length">
                                                <td colspan="5" class="text-center text-muted">Sin préstamos activos</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== PRÉSTAMOS (retroventa / personal) ===================== -->
                <div x-show="tab === 'retroventa' || tab === 'personal'">
                    <div class="card">
                        <div class="row card-body">
                            <div class="col-md-12 mb-1"><b>Filtros</b></div>
                            <div class="col-md-4 d-flex">
                                <div>
                                    <x-input type="date" model="$wire.desde" id="p_desde" label="Desde"></x-input>
                                </div>
                                <div class="ml-2">
                                    <x-input type="date" model="$wire.hasta" id="p_hasta" label="Hasta"></x-input>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <x-select model="$wire.estado_filter" id="p_estado_filter" label="Estado">
                                    <option value="0">Todos...</option>
                                    <option value="activo">Activo</option>
                                    <option value="vencido">Vencido</option>
                                    <option value="pagado">Pagado</option>
                                    <option value="adjudicado">Adjudicado</option>
                                </x-select>
                            </div>
                            <div class="col-md-3" x-show="tab === 'retroventa'">
                                <x-select model="$wire.inversionista_filter" id="p_inv_filter" label="Inversionista">
                                    <option value="0">Todos...</option>
                                    @foreach ($inversionistas as $inv)
                                        <option value="{{ $inv->id }}">{{ $inv->nombre }}</option>
                                    @endforeach
                                </x-select>
                            </div>
                            <div class="col-md-3" x-show="tab === 'personal'">
                                <x-select model="$wire.cartera_filter" id="p_cartera_filter" label="Cartera">
                                    <option value="0">Todas...</option>
                                    @foreach ($carteras as $c)
                                        <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                    @endforeach
                                </x-select>
                            </div>
                            <div class="col-md-2">
                                <label>Buscar cliente</label>
                                <input type="text" class="form-control" x-model="$wire.buscar"
                                    placeholder="Nombre o cédula">
                            </div>
                            <div class="col-md-1">
                                <button type="button" x-on:click="cargar()" class="btn btn-outline-dark"
                                    style="margin-top:19px;">Filtrar</button>
                            </div>
                        </div>

                        <div x-show="!loading">
                            <!-- Tabla retroventa -->
                            <div x-show="tab === 'retroventa'">
                                <x-table id="table_retroventa" extra="d-none">
                                    <tr>
                                        <th>Inicio</th>
                                        <th>Vence</th>
                                        <th>Cliente</th>
                                        <th>Inversionista</th>
                                        <th>Monto</th>
                                        <th>Saldo capital</th>
                                        <th>Interés causado</th>
                                        <th>Total adeudado</th>
                                        <th>Meses pag.</th>
                                        <th>Estado</th>
                                        <th>Acc</th>
                                    </tr>
                                </x-table>
                            </div>
                            <!-- Tabla personal -->
                            <div x-show="tab === 'personal'">
                                <x-table id="table_personal" extra="d-none">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Cartera</th>
                                        <th>Monto</th>
                                        <th>Saldo capital</th>
                                        <th>Interés causado</th>
                                        <th>Total adeudado</th>
                                        <th>Meses pag.</th>
                                        <th>Estado</th>
                                        <th>Acc</th>
                                    </tr>
                                </x-table>
                            </div>
                        </div>
                        <div x-show="loading">
                            <x-spinner></x-spinner>
                        </div>
                    </div>
                </div>

                <!-- ===================== INVERSIONISTAS ===================== -->
                <div x-show="tab === 'inversionistas'">
                    <div class="card">
                        <div x-show="!loading">
                            <x-table id="table_inversionistas" extra="d-none">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tasa mensual (reparto)</th>
                                    <th>Préstamos</th>
                                    <th>Estado</th>
                                    <th>Acc</th>
                                </tr>
                            </x-table>
                        </div>
                        <div x-show="loading">
                            <x-spinner></x-spinner>
                        </div>
                    </div>
                </div>

                <!-- ===================== CARTERAS ===================== -->
                <div x-show="tab === 'carteras'">
                    <div class="card">
                        <div x-show="!loading">
                            <x-table id="table_carteras" extra="d-none">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Préstamos</th>
                                    <th>Estado</th>
                                    <th>Acc</th>
                                </tr>
                            </x-table>
                        </div>
                        <div x-show="loading">
                            <x-spinner></x-spinner>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('livewire.prestamos.form-prestamo')
    @include('livewire.prestamos.form-pago')
    @include('livewire.prestamos.form-inversionista')
    @include('livewire.prestamos.form-cartera')

    @script
        <script>
            Alpine.data('prestamos', () => ({
                tab: @js($tab),
                loading: true,
                rows: [],
                inversionistas: [],
                carteras: [],
                dashRetro: {},
                dashPersonal: {},
                pagoAccionRealizada: false,

                init() {
                    this.cargar();

                    window.addEventListener('openPrestamoModal', () => $('#form_prestamo').modal('show'));
                    window.addEventListener('openPagoModal', () => $('#form_pago').modal('show'));
                    window.addEventListener('closePagoModal', () => {
                        $('#form_pago').modal('hide');
                    });
                    // El pago/cancelación/adjudicación ya no cierran el modal solos (se
                    // muestra el cálculo primero); la tabla solo se refresca al cerrar
                    // manualmente y solo si de verdad se registró algo (no al solo ver el detalle).
                    $('#form_pago').on('hidden.bs.modal', () => {
                        if (this.pagoAccionRealizada) this.cargar();
                    });
                    window.addEventListener('openInversionistaModal', () => $('#form_inversionista').modal('show'));
                    window.addEventListener('closeInversionistaModal', () => {
                        $('#form_inversionista').modal('hide');
                        this.cargar();
                    });
                    window.addEventListener('openCarteraModal', () => $('#form_cartera').modal('show'));
                    window.addEventListener('closeCarteraModal', () => {
                        $('#form_cartera').modal('hide');
                        this.cargar();
                    });
                    window.addEventListener('showToast', (data) => {
                        const t = data.detail[0] ?? data.detail;
                        toastRight(t.type, t.message);
                    });

                    ['cartera_id', 'estado_form'].forEach((campo) => {
                        const el = document.getElementById(campo);
                        if (el) $(el).on('change', () => @this.set(campo === 'estado_form' ? 'estado' : campo, el.value));
                    });

                    const invEl = document.getElementById('inversionista_id');
                    if (invEl) $(invEl).on('change', () => {
                        @this.set('inversionista_id', invEl.value);
                        // Solo en préstamos nuevos: sugiere la tasa del inversionista según el
                        // catálogo, pero queda editable (el interés de la casa sigue siendo explícito).
                        if (!@this.prestamo_id && !@this.tasa_interes_inversionista) {
                            const tasaCatalogo = invEl.selectedOptions[0]?.dataset.tasa;
                            if (tasaCatalogo) @this.set('tasa_interes_inversionista', tasaCatalogo);
                        }
                    });

                    // Al salir del campo Cédula, se consulta si ya existe un cliente con esa
                    // cédula para autocompletar nombre/teléfono (evita registrar el mismo
                    // cliente con nombres distintos).
                    const cedulaEl = document.getElementById('cliente_cedula');
                    if (cedulaEl) cedulaEl.addEventListener('blur', () => @this.set('cliente_cedula', cedulaEl.value));

                    // Los select2 de filtros están en wire:ignore: x-model no basta,
                    // hay que empujar el valor a Livewire manualmente al cambiar.
                    [
                        ['p_estado_filter', 'estado_filter'],
                        ['p_inv_filter', 'inversionista_filter'],
                        ['p_cartera_filter', 'cartera_filter'],
                    ].forEach(([id, prop]) => {
                        const el = document.getElementById(id);
                        if (el) $(el).on('change', () => {
                            @this.set(prop, el.value);
                            this.cargar();
                        });
                    });
                },

                setTab(t) {
                    this.tab = t;
                    @this.set('tab', t);
                    this.cargar();
                },

                async cargar() {
                    this.loading = true;
                    if (this.tab === 'dash_retro') {
                        this.dashRetro = await @this.metricasRetroventa();
                    } else if (this.tab === 'dash_personal') {
                        this.dashPersonal = await @this.metricasPersonal();
                    } else if (this.tab === 'inversionistas') {
                        await this.cargarInversionistas();
                    } else if (this.tab === 'carteras') {
                        await this.cargarCarteras();
                    } else {
                        await this.cargarPrestamos();
                    }
                    this.loading = false;
                },

                fFecha(v) {
                    if (!v) return '-';
                    const s = String(v).substring(0, 10);
                    if (!/^\d{4}-\d{2}-\d{2}$/.test(s)) return '-';
                    return __formatDate(s);
                },

                kpisRetro() {
                    const m = this.dashRetro || {};
                    const n = (x) => __numberFormat(x || 0);
                    return [
                        { label: 'Capital colocado', value: n(m.capital), sub: `${m.activos ?? 0} contratos activos` },
                        { label: 'Interés cliente / mes', value: n(m.interes_mes), sub: 'Sobre capital vigente' },
                        { label: 'Pago a inversionistas / mes', value: n(m.pago_inversionistas_mes), sub: 'Según tasa de reparto' },
                        { label: 'Ganancia casa / mes', value: n(m.ganancia_casa_mes), sub: 'Interés cliente − pago inversionistas' },
                        { label: 'Interés causado (pendiente)', value: n(m.interes_causado), sub: 'Acumulado no pagado' },
                        { label: 'Contratos vencidos', value: (m.vencidos ?? 0), sub: `${m.por_vencer ?? 0} por vencer (≤ 15 días)` },
                        { label: 'Prendas adjudicadas', value: (m.adjudicadas ?? 0), sub: `${m.contratos ?? 0} contratos en total` },
                        { label: 'Capital recuperado', value: n(m.recuperado_capital), sub: `Interés cobrado ${n(m.interes_cobrado)}` },
                    ];
                },

                kpisPersonal() {
                    const m = this.dashPersonal || {};
                    const n = (x) => __numberFormat(x || 0);
                    return [
                        { label: 'Capital colocado', value: n(m.capital), sub: `${m.activos ?? 0} préstamos activos` },
                        { label: 'Interés / mes', value: n(m.interes_mes), sub: 'Sobre capital vigente' },
                        { label: 'Interés causado (pendiente)', value: n(m.interes_causado), sub: 'Acumulado no pagado' },
                        { label: 'Capital recuperado', value: n(m.recuperado_capital), sub: `Interés cobrado ${n(m.interes_cobrado)}` },
                        { label: 'Préstamos', value: (m.prestamos ?? 0), sub: `${m.activos ?? 0} activos · ${m.pagados ?? 0} pagados` },
                        { label: 'Clientes', value: (m.clientes ?? 0), sub: 'Con préstamos personales' },
                    ];
                },

                async cargarPrestamos() {
                    this.rows = await @this.getPrestamos();

                    const tableId = this.tab === 'retroventa' ? '#table_retroventa' : '#table_personal';
                    __destroyTable(tableId);
                    let html = '';
                    for (const p of this.rows) {
                        html += this.tab === 'retroventa' ? this.rowRetroventa(p) : this.rowPersonal(p);
                    }
                    $(`${tableId} tbody`).html(html);
                    setTimeout(() => __resetTable(tableId), 200);
                },

                acciones(p) {
                    let btns = `<div class="d-flex">`;
                    btns +=
                        `<x-buttonsm click="verPago('${p.id}')"><i class="la la-eye"></i></x-buttonsm>`;
                    @can('editar prestamos')
                        if (p.estado === 'activo') {
                            btns +=
                                `<x-buttonsm click="openForm('${p.id}')"><i class="la la-edit"></i></x-buttonsm>`;
                        }
                    @endcan
                    @can('eliminar prestamos')
                        btns +=
                            `<x-buttonsm click="eliminar('${p.id}')"><i class="la la-trash"></i></x-buttonsm>`;
                    @endcan
                    btns += `</div>`;
                    return btns;
                },

                badge(estado) {
                    const map = {
                        activo: 'badge-success',
                        vencido: 'badge-warning',
                        pagado: 'badge-secondary',
                        adjudicado: 'badge-danger'
                    };
                    return `<span class="badge ${map[estado] || 'badge-light'}">${estado}</span>`;
                },

                rowRetroventa(p) {
                    return `<tr>
                        <td>${this.fFecha(p.fecha_inicio)}</td>
                        <td>${this.fFecha(p.fecha_vencimiento)}</td>
                        <td>${p.cliente ? p.cliente.nombre : '-'}</td>
                        <td>${p.inversionista ? p.inversionista.nombre : '-'}</td>
                        <td>${__numberFormat(p.monto)}</td>
                        <td>${__numberFormat(p.saldo_capital)}</td>
                        <td>${__numberFormat(p.interes_causado)}</td>
                        <td>${__numberFormat(p.total_adeudado)}</td>
                        <td class="text-center">${p.meses_pagados ?? 0}</td>
                        <td>${this.badge(p.estado_mostrar)}</td>
                        <td>${this.acciones(p)}</td>
                    </tr>`;
                },

                rowPersonal(p) {
                    return `<tr>
                        <td>${this.fFecha(p.fecha_inicio)}</td>
                        <td>${p.cliente ? p.cliente.nombre : '-'}</td>
                        <td>${p.cartera ? p.cartera.nombre : '-'}</td>
                        <td>${__numberFormat(p.monto)}</td>
                        <td>${__numberFormat(p.saldo_capital)}</td>
                        <td>${__numberFormat(p.interes_causado)}</td>
                        <td>${__numberFormat(p.total_adeudado)}</td>
                        <td class="text-center">${p.meses_pagados ?? 0}</td>
                        <td>${this.badge(p.estado_mostrar)}</td>
                        <td>${this.acciones(p)}</td>
                    </tr>`;
                },

                async cargarInversionistas() {
                    this.inversionistas = await @this.getInversionistas();
                    __destroyTable('#table_inversionistas');
                    let html = '';
                    for (const i of this.inversionistas) {
                        html += `<tr>
                            <td>${i.nombre}</td>
                            <td>${(i.tasa * 100).toFixed(2)}%</td>
                            <td>${i.prestamos_count}</td>
                            <td>${i.activo == 1 ? 'Activo' : 'Inactivo'}</td>
                            <td>
                                <div class="d-flex">
                                    <x-buttonsm click="openInversionista('${i.id}')"><i class="la la-edit"></i></x-buttonsm>
                                </div>
                            </td>
                        </tr>`;
                    }
                    $('#table_inversionistas tbody').html(html);
                    setTimeout(() => __resetTable('#table_inversionistas'), 200);
                },

                async cargarCarteras() {
                    this.carteras = await @this.getCarteras();
                    __destroyTable('#table_carteras');
                    let html = '';
                    for (const c of this.carteras) {
                        html += `<tr>
                            <td>${c.nombre}</td>
                            <td>${c.prestamos_count}</td>
                            <td>${c.activo == 1 ? 'Activo' : 'Inactivo'}</td>
                            <td>
                                <div class="d-flex">
                                    <x-buttonsm click="openCartera('${c.id}')"><i class="la la-edit"></i></x-buttonsm>
                                </div>
                            </td>
                        </tr>`;
                    }
                    $('#table_carteras tbody').html(html);
                    setTimeout(() => __resetTable('#table_carteras'), 200);
                },

                /* ---------- form préstamo ---------- */
                openForm(id = null) {
                    const p = this.rows.find((r) => r.id == id) ?? {};
                    @this.set('prestamo_id', p.id ?? null);
                    @this.set('cliente_nombre', p.cliente ? p.cliente.nombre : null);
                    @this.set('cliente_cedula', p.cliente ? p.cliente.cedula : null);
                    @this.set('cliente_telefono', p.cliente ? p.cliente.telefono : null);
                    @this.set('inversionista_id', p.inversionista_id ?? null);
                    @this.set('cartera_id', p.cartera_id ?? null);
                    @this.set('num_contrato', p.num_contrato ?? null);
                    @this.set('peso', p.peso ?? null);
                    @this.set('descripcion_prenda', p.descripcion_prenda ?? null);
                    @this.set('foto_prenda', p.imagen_prenda ? `{{ asset('storage/prestamos') }}/${p.imagen_prenda}` : null);
                    @this.set('foto_prenda_change', false);
                    @this.set('fecha_inicio', p.fecha_inicio ? p.fecha_inicio.substring(0, 10) : new Date().toISOString()
                        .substring(0, 10));
                    @this.set('monto', p.monto ? __numberFormat(p.monto, true) : null);
                    @this.set('tasa_interes_inversionista', p.tasa_interes_inversionista ? (p.tasa_interes_inversionista * 100) : null);
                    @this.set('tasa_interes_cartera', p.tasa_interes_cartera ? (p.tasa_interes_cartera * 100) : null);
                    @this.set('tasa_interes_casa', p.tasa_interes_casa ? (p.tasa_interes_casa * 100) : null);
                    @this.set('observacion', p.observacion ?? null);

                    setTimeout(() => {
                        const inv = document.getElementById('inversionista_id');
                        if (inv) $(inv).val(@this.inversionista_id).trigger('change');
                        const car = document.getElementById('cartera_id');
                        if (car) $(car).val(@this.cartera_id).trigger('change');
                    }, 300);

                    $('#form_prestamo').modal('show');
                },

                async guardarPrestamo() {
                    try {
                        const p = await @this.savePrestamo();
                        if (p) {
                            $('#form_prestamo').modal('hide');
                            toastRight('success', 'Préstamo guardado');

                            // El filtro de fechas (Desde/Hasta) puede dejar el préstamo
                            // recién guardado fuera de la tabla sin ningún aviso; se
                            // ensancha el rango para que siempre quede visible.
                            const fecha = (p.fecha_inicio || '').substring(0, 10);
                            if (fecha) {
                                if (!@this.desde || fecha < @this.desde) await @this.set('desde', fecha);
                                if (!@this.hasta || fecha > @this.hasta) await @this.set('hasta', fecha);
                            }

                            this.cargar();
                        } else {
                            toastRight('error', 'No se pudo guardar. Revise los datos.');
                        }
                    } catch (e) {
                        console.error('guardarPrestamo error', e);
                        toastRight('error', 'No se pudo guardar. Revise los datos.');
                    }
                },

                async eliminar(id) {
                    alertClickCallback('Eliminar préstamo', 'Esta acción no se puede deshacer.', 'warning',
                        'Confirmar', 'Cancelar', async () => {
                            const ok = await @this.deletePrestamo(id);
                            if (ok) {
                                toastRight('success', 'Préstamo eliminado');
                                this.cargar();
                            }
                            // El backend despacha 'showToast' con el motivo específico cuando no se puede eliminar.
                        });
                },

                /* ---------- pagos ---------- */
                verPago(id) {
                    this.pagoAccionRealizada = false;
                    @this.getPago(id);
                },
                registrarPago() {
                    this.pagoAccionRealizada = true;
                    @this.call('registrarPago');
                },
                adjudicar(id) {
                    alertClickCallback('Adjudicar prenda',
                        'La prenda pasará a ser de la casa y el contrato se cerrará.', 'warning',
                        'Confirmar', 'Cancelar', () => {
                            this.pagoAccionRealizada = true;
                            @this.adjudicarPrenda(id);
                        });
                },
                cancelar(id) {
                    const total = __numberFormat(@this.mov_info.total_adeudado || 0);
                    alertClickCallback('Cancelar contrato',
                        `Se registrará el pago total por ${total} (capital + interés causado) y el contrato quedará cerrado.`,
                        'warning', 'Confirmar', 'Cancelar', () => {
                            this.pagoAccionRealizada = true;
                            @this.cancelarPrestamo(id);
                        });
                },

                getImgPrenda() {
                    const file = document.getElementById('img-prenda')['files'][0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onloadend = () => {
                        @this.foto_prenda = reader.result;
                        @this.foto_prenda_change = true;
                    };
                    reader.readAsDataURL(file);
                },

                /* ---------- inversionistas ---------- */
                openInversionista(id = null) {
                    if (id) {
                        @this.getInversionista(id);
                    } else {
                        @this.set('inv_id', null);
                        @this.set('inv_nombre', null);
                        @this.set('inv_tasa', null);
                        @this.set('inv_telefono', null);
                        @this.set('inv_activo', 1);
                        $('#form_inversionista').modal('show');
                    }
                },
                guardarInversionista() {
                    @this.call('saveInversionista');
                },

                /* ---------- carteras ---------- */
                openCartera(id = null) {
                    if (id) {
                        @this.getCartera(id);
                    } else {
                        @this.set('cart_id', null);
                        @this.set('cart_nombre', null);
                        @this.set('cart_activo', 1);
                        $('#form_cartera').modal('show');
                    }
                },
                guardarCartera() {
                    @this.call('saveCartera');
                },
            }));
        </script>
    @endscript
</div>
