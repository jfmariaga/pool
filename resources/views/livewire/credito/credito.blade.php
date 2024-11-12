<div x-data="credito">

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Credito</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        {{-- @can('crear credito') --}}
                            <a href="javascript:" x-on:click="openForm()" id="btn_form_credito" class="btn btn-dark"> <i
                                    class="la la-plus"></i> Nuevo Credito</a>
                        {{-- @endcan --}}
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
                            <x-select model="$wire.estado_filter" id="estado_filter" label="Filtrar por estado">
                                <option value="0">Todos...</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="pago">Pago</option>
                            </x-select>
                        </div>
                        <div class="col-md-3">
                            <x-select model="$wire.deudor_id_filter" id="deudor_id_filter" label="Filtrar por deudor">
                                <option value="0">Todos...</option>
                                @foreach ($usuarios as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-2">
                            <x-select model="$wire.responsable_id" id="responsable_id" label="Filtrar por responsable">
                                <option value="0">Todos...</option>
                                @foreach ($usuarios as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-1">
                            <button type="button" x-on:click="getCredito()" class="btn btn-outline-dark"
                                style="margin-top:19px;">Filtrar</button>
                        </div>
                    </div>
                    <div x-show="!loading">
                        <x-table id="table_credito" extra="d-none">
                            <tr>
                                <th>Fecha</th>
                                <th>Deudor</th>
                                <th>Monto</th>
                                <th>Tipo</th>
                                <th>Responsable</th>
                                <th>Estado</th>
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

    {{-- <x-modal id="credito" size="md">
        <x-slot name="title">
            <span>Comprobante del credito</span>
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
                        <div class="w_150px"><b>credito:</b></div>
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
    </x-modal> --}}


    @include('livewire.credito.form-credito')

    @script
        <script>
            Alpine.data('credito', () => ({
                credito: [],
                m: [],
                loading: true,

                init() {
                    this.getCredito();
                    $('#deudor_id').change(() => {
                        val = $('#deudor_id').val()
                        @this.deudor_id = val
                    })

                    $('#responsable_id').change(() => {
                        val = $('#responsable_id').val()
                        @this.responsable_id = val
                    })

                    $('#estado_filter').change(() => {
                        val = $('#estado_filter').val()
                        @this.estado_filter = val
                    })

                    $('#deudor_id_filter').change(() => {
                        val = $('#deudor_id_filter').val()
                        @this.deudor_id_filter = val
                    })

                    $('#estado').change(() => {
                        val = $('#estado').val();
                        @this.estado = val;
                    });
                },

                async getCredito() {
                    this.loading = true;
                    this.credito = await @this.getCredito();
                    console.log(this.credito);

                    __destroyTable('#table_credito')

                    for (const credito of this.credito) {
                        await this.addcreditoTable(credito);
                    }

                    setTimeout(() => {
                        __resetTable('#table_credito');
                        this.loading = false;
                    }, 500);
                },

                async addcreditoTable(credito, is_update = false) {
                    let tr = ``;

                    if (!is_update) {
                        tr += `<tr id="credito${credito.id}">`;
                    }

                    // Lógica para determinar si es Compra, Venta o Manual
                    let tipocredito = 'Prestamo';
                    let isEditable = true; // Variable para determinar si es editable
                    if (credito.venta_id) {
                        tipocredito = 'Venta';
                        isEditable = false; // No editable si es una compra
                    }

                    // Desactivar botón de edición si no es manual
                    let editButton = !credito.block ? isEditable ?
                        `<x-buttonsm click="openForm('${credito.id}')"><i class="la la-edit"></i></x-buttonsm>` :
                        `<x-buttonsm click="creditoAuto()"><i class="la la-edit"></i></x-buttonsm>` : ``;
                    const adjuntoRuta = credito.adjuntos && credito.adjuntos.length > 0 ? credito
                        .adjuntos[0].ruta.replace(
                            'public/', 'storage/') : null;
                    tr += `
                        <td>${ __formatDate( credito.fecha ) }</td>
                        <td>${credito.deudor ? credito.deudor.name : 'Sin detalle'}</td>
                        <td>${__numberFormat( credito.monto )}</td>
                        <td>${tipocredito}</td>
                        <td>${credito.responsable ? credito.responsable.name : 'Sin detalle'}</td>
                        <td>${credito.estado}</td>
                        <td>
                            <div class="d-flex">
                                <x-buttonsm click="showcredito('${credito.id}')"><i class="la la-eye"></i> </x-buttonsm>
                                ${editButton}
                                    <template x-if="!credito.compra_id && !credito.venta_id">
                                                    <x-buttonsm click="deletecredito('${credito.id}')">
                                                        <i class="la la-trash"></i>
                                                    </x-buttonsm>
                                </template>
                            </div>
                        </td>`;

                    if (!is_update) {
                        tr += `</tr>`;
                        $('#body_table_credito').prepend(tr);
                    } else {
                        $(`#credito_${credito.id}`).html(tr);
                    }
                },

                async deletecredito(credito_id) {
                    alertClickCallback('Eliminar credito',
                        'Esta acción no se puede deshacer.', 'warning',
                        'Confirmar', 'Cancelar', async () => {
                            const res = await @this.deletecredito(credito_id);
                            if (res) {
                                toastRight('success', 'credito eliminado con éxito');
                                this.getCredito();
                            } else {
                                toastRight('error',
                                    'Este credito no se puede eliminar porque está vinculado a una compra o venta.'
                                );
                            }
                        });
                },

                creditoAuto() {
                    alertMessage('Lo sentimos!',
                        'No puede editar este credito ya que fue generado de manera automática por el sistema, si necesita editar su valor, debe editar la compra o venta que generó el credito',
                        'warning', true)
                },

                async saveFront() {
                    const is_update = @this.credito_id ? true :
                        false;
                    const credito = await @this.save();

                    if (credito) {
                        this.addcreditoTable(credito, is_update);

                        $('#form_credito').modal('hide');
                        if (is_update) {
                            for (const key in this.credito) {
                                if (this.credito[key].id == credito.id) {
                                    this.credito[key] = credito;
                                }
                            }
                            toastRight('success', 'credito actualizado con éxito');
                        } else {
                            this.credito.push(credito);
                            toastRight('success', 'credito registrado con éxito');
                        }
                    }
                },


                openForm(credito_id = null) {
                    let credito_edit = this.credito.find((credito) => credito.id == credito_id);

                    credito_edit = credito_edit ?? {};

                    @this.credito_id = credito_edit ? credito_edit.id : null;
                    @this.cuenta_id = credito_edit ? credito_edit.cuenta_id : null;
                    @this.tipo = credito_edit ? credito_edit.tipo : null;
                    @this.valor = credito_edit ? __numberFormat(credito_edit.monto, true) : null;
                    @this.fecha = credito_edit ? credito_edit.fecha : null;
                    @this.descripcion = credito_edit ? credito_edit.descripcion : null;
                    @this.adjunto = (credito_edit.adjuntos && Array.isArray(credito_edit.adjuntos) &&
                            credito_edit.adjuntos.length > 0) ?
                        credito_edit.adjuntos[0]['ruta'] :
                        null;


                    setTimeout(() => {
                        const cuentaElement = document.getElementById('cuenta_id');
                        if (cuentaElement) {
                            $(cuentaElement).val(@this.cuenta_id).trigger('change');
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

                    $('#form_credito').modal('show');
                },

                showcredito(credito_id) {
                    this.m = this.credito.find((credito) => credito.id == credito_id)
                    console.log(this.m)
                    $('#credito').modal('show')
                }
            }));
        </script>
    @endscript
</div>
