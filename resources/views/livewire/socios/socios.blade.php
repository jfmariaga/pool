<div x-data="socios">

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Socios</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="d-flex justify-content-md-end" style="gap: 10px;">
                        @can('editar socios')
                            <a href="javascript:" x-on:click="openSinSocio()" class="btn btn-outline-dark"> <i
                                    class="la la-link"></i> Asignar inventario sin socio</a>
                        @endcan
                        @can('crear socios')
                            <a href="javascript:" x-on:click="openForm()" id="btn_form_socios" class="btn btn-dark"> <i
                                    class="la la-plus"></i> Nuevo</a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="card">
                    <div x-show="!loading">
                        <x-table id="table_socios" extra="d-none">
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Contacto</th>
                                <th>Estado</th>
                                <th>Acción</th>
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

    @include('livewire.socios.form-socios')

    {{-- Modal con KPIs de inventario/trazabilidad del socio --}}
    <x-modal id="kpis_socio" size="lg">
        <x-slot name="title">
            <span x-text="'Inventario de ' + (kpi_socio_nombre ?? '')"></span>
        </x-slot>
        <div x-show="loading_kpis" class="text-center p-3">Cargando...</div>
        <div x-show="!loading_kpis" class="row">
            <div class="col-md-6 mt-1">
                <div class="card p-2">
                    <small>Prendas en inventario (categoría oro)</small>
                    <h4 x-text="kpis.prendas ?? 0"></h4>
                </div>
            </div>
            <div class="col-md-6 mt-1">
                <div class="card p-2">
                    <small>Capital invertido</small>
                    <h4 x-text="__numberFormat(kpis.capital_invertido ?? 0)"></h4>
                </div>
            </div>
            <div class="col-md-6 mt-1">
                <div class="card p-2">
                    <small>Unidades vendidas</small>
                    <h4 x-text="kpis.unidades_vendidas ?? 0"></h4>
                </div>
            </div>
            <div class="col-md-6 mt-1">
                <div class="card p-2">
                    <small>Total vendido</small>
                    <h4 x-text="__numberFormat(kpis.total_vendido ?? 0)"></h4>
                </div>
            </div>
            <div class="col-md-12 mt-1">
                <div class="card p-2">
                    <small>Margen de ganancia</small>
                    <h4 x-text="__numberFormat(kpis.margen_ganancia ?? 0)"></h4>
                </div>
            </div>
        </div>
        <x-slot name="footer">
            <span>
                <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Cerrar</button>
            </span>
        </x-slot>
    </x-modal>

    {{-- Modal para asignar retroactivamente un socio a compras de oro que quedaron sin socio --}}
    <x-modal id="sin_socio" size="lg">
        <x-slot name="title">Asignar inventario sin socio</x-slot>

        <div x-show="loading_sin_socio" class="text-center p-3">Cargando...</div>

        <div x-show="!loading_sin_socio">
            <template x-if="det_sin_socio.length == 0">
                <p>No hay líneas de compra de la categoría oro pendientes por asignar a un socio.</p>
            </template>

            <template x-if="det_sin_socio.length > 0">
                <div>
                    <div class="row mb-2">
                        <div class="col-md-8">
                            <select class="form-control" x-model="socio_asignar">
                                <option value="">Seleccionar socio a asignar...</option>
                                @foreach ($socios_activos as $i)
                                    <option value="{{ $i->id }}">{{ $i->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-primary w-100"
                                x-on:click="confirmarAsignarSocio()">Asignar seleccionados</button>
                        </div>
                    </div>
                    <table class="table table-striped w-100">
                        <thead>
                            <tr>
                                <th><input type="checkbox" x-on:change="toggleAllSinSocio($event)"></th>
                                <th>Producto</th>
                                <th>Fecha compra</th>
                                <th>Comprado</th>
                                <th>Restante</th>
                                <th>Precio compra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, key) in det_sin_socio" :key="key">
                                <tr>
                                    <td><input type="checkbox" :value="item.id" x-model="seleccionados_sin_socio"></td>
                                    <td x-text="item.producto"></td>
                                    <td x-text="item.fecha"></td>
                                    <td x-text="item.stock_compra"></td>
                                    <td x-text="item.stock"></td>
                                    <td x-text="__numberFormat(item.precio_compra)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>

        <x-slot name="footer">
            <span>
                <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Cerrar</button>
            </span>
        </x-slot>
    </x-modal>

    @script
        <script>
            Alpine.data('socios', () => ({
                socios: [],
                loading: true,
                loading_kpis: false,
                kpis: {},
                kpi_socio_nombre: null,
                loading_sin_socio: false,
                det_sin_socio: [],
                seleccionados_sin_socio: [],
                socio_asignar: '',

                init() { // se ejecuta cuando ya la aplicación esta lista visualmente
                    this.getSocios()
                    $('#status').change(() => {
                        val = $('#status').val()

                        @this.status = val
                    })
                },

                async getSocios() {

                    this.loading = true

                    this.socios = await @this.getSocios() // consultamos

                    for (const socio of this.socios) {
                        add = await this.addsocioTable(socio) // agregamos
                    }

                    setTimeout(() => { // necesario para que no se renderice datatable antes de haber cargado el body
                        __resetTable('#table_socios')
                        this.loading = false
                    }, 500);

                },

                async addsocioTable(socio, is_update = false) { // agregamos cada socio a la tabla

                    tr = ``;

                    if (!is_update) {
                        tr += `<tr id="socio_${socio.id}">`
                    }

                    tr += `<td>${ socio.documento ? socio.documento :'' }</td>
                        <td>${ socio.nombre }</td>
                        <td>${ socio.telefono ? socio.telefono :''  }</td>
                        <td>${ socio.email ? socio.email :''  }</td>
                        <td>${ socio.contacto ? socio.contacto :''  }</td>
                        <td>${ socio.status == 1 ?  '<span style="color: green;">✔</span>' : '<span style="color: red;">✘</span>'}</td>
                        <td>
                            <div class="d-flex">
                                <x-buttonsm click="verInventario(${socio.id}, '${socio.nombre}')"><i class="la la-eye"></i> </x-buttonsm>
                                @can('editar socios')
                                <x-buttonsm click="openForm(${socio.id})"><i class="la la-edit"></i> </x-buttonsm>
                                @endcan
                                @can('eliminar socios')
                                <x-buttonsm click="confirmDelete(${socio.id})" color="danger"><i class="la la-trash"></i> </x-buttonsm>
                                @endcan
                            </div>
                        </td>`

                    if (!is_update) {
                        tr += `</tr>`
                        $('#body_table_socios').prepend(tr)
                    } else {
                        $(`#socio_${socio.id}`).html(tr)
                    }

                },

                async verInventario(socio_id, nombre) {
                    this.kpi_socio_nombre = nombre
                    this.kpis = {}
                    this.loading_kpis = true
                    $('#kpis_socio').modal('show')

                    this.kpis = await @this.getKpisSocio(socio_id)
                    this.loading_kpis = false
                },

                async openSinSocio() {
                    this.seleccionados_sin_socio = []
                    this.socio_asignar = ''
                    this.loading_sin_socio = true
                    $('#sin_socio').modal('show')

                    this.det_sin_socio = await @this.getDetComprasSinSocio()
                    this.loading_sin_socio = false
                },

                toggleAllSinSocio(event) {
                    this.seleccionados_sin_socio = event.target.checked ?
                        this.det_sin_socio.map((item) => item.id) : []
                },

                confirmarAsignarSocio() {
                    if (!this.socio_asignar) {
                        toastRight('warning', 'Selecciona un socio')
                        return
                    }
                    if (this.seleccionados_sin_socio.length == 0) {
                        toastRight('warning', 'Selecciona al menos una línea de compra')
                        return
                    }

                    const socio_nombre = $(`option[value="${this.socio_asignar}"]`).text()

                    alertClickCallback('Asignar',
                        `Se asignarán ${this.seleccionados_sin_socio.length} línea(s) de compra a ${socio_nombre}`,
                        'warning', 'Confirmar', 'Cancelar', async () => {
                            const ok = await @this.asignarSocio(this.seleccionados_sin_socio, this
                                .socio_asignar)

                            if (ok) {
                                toastRight('success', 'Inventario asignado al socio con éxito')
                                $('#sin_socio').modal('hide')
                                this.det_sin_socio = this.det_sin_socio.filter((item) => !this
                                    .seleccionados_sin_socio.includes(item.id))
                                this.seleccionados_sin_socio = []
                            } else {
                                toastRight('error', 'No se pudo asignar el inventario')
                            }
                        })
                },

                async saveFront() {
                    const is_update = @this.socio_id ? true : false;

                    const socio = await @this.save()
                    if (socio) {
                        this.addsocioTable(socio, is_update)
                        $('#form_socios').modal('hide')
                        if (is_update) {
                            for (const key in this.socios) {
                                if (this.socios[key].id == socio.id) {
                                    this.socios[key] = socio;
                                }
                            }
                            toastRight('success', 'Socio actualizado con éxito');
                        } else {
                            this.socios.push(socio);
                            toastRight('success', 'Socio registrado con éxito');
                        }
                    }
                },

                openForm(socio_id = null) {
                    let socio_edit = this.socios.find((socio) => socio.id == socio_id);
                    socio_edit = socio_edit ?? {} // sino encunetra resultado declaramos el obteto vacío

                    @this.socio_id = socio_id ? socio_edit.id : null

                    @this.documento = socio_id ? socio_edit.documento : null
                    @this.nombre = socio_id ? socio_edit.nombre : null
                    @this.direccion = socio_id ? socio_edit.direccion : null
                    @this.telefono = socio_id ? socio_edit.telefono : null
                    @this.correo = socio_id ? socio_edit.email : null
                    @this.contacto = socio_id ? socio_edit.contacto : null
                    @this.status = socio_id ? socio_edit.status : 1
                    $('#form_socios').modal('show')

                    setTimeout(() => {
                            const statusElement = document.getElementById('status');
                            if (statusElement) {
                                $(statusElement).val(@this.status).trigger('change');
                            }
                        },
                        300
                    );
                },

                confirmDelete(socio_id) {
                    alertClickCallback('Inactivar',
                        'No podras registrar nuevas compras con este socio', 'warning',
                        'Confirmar', 'Cancelar', async () => {
                            const res = await @this.deleteSocio(socio_id)

                            if (res) {
                                const is_update = true
                                this.addsocioTable(res, is_update)
                                if (is_update) {
                                    for (const key in this.socios) {
                                        if (this.socios[key].id == res.id) {
                                            this.socios[key] = res;
                                        }
                                    }
                                    toastRight('error', 'Socio inactivo!')
                                }
                            }
                        })
                },
            }))
        </script>
    @endscript
</div>
