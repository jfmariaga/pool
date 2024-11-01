<div x-data="dataalpine">

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Cierres de caja</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        @can('crear cierre-caja')
                            <a href="javascript:" x-on:click="$('#form-cierre').modal('show')" id="btn_form_personal"
                                class="btn btn-dark">
                                Cerrar Caja
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="card">
                    <div x-show="!loading">
                        <x-table id="data_table" extra="d-none">
                            <tr>
                                <th class="d-none">id</th>
                                <th>Fecha</th>
                                <th>Realizado por</th>
                                <th>Total Inicio</th>
                                <th>Total Cierre</th>
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

    {{-- solo lo sacamos para no hacer este tan extenso --}}
    @include('livewire.cierre-caja.form-cierre')
    @include('livewire.cierre-caja.comprobante-cierre')

    @script
        <script>
            Alpine.data('dataalpine', () => ({
                cierres: [],
                comprobante: [],
                loading: true,
                loading_cierre: false,

                init() { // se ejecuta cuando ya la aplicación esta lista visualmente
                    this.getData()
                },

                async getData() {

                    this.loading = true

                    this.cierres = await @this.getData() // consultamos

                    for (const item of this.cierres) {
                        add = await this.addItem(item) // agregamos
                    }

                    setTimeout(() => { // necesario para que no se renderice datatable antes de haber cargado el body
                        __resetTable('#data_table')
                        this.loading = false
                    }, 500);

                },

                async addItem(item, is_update = false) { // agregamos cada item a la tabla

                    tr = ``;

                    if (!is_update) {
                        tr += `<tr id="user_${item.id}">`
                    }

                    tr += `
                            <td class="d-none">${ item.id }</td>
                            <td>${ __formatDateTime( item.fecha ) }</td>
                            <td>${item.usuario.name} ${item.usuario.last_name}</td>
                            <td>${__numberFormat( item.total_inicio )}</td>
                            <td>${__numberFormat( item.total_cierre )}</td>
                            <td>
                                <x-buttonsm click="showComprobante('${item.id}')"><i class="la la-eye"></i> </x-buttonsm>
                            </td>`

                    if (!is_update) {
                        tr += `</tr>`
                        $('#body_data_table').prepend(tr)
                    } else {
                        $(`#user_${item.id}`).html(tr)
                    }

                },

                async saveFront() {

                    alertClickCallback('Cerrar caja',
                        'Acción irreversible, una vez cierre la caja, las Ventas, Compras y Movimientos no serán editables, desea continuar?',
                        'warning', 'Confirmar', 'Cancelar', async () => {

                            this.loading_cierre = true

                            const cierre = await @this.cierreCaja()

                            if (cierre) {
                                this.addItem(cierre)
                                $('#form-cierre').modal('hide')
                                this.cierres.push(cierre)
                                toastRight('success', 'Cierre de caja realizado con éxito!')
                            } else {
                                toastRight('error', 'La consulta falló, por favor cuelva a intentarlo')
                            }

                            this.loading_cierre = false

                        })
                },

                showComprobante(cierre_id) {
                    this.comprobante = {
                        ...this.cierres.find((i) => i.id == cierre_id)
                    }
                    $('#comprobante-cierre').modal('show')
                }

            }))
        </script>
    @endscript
</div>
