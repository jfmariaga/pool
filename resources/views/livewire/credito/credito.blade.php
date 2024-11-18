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

    @include('livewire.credito.form-credito')
    @include('livewire.credito.form-abono')

    @script
        <script>
            Alpine.data('credito', () => ({
                credito: [],
                metodosPago: [],
                m: [],
                loading: true,

                init() {
                    this.getCredito();

                    window.addEventListener('openAbonoModal', () => {
                        $('#form_abono').modal('show')
                        this.cargarMetodosPago(); // Cargar los métodos de pago existentes para la edición
                        // this.montoTotalVenta = parseFloat(@this.montoTotal.toFixed(2));

                        // Cargar cuentas desde Livewire a Alpine
                        this.cuentas = @json($cuentas) || [];
                        if (!Array.isArray(this.cuentas)) {
                            console.error("Error: `cuentas` no es un array.");
                            this.cuentas = [];
                        }

                    });

                    window.addEventListener('closeModal', () => {
                        $('#form_abono').modal('hide');
                        this.getCredito();
                    });

                    window.addEventListener('showToast', (data) => {
                        const toastData = data.detail[0];
                        toastRight(toastData.type, toastData.message);
                    });

                    $('#deudor_id').change(() => {
                        val = $('#deudor_id').val()
                        @this.deudor_id = val
                    })

                    $('#deudor_id_filter').change(() => {
                        val = $('#deudor_id_filter').val()
                        @this.deudor_id_filter = val
                    })

                    $('#responsable_id').change(() => {
                        val = $('#responsable_id').val()
                        @this.responsable_id = val
                    })

                    $('#estado_filter').change(() => {
                        val = $('#estado_filter').val()
                        @this.estado_filter = val
                    })

                    $('#estado').change(() => {
                        val = $('#estado').val();
                        @this.estado = val;
                    });
                },

                async getCredito() {
                    this.loading = true;
                    this.credito = await @this.getCredito();

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
                        tr += `<tr id="credito_${credito.id}">`;
                    }

                    let tipocredito = 'Prestamo';
                    let isEditable = true;
                    console.log(credito);

                    if (credito.venta_id ||credito.des_monto <= 0 || credito.abonos.length > 0) {
                        isEditable = false;
                    }

                    if (credito.venta_id) {
                        tipocredito = 'Venta';
                    }

                    // Desactivar botón de edición si no es manual
                    let editButton = !credito.block ? isEditable ?
                        `<x-buttonsm click="openForm('${credito.id}')"><i class="la la-edit"></i></x-buttonsm>` :
                        `<x-buttonsm click="creditoAuto()"><i class="la la-edit"></i></x-buttonsm>` : ``;

                    const adjuntoRuta = credito.adjuntos && credito.adjuntos.length > 0 ? credito
                        .adjuntos[0].ruta.replace(
                            'public/', 'storage/') : null;
                    tr += `
                        <td>${ __formatDate(credito.fecha)  }</td>
                        <td>${credito.deudor ? credito.deudor.name : 'Sin detalle'}</td>
                        <td>${__numberFormat( credito.monto )}</td>
                        <td>${tipocredito}</td>
                        <td>${credito.responsable ? credito.responsable.name : 'Sin detalle'}</td>
                        <td>${credito.estado}</td>
                        <td>
                            <div class="d-flex">
                                <x-buttonsm click="abono('${credito.id}')"><i class="la la-eye"></i></x-buttonsm>
                                ${editButton}
                                    <template x-if="!credito.compra_id && !credito.venta_id">
                                      <x-buttonsm click="deletecredito('${credito.id}')">
                                         <i class="la la-trash"></i>
                                      </x-buttonsm>
                                    </template>
                                ${
                                        adjuntoRuta ? `
                                                          <a href="${new URL(adjuntoRuta, window.location.origin).href}" target="_blank"  class="btn  btn-sm " style="margin-top:-4px ">
                                                           <i class="la la-paperclip"></i>
                                                          </a>
                                                          ` : ``
                                    }
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
                            const res = await @this.deleteCredito(credito_id);
                            if (res) {
                                toastRight('success', 'credito eliminado con éxito');
                                this.getCredito();
                            } else {
                                toastRight('error',
                                    'Este credito no se puede eliminar porque está vinculado a una venta o tienes abonos.'
                                );
                            }
                        });
                },
                cargarMetodosPago() {
                    this.metodosPago = [];
                    @this.metodosPago.forEach((metodo) => {
                        this.metodosPago.push({
                            cuenta_id: metodo.cuenta_id,
                            nombre: metodo.nombre,
                            monto: parseFloat(metodo.monto) || 0
                        });
                    });
                },

                agregarMetodoPago() {
                    this.metodosPago.push({
                        cuenta_id: '',
                        nombre: '',
                        monto: 0
                    });
                    @this.set('metodosPago', this.metodosPago); // Sincroniza con Livewire
                },

                eliminarMetodoPago(index) {
                    this.metodosPago.splice(index, 1);
                    @this.set('metodosPago', this.metodosPago); // Sincroniza con Livewire cada vez que se elimina
                },

                actualizarNombreCuenta(index) {
                    if (Array.isArray(this.cuentas)) {
                        const cuentaSeleccionada = this.cuentas.find(cuenta => cuenta.id == this.metodosPago[index]
                            .cuenta_id);
                        if (cuentaSeleccionada) {
                            this.metodosPago[index].nombre = cuentaSeleccionada.nombre;
                            this.metodosPago[index].cuenta_id = cuentaSeleccionada
                                .id; // Asegura que el ID también esté correcto
                            console.log("Métodos de pago actualizados:", this.metodosPago);
                            @this.set('metodosPago', this.metodosPago); // Forzamos la actualización de Livewire
                        } else {
                            console.error("Error: Cuenta seleccionada no encontrada.");
                        }
                    } else {
                        console.error("Error: `cuentas` no es un array.");
                    }
                },

                validarYGuardar() {
                    @this.call('validarYGuardar');
                },

                creditoAuto() {
                    alertMessage('Lo sentimos!',
                        'No puede editar este credito ya que fue generado de manera automática por el sistema o tiene abonos, si necesita editar su valor, debe editar la venta que generó el credito',
                        'warning', true)
                },



                openForm(credito_id = null) {
                    let credito_edit = this.credito.find((credito) => credito.id == credito_id);

                    credito_edit = credito_edit ?? {};

                    @this.credito_id = credito_edit ? credito_edit.id : null;
                    @this.deudor_id = credito_edit ? credito_edit.deudor_id : null;
                    @this.tipo = credito_edit ? credito_edit.tipo : null;
                    @this.monto = credito_edit ? __numberFormat(credito_edit.monto, true) : null;
                    @this.fecha = credito_edit ? credito_edit.fecha : null;
                    @this.estado = credito_edit ? credito_edit.estado : null;
                    @this.adjunto = (credito_edit.adjuntos && Array.isArray(credito_edit.adjuntos) &&
                            credito_edit.adjuntos.length > 0) ?
                        credito_edit.adjuntos[0]['ruta'] :
                        null;


                    setTimeout(() => {
                        const deudorElement = document.getElementById('deudor_id');
                        if (deudorElement) {
                            $(deudorElement).val(@this.deudor_id).trigger('change');
                        }
                    }, 300);

                    $('#form_credito').modal('show');
                },

                async saveFront() {
                    const is_update = @this.credito_id ? true : false;
                    const credito = await @this.saveCredito();

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
                    } else {
                        toastRight('error',
                            'No se puede editar el crédito porque ya tiene abonos o pertenece a una venta.');
                    }
                },

                abono(credito_id) {
                    @this.getAbono(credito_id);
                }
            }));
        </script>
    @endscript
</div>
