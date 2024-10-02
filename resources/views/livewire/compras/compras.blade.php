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
                        <a href="{{ route('form-compra') }}" id="btn_form_personal" class="btn btn-dark"> 
                            <i class="la la-plus"></i> Nuevo
                        </a>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="card p-3">
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
                                            <img class="producto_table" :src="`{{ asset('storage/productos/${ item.producto.imagenes != `` ? item.producto.imagenes  : `default.png` }') }}`" alt="prod" style="max-height:30px !important;">
                                        </td>
                                        <td x-text="item.producto.nombre"></td>
                                        <td>
                                            <span x-text="item.stock_compra"></span>
                                        </td>
                                        <td>
                                            <span x-text="__numberFormat( item.precio_compra )"></span>
                                        </td>
                                        <td x-text="__numberFormat( __limpiarNum( item.precio_compra ) * item.stock_compra )"></td>
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
                loading:        true,
                compras:        {},
                comprobante:    {},

                init() { // se ejecuta cuando ya la aplicación esta lista visualmente
                    this.getTabla()
                },

                async getTabla() {

                    this.loading = true

                    this.compras = await @this.getTabla() // consultamos

                    this.compras.map( ( i )=>{
                        this.addUser( i )
                    })

                    setTimeout(() => { // necesario para que no se renderice datatable antes de haber cargado el body
                        resetTable('#table')
                        this.loading = false
                    }, 500);

                },

                async addUser(i) { // agregamos cada item a la tabla

                    tr = `<tr id="tr_${i.id}">`

                    tr += `
                            <td>${i.fecha}</td>
                            <td>${i.usuario.name} ${i.usuario.last_name}</td>
                            <td>${i.proveedor.nombre}</td>
                            <td>${i.cuenta.nombre}</td>
                            <td>${__numberFormat( i.total )}</td>
                            <td>
                                <div class="d-flex">
                                    <x-buttonsm click="showComprobante('${i.id}')"><i class="la la-eye"></i> </x-buttonsm>
                                    <x-buttonsm href="form-compra/${i.id}"><i class="la la-edit"></i> </x-buttonsm>
                                    <x-buttonsm click="confirmDelete('${i.id}', '${i.puede_eliminar}')" color="danger"><i class="la la-trash"></i> </x-buttonsm>
                                </div>
                            </td>`

                    tr += `</tr>`
                    $('#body_table').prepend(tr)

                },

                confirmDelete( id, puede_eliminar ) {
                    console.log( puede_eliminar )
                    if( puede_eliminar === 'true' ){
                        alertClickCallback('Eliminar',
                            'La entrada será eliminada por completo, las cantidades ingresadas, serán devueltas',
                            'warning', 'Confirmar', 'Cancelar', async () => {
                            const res = await @this.eliminarCompra(id)
                            if (res) {
                                $(`#tr_${id}`).addClass('d-none')
                                toastRight('error', 'Entrada eliminada')
                            }
                        })
                    }else{
                        alertMessage('Lo sentimos!', 'No puede eliminar esta venta ya que algunos de sus productos ya fueron vendidos', 'error')
                    }
                },

                showComprobante( compra_id ){
                    this.comprobante = this.compras.find( (i) => i.id == compra_id )
                    console.log( this.comprobante )
                    $('#comprobante').modal( 'show' )
                }

            }))
        </script>
    @endscript
</div>
