<div x-data="ajusteinventario">

    {{-- <span class="loader_new"></span> --}}

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Ajustes de inventario</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        @can('crear ajuste-inventario')
                            <a href="{{ route('form-ajuste-inventario') }}" id="btn_form_personal" class="btn btn-dark">
                                Nuevo ajuste
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="card">
                    {{-- filtros --}}
                    <div class="row card-body">
                        <div class="col-md-12 mb-1">
                            <b>Filtros</b>
                        </div>
                        <div class="col-md-6 d-flex">
                            <div class="">
                                {{-- <span x-text="$wire.desde"></span> --}}
                                <x-input type="date" model="$wire.desde" id="desde" class="form-control"
                                    label="Desde"></x-input>
                            </div>
                            <div class="ml-2">
                                <x-input type="date" model="$wire.hasta" id="hasta" class="form-control"
                                    label="Hasta"></x-input>
                            </div>
                            <button type="button" x-on:click="getTabla()" class="btn btn-outline-dark ml-2"
                                style="margin-top:19px;">Filtrar</button>
                        </div>
                    </div>

                    <div x-show="!loading">
                        <x-table id="table" extra="d-none">
                            <tr>
                                <th>Fecha</th>
                                <th>Registrado por</th>
                                <th>Ajustes positivos</th>
                                <th>Ajustes negativos</th>
                                <th>Productos ajustados</th>
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

    {{-- comprobante --}}
    <x-modal id="comprobante">
        <x-slot name="title">
            <span>Detalles</span>
        </x-slot>
        <div class="row scroll_y">
            <template x-if="( typeof comprobante.usuario !== 'undefined' )">
                <div class="col-md-12 p-2">
                    <div class="d-flex">
                        <div class="w_150px"><b>Fecha:</b></div>
                        <div x-text="comprobante.fecha"></div>
                    </div>
                    <div class="d-flex">
                        <div class="w_150px"><b>Creada por:</b></div>
                        <div x-text="`${comprobante.usuario.name} ${comprobante.usuario.last_name}`"></div>
                    </div>
                    <div class="d-flex">
                        <div class="w_150px"><b>Ajustes positivos:</b></div>
                        <div x-text="comprobante.cantidades_positivas"></div>
                    </div>
                    <div class="d-flex">
                        <div class="w_150px"><b>Ajustes negativos:</b></div>
                        <div x-text="comprobante.cantidades_negativas"></div>
                    </div>
                    <div class="">
                        <br><br>
                        <b>Lista de productos</b>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Imagen</th>
                                    <th>Producto</th>
                                    <th>Cant sistema</th>
                                    <th>Cant real</th>
                                    <th>Ajuste</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="( item, key ) in comprobante.detalles" :key="key">
                                    <tr>
                                        <td>
                                            <img class="producto_table"
                                                :src="`{{ asset('storage/productos/${ item.producto.imagenes != `` ? item.producto.imagenes  : `default.png` }') }}`"
                                                alt="prod" style="max-height:30px !important;">
                                        </td>
                                        <td x-text="item.producto.nombre"></td>
                                        <td>
                                            <span x-text="item.cant_sistema"></span>
                                        </td>
                                        <td>
                                            <span x-text="item.cant_real"></span>
                                        </td>
                                        <td>
                                            <span x-text="item.cant_ajustada"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
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

    @script
        <script>
            Alpine.data('ajusteinventario', () => ({
                loading: true,
                ajustes: {},
                comprobante: {},

                init() { // se ejecuta cuando ya la aplicación esta lista visualmente
                    this.getTabla()
                    $('#desde').change(() => {
                        @this.desde = $('#proveedor_id').val()
                    })
                    $('#hasta').change(() => {
                        @this.hasta = $('#cuenta_id').val()
                    })
                },

                async getTabla() {

                    this.loading = true

                    this.ajustes = await @this.getTabla() // consultamos

                    // impiamos el contenido de la tabla
                    __destroyTable('#table')

                    this.ajustes.map(async (i) => {
                        const addItem = await this.addItem(i)
                    })

                    setTimeout(() => { // necesario para que no se renderice datatable antes de haber cargado el body
                        __resetTable('#table')
                        this.loading = false
                    }, 500);

                },

                async addItem(i) { // agregamos cada item a la tabla

                    tr = `<tr id="tr_${i.id}">`

                    tr += `
                            <td>${i.fecha}</td>
                            <td>${i.usuario.name} ${i.usuario.last_name}</td>
                            <td>${i.cantidades_positivas}</td>
                            <td>${i.cantidades_negativas}</td>
                            <td>${i.count_productos}</td>
                            <td>
                                <div class="d-flex">
                                    <x-buttonsm click="showComprobante('${i.id}')"><i class="la la-eye"></i> </x-buttonsm>
                                    @can('editar ajuste-inventario')
                                    <x-buttonsm href="form-ajuste-inventario/${i.id}"><i class="la la-edit"></i> </x-buttonsm>
                                    @endcan
                                    @can('eliminar ajuste-inventario')
                                    <x-buttonsm click="confirmDelete('${i.id}')" color="danger"><i class="la la-trash"></i> </x-buttonsm>
                                    @endcan
                                </div>
                            </td>`

                    tr += `</tr>`
                    $('#body_table').prepend(tr)
                    return true;
                },

                confirmDelete(id, puede_eliminar) {
                    console.log(puede_eliminar)
                    if (puede_eliminar === 'true') {
                        alertClickCallback('Eliminar',
                            'La entrada será eliminada por completo, las cantidades ingresadas, serán devueltas',
                            'warning', 'Confirmar', 'Cancelar', async () => {
                                const res = await @this.eliminarCompra(id)
                                if (res) {
                                    $(`#tr_${id}`).addClass('d-none')
                                    toastRight('error', 'Entrada eliminada')
                                }
                            })
                    } else {
                        alertMessage('Lo sentimos!',
                            'No puede eliminar esta venta ya que algunos de sus productos ya fueron vendidos',
                            'error')
                    }
                },

                showComprobante(item_id) {
                    this.comprobante = this.ajustes.find((i) => i.id == item_id)
                    console.log(this.comprobante);
                    $('#comprobante').modal('show')
                }

            }))
        </script>
    @endscript
</div>
