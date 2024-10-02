<div x-data="movimientos">

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Movimientos</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        <a href="javascript:" x-on:click="openForm()" id="btn_form_movimiento" class="btn btn-dark"> <i
                                class="la la-plus"></i> Nuevo Movimiento</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="card">
                    <div x-show="!loading">
                        <x-table id="table_movimientos" extra="d-none">
                            <tr>
                                <th>Cuenta</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
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

    @include('livewire.movimientos.form-movimiento')

    @script
        <script>
            Alpine.data('movimientos', () => ({
                movimientos: [],
                loading: true,

                init() {
                    this.getMovimientos();
                    $('#cuenta_id').change(() => {
                        val = $('#cuenta_id').val()
                        @this.cuenta_id = val
                    })

                    $('#tipo').change(() => {
                        val = $('#tipo').val()
                        @this.tipo = val
                    })
                },

                async getMovimientos() {
                    this.loading = true;
                    this.movimientos = await @this.getMovimientos(); // Consultamos movimientos desde Livewire
                    console.log(this.movimientos);

                    for (const movimiento of this.movimientos) {
                        await this.addMovimientoTable(movimiento); // Agregamos los movimientos a la tabla
                    }

                    setTimeout(() => {
                        resetTable('#table_movimientos');
                        this.loading = false;
                    }, 500);
                },

                async addMovimientoTable(movimiento, is_update = false) {
                    let tr = ``;

                    if (!is_update) {
                        tr += `<tr id="movimiento_${movimiento.id}">`;
                    }

                    let formatter = new Intl.NumberFormat('es-CO', {
                        style: 'currency',
                        currency: 'COP',
                        minimumFractionDigits: 0
                    });

                    let valorFormateado = formatter.format(movimiento.monto);
                    tr += `
                        <td>${movimiento.cuenta ? movimiento.cuenta.nombre : 'Sin cuenta'}</td>
                        <td>${movimiento.tipo == 'ingreso' ? 'Ingreso' : 'Egreso'}</td>
                        <td>${valorFormateado}</td>
                        <td>${movimiento.usuario.user_name}</td>
                        <td>${movimiento.fecha}</td>
                        <td>
                            <div class="d-flex">
                                <x-buttonsm click="openForm('${movimiento.id}')"><i class="la la-edit"></i></x-buttonsm>
                            </div>
                        </td>`;

                    if (!is_update) {
                        tr += `</tr>`;
                        $('#body_table_movimientos').prepend(tr);
                    } else {
                        $(`#movimiento_${movimiento.id}`).html(tr);
                    }
                },

                async saveFront() {
                    const is_update = @this.movimiento_id ? true :
                        false; // Verifica si estamos actualizando un movimiento
                    const movimiento = await @this.save(); // Guardamos movimiento desde Livewire

                    if (movimiento) {
                        this.addMovimientoTable(movimiento, is_update); // Agrega el movimiento a la tabla

                        $('#form_movimientos').modal('hide'); // Cierra el modal
                        if (is_update) {
                            for (const key in this.movimientos) {
                                if (this.movimientos[key].id == movimiento.id) {
                                    this.movimientos[key] = movimiento; // Actualiza el movimiento en la lista
                                }
                            }
                            toastRight('success', 'Movimiento actualizado con éxito'); // Mensaje de éxito
                        } else {
                            this.movimientos.push(movimiento); // Agrega el nuevo movimiento a la lista
                            toastRight('success', 'Movimiento registrado con éxito'); // Mensaje de éxito
                        }
                    }
                },


                openForm(movimiento_id = null) {
                    let movimiento_edit = this.movimientos.find((movimiento) => movimiento.id == movimiento_id);
                    movimiento_edit = movimiento_edit ?? {};

                    @this.movimiento_id = movimiento_edit ? movimiento_edit.id : null;
                    @this.cuenta_id = movimiento_edit ? movimiento_edit.cuenta_id : null;
                    @this.tipo = movimiento_edit ? movimiento_edit.tipo : null; // Puede ser 'ingreso' o 'egreso'
                    @this.valor = movimiento_edit ? __numberFormat(movimiento_edit.monto) : null;
                    @this.fecha = movimiento_edit ? movimiento_edit.fecha : null;


                    // @this.descripcion = movimiento_edit ? movimiento_edit.descripcion : null;

                    setTimeout(() => {
                        // Actualiza el select de cuenta_id
                        const cuentaElement = document.getElementById('cuenta_id');
                        if (cuentaElement) {
                            $(cuentaElement).val(@this.cuenta_id).trigger('change');
                        }

                        // Actualiza el select de tipo (ingreso/egreso)
                        const tipoElement = document.getElementById('tipo');
                        if (tipoElement) {
                            $(tipoElement).val(@this.tipo).trigger('change');
                        }

                        // Si tienes campos de fecha que usan un datepicker, actualízalo también
                        const fechaElement = document.getElementById('fecha');
                        if (fechaElement) {
                            fechaElement.value = @this.fecha;
                        }
                    }, 300);

                    $('#form_movimientos').modal('show');
                },


                // confirmDelete(movimiento_id) {
                //     alertClickCallback('Eliminar Movimiento',
                //         'Esta acción no se puede deshacer.', 'warning',
                //         'Confirmar', 'Cancelar', async () => {
                //             const res = await @this.deleteMovimiento(movimiento_id);
                //             toastRight('success', 'Movimiento eliminado con éxito');
                //             this.getMovimientos();
                //         });
                // },
            }));
        </script>
    @endscript
</div>
