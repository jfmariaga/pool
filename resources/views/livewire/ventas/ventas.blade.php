<div x-data="dataalpine">

    {{-- <span class="loader_new"></span> --}}
    <style>
        .invoice-modal {
            font-family: Arial, sans-serif;
            background-color: #fff;
            color: #000;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }

        .invoice-modal h3,
        .invoice-modal h4 {
            margin: 0;
            padding: 5px 0;
            text-align: center;
        }

        .invoice-modal p {
            margin: 5px 0;
            font-size: 14px;
        }

        .invoice-modal table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-modal table th,
        .invoice-modal table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .invoice-modal .text-right {
            text-align: right;
            padding-right: 10px;
        }

        .invoice-modal .text-center {
            text-align: center;
        }

        .invoice-modal hr {
            border: 1px solid #ddd;
            margin: 10px 0;
        }

        .invoice-modal .footer {
            padding: 20px;
            text-align: center;
        }

        .text-center {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            width: 100%;
        }

        .text-center h3,
        .text-center h4,
        .text-center p {
            margin: 5px 0;
        }
    </style>
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Ventas</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        <a href="{{ route('form-ventas') }}" id="btn_form_personal" class="btn btn-dark">
                            <i class="la la-plus"></i> Nueva
                        </a>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="card">
                    {{-- filtros --}}
                    {{-- <div class="row card-body">
                        <div class="col-md-12 mb-1">
                            <b>Filtros</b>
                        </div>
                        <div class="col-md-4 d-flex">
                            <div class="">
                                <x-input type="date" model="$wire.desde" id="desde" class="form-control" label="Desde"></x-input>
                            </div>
                            <div class="ml-2">
                                <x-input type="date" model="$wire.hasta" id="hasta" class="form-control" label="Hasta"></x-input>
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
                            <button type="button" x-on:click="getTabla()" class="btn btn-outline-dark" style="margin-top:19px;">Filtrar</button>
                        </div>
                    </div> --}}

                    <div x-show="!loading">
                        <x-table id="table" extra="d-none">
                            <tr>
                                <th>Fecha</th>
                                <th>Registrado por</th>
                                <th>Cliente</th>
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

    <x-modal id="comprobante" class="invoice-modal">
        <x-slot name="title">
            <!-- Encabezado del establecimiento -->
            <div class="text-center">
                <h3>BLACK POOL</h3>
                <p>NIT: 123456789</p>
                <p>Dirección: Chinácota, Norte de Santander</p>
                <p>Teléfono: +57 123 456 789</p>
                <h4>FACTURA DE VENTA - RÉGIMEN COMÚN</h4>
            </div>
        </x-slot>

        <div class="row scroll_y p-4">
            <template x-if="(typeof comprobante !== 'undefined')">
                <div class="col-md-12 p-2">
                    <div class="d-flex justify-content-between">
                        <div><strong>Fecha:</strong> <span x-text="comprobante.fecha"></span></div>
                        <div><strong>Factura No.:</strong> <span x-text="comprobante.id"></span></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <div><strong>Cliente:</strong> <span x-text="comprobante.descripcion"></span></div>
                        <div><strong>Vendedor:</strong> <span
                                x-text="`${comprobante.usuario.name} ${comprobante.usuario.last_name}`"></span></div>
                    </div>
                    <hr>

                    <h4>Detalles de los Productos</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Artículo</th>
                                <th>Precio</th>
                                <th>Cant.</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, key) in comprobante.det_ventas" :key="key">
                                <tr>
                                    <td x-text="item.producto.nombre"></td>
                                    <td x-text="`${__numberFormat(item.precio_venta)}`"></td>
                                    <td x-text="item.cant"></td>
                                    <td x-text="`${__numberFormat(item.precio_venta * item.cant)}`"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <div class="text-right">
                        <p><strong>Subtotal:</strong> <span
                                x-text="`${__numberFormat(comprobante.monto_total)}`"></span></p>
                        <p><strong>IVA 19%:</strong> <span x-text="`${__numberFormat(comprobante.iva)}`"></span></p>
                        <p><strong>Total:</strong> <span x-text="`${__numberFormat(comprobante.monto_total)}`"></span>
                        </p>
                        <p><strong>Tipo de Pago:</strong> <span x-text="comprobante.cuenta.nombre"></span></p>
                    </div>
                </div>
            </template>
        </div>

        <x-slot name="footer">
            <div class="text-center">
                <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </x-slot>
    </x-modal>






    @script
        <script>
            Alpine.data('dataalpine', () => ({
                loading: true,
                compras: {},
                comprobante: {},

                init() { // se ejecuta cuando ya la aplicación esta lista visualmente
                    this.getTabla()
                    // $('#proveedor_id').change( ()=>{
                    //     @this.proveedor_id = $('#proveedor_id').val()
                    // })
                    // $('#cuenta_id').change( ()=>{
                    //     @this.cuenta_id = $('#cuenta_id').val()
                    // })
                    // $('#desde').change( ()=>{
                    //     @this.desde = $('#proveedor_id').val()
                    // })
                    // $('#hasta').change( ()=>{
                    //     @this.hasta = $('#cuenta_id').val()
                    // })
                },

                async getTabla() {

                    this.loading = true

                    this.compras = await @this.getTabla() // consultamos

                    // impiamos el contenido de la tabla
                    __destroyTable('#table')

                    this.compras.map(async (i) => {
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
                            <td>${ __formatDateTime( i.fecha ) }</td>
                            <td>${i.usuario.name} ${i.usuario.last_name}</td>
                            <td>${i.descripcion ? i.descripcion: ''}</td>
                            <td>${i.cuenta.nombre}</td>
                            <td>${__numberFormat( i.monto_total )}</td>
                            <td>
                                <div class="d-flex">
                                    <x-buttonsm click="showComprobante('${i.id}')"><i class="la la-eye"></i> </x-buttonsm>
                                    ${
                                        i.block
                                        ? ``
                                        : `<!-- <x-buttonsm href="form-compra/${i.id}"><i class="la la-edit"></i> </x-buttonsm> -->
                                            <x-buttonsm click="confirmDelete('${i.id}')" color="danger"><i class="la la-trash"></i> </x-buttonsm>`
                                    }
                                   
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

                showComprobante(compra_id) {
                    this.comprobante = this.compras.find((i) => i.id == compra_id)
                    console.log(this.comprobante);

                    $('#comprobante').modal('show')
                }
            }))
        </script>
    @endscript
</div>
