<div x-data="compras">

    {{-- <span class="loader_new"></span> --}}

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Compras</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        @can('crear compras')
                            <a href="{{ route('form-compra') }}" id="btn_form_personal" class="btn btn-dark">
                                <i class="la la-plus"></i> Nuevo
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
                        <div class="col-md-3">
                            <x-select model="$wire.proveedor_id" id="proveedor_id" label="Filtrar por proveedor">
                                <option value="0">Todos...</option>
                                @foreach ($proveedores as $i)
                                    <option value="{{ $i['id'] }}">{{ $i['nombre'] }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-3">
                            <x-select model="$wire.cuenta_id" id="cuenta_id" label="Filtrar por cuenta">
                                <option value="0">Todas...</option>
                                @foreach ($cuentas as $i)
                                    <option value="{{ $i['id'] }}">{{ $i['nombre'] }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-1">
                            <button type="button" x-on:click="getTabla()" class="btn btn-outline-dark"
                                style="margin-top:19px;">Filtrar</button>
                        </div>
                    </div>

                    <div x-show="!loading">
                        <x-table id="table" extra="d-none">
                            <tr>
                                <th>Fecha</th>
                                <th>Registrado por</th>
                                <th>Proveedor</th>
                                <th>Método de pago</th>
                                <th>Total</th>
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

    {{-- comprobante de la compra --}}
    <x-modal id="comprobante">
        <x-slot name="title">
            <span>Comprobante de la compra</span>
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
                        <div class="w_150px"><b>Proveedor:</b></div>
                        <div x-text="comprobante.proveedor.nombre"></div>
                    </div>
                    <div class="d-flex">
                        <div class="w_150px"><b>Método de pago:</b></div>
                        <div x-text="comprobante.cuenta.nombre"></div>
                    </div>
                    <div class="d-flex">
                        <div class="w_150px"><b>Total:</b></div>
                        <div x-text="__numberFormat( comprobante.total )"></div>
                    </div>
                    <div class="">
                        <br><br>
                        <b>Lista de productos</b>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Imagen</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio de compra</th>
                                    <th>SubTotal</th>
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
                                            <span x-text="item.stock_compra"></span>
                                        </td>
                                        <td>
                                            <span x-text="__numberFormat( item.precio_compra )"></span>
                                        </td>
                                        <td
                                             x-text="__numberFormat( item.precio_compra  * item.stock_compra )">
                                             
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
            Alpine.data('compras', () => ({
                loading: true,
                compras: {},
                comprobante: {},

                init() { // se ejecuta cuando ya la aplicación esta lista visualmente
                    this.getTabla()
                    $('#proveedor_id').change(() => {
                        @this.proveedor_id = $('#proveedor_id').val()
                    })
                    $('#cuenta_id').change(() => {
                        @this.cuenta_id = $('#cuenta_id').val()
                    })
                    $('#desde').change(() => {
                        @this.desde = $('#desde').val()
                    })
                    $('#hasta').change(() => {
                        @this.hasta = $('#hasta').val()
                    })
                },

                async getTabla() {

                    this.loading = true

                    this.compras = await @this.getTabla() // consultamos

                    // impiamos el contenido de la tabla
                    __destroyTable('#table')

                    this.compras.map(async (i) => {
                        const addUser = await this.addUser(i)
                    })

                    setTimeout(() => { // necesario para que no se renderice datatable antes de haber cargado el body
                        __resetTable('#table')
                        this.loading = false
                    }, 500);
                },

                async addUser(i) { // agregamos cada item a la tabla
                    const adjuntoRuta = i.adjuntos && i.adjuntos.length > 0 ? i.adjuntos[0].ruta.replace(
                        'public/', 'storage/') : null;
                    tr = `<tr id="tr_${i.id}">`

                    tr += `
                            <td>${ __formatDate( i.fecha ) }</td>
                            <td>${i.usuario.name} ${i.usuario.last_name}</td>
                            <td>${i.proveedor.nombre}</td>
                            <td>${i.cuenta.nombre}</td>
                            <td>${__numberFormat( i.total )}</td>
                            <td>
                                <div class="d-flex">
                                    <x-buttonsm click="showComprobante('${i.id}')"><i class="la la-eye"></i></x-buttonsm>
                                    ${
                                        i.block ? `` :
                                        `
                                                                        @can('editar compras') <!-- Verificación de permiso -->
                                                                            <x-buttonsm href="form-compra/${i.id}"><i class="la la-edit"></i></x-buttonsm>
                                                                        @endcan
                                                                        @can('eliminar compras') <!-- Verificación de permiso -->
                                                                            <x-buttonsm click="confirmDelete('${i.id}', '${i.puede_eliminar}')" color="danger"><i class="la la-trash"></i></x-buttonsm>
                                                                        @endcan
                                                                        `
                                    }
                                    ${
                                        adjuntoRuta ? `
                                                <a href="${new URL(adjuntoRuta, window.location.origin).href}" target="_blank"  class="btn  btn-sm " style="margin-top:-4px ">
                                                    <i class="la la-paperclip"></i>
                                                </a>
                                                ` : ``
                                    }

                                </div>
                            </td>`;

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

                showComprobante(compra_id) {
                    this.comprobante = this.compras.find((i) => i.id == compra_id)
                    $('#comprobante').modal('show')
                }

            }))
        </script>
    @endscript
</div>
