<div x-data="proveedores">

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Proveedores</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        <a href="javascript:" x-on:click="openForm()" id="btn_form_proveedores" class="btn btn-dark"> <i
                                class="la la-plus"></i> Nuevo</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="card">
                    <div x-show="!loading">
                        <x-table id="table_proveedores" extra="d-none">
                            <tr>
                                <th>NIT/CC</th>
                                <th>Nombre </th>
                                <th>Dirección</th>
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

    @include('livewire.proveedores.form-proveedores')


    @script
        <script>
            Alpine.data('proveedores', () => ({
                proveedores: [],
                loading: true,

                init() { // se ejecuta cuando ya la aplicación esta lista visualmente
                    this.getProveedores()
                    $('#status').change(() => {
                        val = $('#status').val()
                        console.log(val);

                        @this.status = val
                    })
                },

                async getProveedores() {

                    this.loading = true

                    this.proveedores = await @this.getProveedores() // consultamos

                    for (const proveedor of this.proveedores) {
                        add = await this.addproveedorTable(proveedor) // agregamos
                    }



                    setTimeout(() => { // necesario para que no se renderice datatable antes de haber cargado el body
                        resetTable('#table_proveedores')
                        this.loading = false
                    }, 500);

                },

                async addproveedorTable(proveedor, is_update = false) { // agregamos cada proveedor a la tabla

                    tr = ``;

                    if (!is_update) {
                        tr += `<tr id="proveedor_${proveedor.id}">`
                    }

                    tr += `<td>${proveedor.nit}</td>
                        <td>${proveedor.nombre }</td>
                        <td>${ proveedor.direccion ? proveedor.direccion :''  }</td>
                        <td>${ proveedor.telefono ? proveedor.telefono :''  }</td>
                        <td>${ proveedor.email }</td>
                        <td>${ proveedor.contacto ? proveedor.contacto :''  }</td>
                        <td>${ proveedor.status == 1 ?  '<span style="color: green;">✔</span>' : '<span style="color: red;">✘</span>'}</td>
                        <td>
                            <div class="d-flex">
                                <x-buttonsm click="openForm(${proveedor.id})"><i class="la la-edit"></i> </x-buttonsm>
                                <x-buttonsm click="confirmDelete(${proveedor.id})" color="danger"><i class="la la-trash"></i> </x-buttonsm>
                            </div>
                        </td>`

                    if (!is_update) {
                        tr += `</tr>`
                        $('#body_table_proveedores').prepend(tr)
                    } else {
                        $(`#proveedor_${proveedor.id}`).html(tr)
                    }

                },

                async saveFront() {
                    const is_update = @this.proveedor_id ? true : false;

                    const proveedor = await @this.save()
                    if (proveedor) {
                        this.addproveedorTable(proveedor, is_update)
                        $('#form_proveedores').modal('hide')
                        if (is_update) {
                            for (const key in this.proveedores) {
                                if (this.proveedores[key].id == proveedor.id) {
                                    this.proveedores[key] = proveedor;
                                }
                            }
                            toastRight('success', 'Proveedor actualizado con éxito');
                        } else {
                            this.proveedores.push(proveedor);
                            toastRight('success', 'Proveedor registrado con éxito');
                        }
                    }
                },

                openForm(proveedor_id = null) {
                    let proveedor_edit = this.proveedores.find((proveedor) => proveedor.id == proveedor_id);
                    proveedor_edit = proveedor_edit ?? {} // sino encunetra resultado declaramos el obteto vacío

                    @this.proveedor_id = proveedor_id ? proveedor_edit.id : null
                    console.log(@this.proveedor_id);

                    @this.nit = proveedor_id ? proveedor_edit.nit : null
                    @this.nombre = proveedor_id ? proveedor_edit.nombre : null
                    @this.direccion = proveedor_id ? proveedor_edit.direccion : null
                    @this.telefono = proveedor_id ? proveedor_edit.telefono : null
                    @this.correo = proveedor_id ? proveedor_edit.email : null
                    @this.contacto = proveedor_id ? proveedor_edit.contacto : null
                    @this.status = proveedor_id ? proveedor_edit.status : 1
                    $('#form_proveedores').modal('show')

                    setTimeout(() => {
                            // Asegúrate de que el elemento con id 'categoria_id' existe antes de llamar a trigger
                            const statusElement = document.getElementById('status');
                            if (statusElement) {
                                $(statusElement).val(@this.status).trigger('change');
                            }
                        },
                        300
                    );
                },

                confirmDelete(proveedor_id) {
                    alertClickCallback('Inactivar',
                        'No podras realizar entradas con este proveedor', 'warning',
                        'Confirmar', 'Cancelar', async () => {
                            const res = await @this.deleteProveedor(proveedor_id)

                            if (res) {
                                const is_update = true
                                this.addproveedorTable(res, is_update)
                                if (is_update) {
                                    for (const key in this.cuentas) {
                                        if (this.cuentas[key].id == res.id) {
                                            this.cuentas[key] = res;
                                        }
                                    }
                                    toastRight('error', 'Proveedor inactivo!')
                                }
                            }
                        })
                },
            }))
        </script>
    @endscript
</div>
