<div x-data="cuentas">

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Cuentas Efectivo/Bancarias</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        @can('crear cuentas')
                            <a href="javascript:" x-on:click="openForm()" id="btn_form_cuenta" class="btn btn-dark">
                                <i class="la la-plus"></i> Nuevo
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="card">
                    <div x-show="!loading">
                        <x-table id="table_cuentas" extra="d-none">
                            <tr>
                                <th>Nombre</th>
                                <th>Número de Cuenta</th>
                                <th>Saldo</th>
                                <th>Estado</th>
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

    @include('livewire.cuentas.form-cuenta')

    @script
        <script>
            Alpine.data('cuentas', () => ({
                cuentas: [],
                loading: true,

                init() {
                    this.getCuentas()
                    $('#status').change(() => {
                        val = $('#status').val()
                        console.log(val);

                        @this.status = val
                    })
                },

                async getCuentas() {
                    this.loading = true

                    this.cuentas = await @this.getCuentas()

                    for (const cuenta of this.cuentas) {
                        add = await this.addCuentaTable(cuenta)
                    }

                    setTimeout(() => {
                        __resetTable('#table_cuentas')
                        this.loading = false
                    }, 500);
                },

                async addCuentaTable(cuenta, is_update = false) {
                    tr = ``;

                    if (!is_update) {
                        tr += `<tr id="cuenta_${cuenta.id}">`
                    }

                    let formatter = new Intl.NumberFormat('es-CO', {
                        style: 'currency',
                        currency: 'COP',
                        minimumFractionDigits: 0
                    });

                    let valorFormateado = formatter.format(cuenta.saldo);

                    tr += `<td>${cuenta.nombre}</td>
                        <td>${ cuenta.numero_de_cuenta ? cuenta.numero_de_cuenta : '' }</td>
                        <td>${ valorFormateado }</td>
                        <td>${ cuenta.status == 1 ?  '<span style="color: green;">✔</span>' : '<span style="color: red;">✘</span>' }</td>
                        <td>
                            <div class="d-flex">
                                @can('editar cuentas')
                                <x-buttonsm click="openForm(${cuenta.id})"><i class="la la-edit"></i> </x-buttonsm>
                                @endcan
                                @can('eliminar cuentas')
                                <x-buttonsm click="confirmDelete(${cuenta.id})" color="danger"><i class="la la-trash"></i> </x-buttonsm>
                                @endcan
                            </div>
                        </td>`

                    if (!is_update) {
                        tr += `</tr>`
                        $('#body_table_cuentas').prepend(tr)
                    } else {
                        $(`#cuenta_${cuenta.id}`).html(tr)
                    }
                },

                async saveFront() {
                    const is_update = @this.cuenta_id ? true : false;

                    const cuenta = await @this.save()
                    if (cuenta) {
                        this.addCuentaTable(cuenta, is_update)
                        $('#form_cuentas').modal('hide')
                        if (is_update) {
                            for (const key in this.cuentas) {
                                if (this.cuentas[key].id == cuenta.id) {
                                    this.cuentas[key] = cuenta;
                                }
                            }
                            toastRight('success', 'Cuenta actualizada con éxito');
                        } else {
                            this.cuentas.push(cuenta);
                            toastRight('success', 'Cuenta registrada con éxito');
                        }
                    }
                },

                openForm(cuenta_id = null) {
                    let cuenta_edit = this.cuentas.find((cuenta) => cuenta.id == cuenta_id);
                    cuenta_edit = cuenta_edit ?? {}

                    @this.cuenta_id = cuenta_edit ? cuenta_edit.id : null
                    @this.nombre = cuenta_edit ? cuenta_edit.nombre : null
                    @this.numero_de_cuenta = cuenta_edit ? cuenta_edit.numero_de_cuenta : null
                    @this.status = cuenta_edit ? cuenta_edit.status : null
                    $('#form_cuentas').modal('show')

                    setTimeout(() => {
                            const statusElement = document.getElementById('status');
                            if (statusElement) {
                                $(statusElement).val(@this.status).trigger('change');
                            }
                        },
                        300
                    );
                },

                confirmDelete(cuenta_id) {
                    alertClickCallback('Eliminar',
                        'La cuenta no se eliminará por completo pasará a estar inactiva en el sistema',
                        'warning',
                        'Confirmar', 'Cancelar', async () => {
                            const res = await @this.inactivarCuenta(cuenta_id)
                            if (res) {
                                const is_update = true
                                this.addCuentaTable(res, is_update)
                                if (is_update) {
                                    for (const key in this.cuentas) {
                                        if (this.cuentas[key].id == res.id) {
                                            this.cuentas[key] = res;
                                        }
                                    }
                                    toastRight('error', 'Cuenta inactiva!')
                                }
                            }

                        })
                },
            }))
        </script>
    @endscript
</div>
