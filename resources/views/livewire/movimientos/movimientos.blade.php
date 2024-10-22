<div x-data="movimientos">

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Movimientos</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        @can('crear movimientos')
                            <a href="javascript:" x-on:click="openForm()" id="btn_form_movimiento" class="btn btn-dark"> <i
                                    class="la la-plus"></i> Nuevo Movimiento</a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="card">
                    <div class="row card-body">
                        <div class="col-md-12 mb-1">
                            <b>Filtros</b>
                        </div>
                        <div class="col-md-4 d-flex">
                            <div class="">
                                {{-- <span x-text="$wire.desde"></span> --}}
                                <x-input type="date" model="$wire.desde" id="desde" class="form-control"
                                    label="Desde"></x-input>
                            </div>
                            <div class="ml-2">
                                <x-input type="date" model="$wire.hasta" id="hasta" class="form-control"
                                    label="Hasta"></x-input>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <x-select model="$wire.tipo_filter" id="tipo_filter" label="Filtrar por tipo">
                                <option value="0">Todas...</option>
                                <option value="ingreso">Ingreso</option>
                                <option value="egreso">Egreso</option>
                            </x-select>
                        </div>
                        <div class="col-md-3">
                            <x-select model="$wire.cuenta_id_filter" id="cuenta_id_filter" label="Filtrar por cuenta">
                                <option value="0">Todos...</option>
                                @foreach ($cuentas as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-2">
                            <x-select model="$wire.usuario_id" id="usuario_id" label="Filtrar por usuario">
                                <option value="0">Todas...</option>
                                @foreach ($usuarios as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-1">
                            <button type="button" x-on:click="getMovimientos()" class="btn btn-outline-dark"
                                style="margin-top:19px;">Filtrar</button>
                        </div>
                    </div>
                    <div x-show="!loading">
                        <x-table id="table_movimientos" extra="d-none">
                            <tr>
                                <th>Fecha</th>
                                <th>Cuenta</th>
                                <th>Tipo</th>
                                <th>Movimiento</th>
                                <th>Valor</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
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

    <x-modal id="movimiento" size="md">
        <x-slot name="title">
            <span>Comprobante del Movimiento</span>
        </x-slot>
        <div>
            <template x-if="( typeof m.usuario !== 'undefined' )">
                <div class="col-md-12 p-2">
                    <div class="d-flex">
                        <div class="w_150px"><b>Fecha:</b></div>
                        <div x-text="m.fecha"></div>
                    </div>
                    <div class="d-flex">
                        <div class="w_150px"><b>Creada por:</b></div>
                        <div x-text="`${m.usuario.name} ${m.usuario.last_name}`"></div>
                    </div>
                    <div class="d-flex">
                        <div class="w_150px"><b>Tipo:</b></div>
                        <div x-text="m.tipo"></div>
                    </div>
                    <div class="d-flex">
                        <div class="w_150px"><b>Movimiento:</b></div>
                        <div x-text="m.compra_id ? 'Compra' : (m.venta_id ? 'Venta' : 'Manual')"></div>
                    </div>
                    <div class="d-flex">
                        <div class="w_150px"><b>Cuenta:</b></div>
                        <div x-text="m.cuenta.nombre"></div>
                    </div>
                    <div class="d-flex">
                        <div class="w_150px"><b>Total:</b></div>
                        <div x-text="__numberFormat( m.monto )"></div>
                    </div>
                    <div class="d-flex">
                        <div class="w_150px"><b>Descripción:</b></div>
                        <div x-text="m.descripcion"></div>
                    </div>
                </div>
            </template>
        </div>
        <x-slot name="footer">
            <span>
                <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Cerrar</button>
            </span>
        </x-slot>
    </x-modal>


    @include('livewire.movimientos.form-movimiento')

    @script
        <script>
            Alpine.data('movimientos', () => ({
                movimientos: [],
                m: [],
                loading: true,

                init() {
                    this.getMovimientos();
                    $('#cuenta_id').change(() => {
                        val = $('#cuenta_id').val()
                        @this.cuenta_id = val
                    })

                    $('#cuenta_id_filter').change(() => {
                        val = $('#cuenta_id_filter').val()
                        @this.cuenta_id_filter = val
                    })


                    $('#cuenta_destino_id').change(() => {
                        val = $('#cuenta_destino_id').val()
                        @this.cuenta_destino_id = val
                    })

                    $('#tipo_filter').change(() => {
                        val = $('#tipo_filter').val()
                        @this.tipo_filter = val
                    })

                    $('#usuario_id').change(() => {
                        val = $('#usuario_id').val()
                        @this.usuario_id = val
                    })

                    $('#tipo').change(() => {
                        val = $('#tipo').val();
                        @this.tipo = val;

                        // Si el tipo es "transferencia", muestra el campo de cuenta de destino
                        if (val === 'transferencia') {
                            $('#cuenta_destino_id').parent().show();
                        } else {
                            $('#cuenta_destino_id').parent().hide();
                        }
                    });
                },

                async getMovimientos() {
                    this.loading = true;
                    this.movimientos = await @this.getMovimientos();
                    __destroyTable('#table_movimientos')

                    for (const movimiento of this.movimientos) {
                        await this.addMovimientoTable(movimiento);
                    }

                    setTimeout(() => {
                        __resetTable('#table_movimientos');
                        this.loading = false;
                    }, 500);
                },

                async addMovimientoTable(movimiento, is_update = false) {
                    let tr = ``;

                    if (!is_update) {
                        tr += `<tr id="movimiento_${movimiento.id}">`;
                    }

                    // Lógica para determinar si es Compra, Venta o Manual
                    let tipoMovimiento = 'Manual';
                    let isEditable = true; // Variable para determinar si es editable
                    if (movimiento.compra_id) {
                        tipoMovimiento = 'Compra';
                        isEditable = false; // No editable si es una compra
                    } else if (movimiento.venta_id) {
                        tipoMovimiento = 'Venta';
                        isEditable = false; // No editable si es una venta
                    }

                    // Desactivar botón de edición si no es manual
                    let editButton = !movimiento.block ? isEditable ?
                        `<x-buttonsm click="openForm('${movimiento.id}')"><i class="la la-edit"></i></x-buttonsm>` :
                        `<x-buttonsm click="MovimientoAuto()"><i class="la la-edit"></i></x-buttonsm>` : ``;
                    tr += `
                        <td>${ __formatDate( movimiento.fecha ) }</td>
                        <td>${movimiento.cuenta ? movimiento.cuenta.nombre : 'Sin cuenta'}</td>
                        <td>${movimiento.tipo == 'ingreso' ? 'Ingreso' : 'Egreso'}</td>
                        <td>${tipoMovimiento}</td>
                        <td>${__numberFormat( movimiento.monto )}</td>
                        <td>${movimiento.usuario.name}</td>
                        <td>
                            <div class="d-flex">
                                <x-buttonsm click="showMovimiento('${movimiento.id}')"><i class="la la-eye"></i> </x-buttonsm>
                                @can('editar movimientos')
                                ${editButton}
                                @endcan
                                @can('eliminar movimientos')
                                    <template x-if="!movimiento.compra_id && !movimiento.venta_id">
                                                    <x-buttonsm click="deleteMovimiento('${movimiento.id}')">
                                                        <i class="la la-trash"></i>
                                                    </x-buttonsm>
                                </template>
                                @endcan

                            </div>
                        </td>`;

                    if (!is_update) {
                        tr += `</tr>`;
                        $('#body_table_movimientos').prepend(tr);
                    } else {
                        $(`#movimiento_${movimiento.id}`).html(tr);
                    }
                },

                async deleteMovimiento(movimiento_id) {
                    alertClickCallback('Eliminar Movimiento',
                        'Esta acción no se puede deshacer.', 'warning',
                        'Confirmar', 'Cancelar', async () => {
                            const res = await @this.deleteMovimiento(movimiento_id);
                            if (res) {
                                toastRight('success', 'Movimiento eliminado con éxito');
                                this.getMovimientos();
                            } else {
                                toastRight('error',
                                    'Este movimiento no se puede eliminar porque está vinculado a una compra o venta.'
                                );
                            }
                        });
                },

                MovimientoAuto() {
                    alertMessage('Lo sentimos!',
                        'No puede editar este movimiento ya que fue generado de manera automática por el sistema, si necesita editar su valor, debe editar la compra o venta que generó el movimiento',
                        'warning', true)
                },

                async saveFront() {
                    const is_update = @this.movimiento_id ? true :
                        false;
                    const movimiento = await @this.save();

                    if (movimiento) {
                        this.addMovimientoTable(movimiento, is_update);

                        $('#form_movimientos').modal('hide');
                        if (is_update) {
                            for (const key in this.movimientos) {
                                if (this.movimientos[key].id == movimiento.id) {
                                    this.movimientos[key] = movimiento;
                                }
                            }
                            toastRight('success', 'Movimiento actualizado con éxito');
                        } else {
                            this.movimientos.push(movimiento);
                            toastRight('success', 'Movimiento registrado con éxito');
                        }
                    }
                },


                openForm(movimiento_id = null) {
                    let movimiento_edit = this.movimientos.find((movimiento) => movimiento.id == movimiento_id);

                    movimiento_edit = movimiento_edit ?? {};

                    @this.movimiento_id = movimiento_edit ? movimiento_edit.id : null;
                    @this.cuenta_id = movimiento_edit ? movimiento_edit.cuenta_id : null;
                    @this.tipo = movimiento_edit ? movimiento_edit.tipo : null;
                    @this.valor = movimiento_edit ? __numberFormat(movimiento_edit.monto, true) : null;
                    @this.fecha = movimiento_edit ? movimiento_edit.fecha : null;
                    @this.descripcion = movimiento_edit ? movimiento_edit.descripcion : null;
                    @this.cuenta_destino_id = movimiento_edit ? movimiento_edit.cuenta_destino_id : null;
                    @this.adjunto = (movimiento_edit.adjuntos && Array.isArray(movimiento_edit.adjuntos) &&
                            movimiento_edit.adjuntos.length > 0) ?
                        movimiento_edit.adjuntos[0]['ruta'] :
                        null;


                    setTimeout(() => {
                        const cuentaElement = document.getElementById('cuenta_id');
                        if (cuentaElement) {
                            $(cuentaElement).val(@this.cuenta_id).trigger('change');
                        }

                        const cuentaDestinoElement = document.getElementById('cuenta_destino_id');
                        if (cuentaDestinoElement) {
                            $(cuentaDestinoElement).val(@this.cuenta_destino_id).trigger(
                                'cuenta_destino_id');
                        }

                        const tipoElement = document.getElementById('tipo');
                        if (tipoElement) {
                            $(tipoElement).val(@this.tipo).trigger('change');
                        }

                        const fechaElement = document.getElementById('fecha');
                        if (fechaElement) {
                            fechaElement.value = @this.fecha;
                        }
                    }, 300);

                    $('#form_movimientos').modal('show');
                },

                showMovimiento(movimiento_id) {
                    this.m = this.movimientos.find((movimiento) => movimiento.id == movimiento_id)
                    console.log(this.m)
                    $('#movimiento').modal('show')
                }
            }));
        </script>
    @endscript
</div>
