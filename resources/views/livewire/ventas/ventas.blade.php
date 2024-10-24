<div x-data="dataalpine">

    <style>
        .texto h3,
        .texto h4,
        .texto h5 {
            margin: 0; /* Elimina el margen superior e inferior */
            padding: 5px 0; /* Ajusta el padding si es necesario */
        }

        .texto p {
            margin: 2px 0; /* Ajusta el margen para los párrafos */
            font-size: 14px; /* Ajusta el tamaño de fuente si es necesario */
        }
    </style>
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Ventas</h3>
                </div>
            </div>

            <div class="content-body">
                <div class="card">
                    <div x-show="!loading">
                        <x-table id="table" extra="d-none">
                            <tr>
                                <th>Fecha</th>
                                <th>Registrado por</th>
                                <th>Cliente</th>
                                <th>Método de pago</th>
                                <th>Venta al por mayor</th>
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
        <x-slot name="header">
            <x-slot name="title">
                <p>Comprobante de venta</p>
            </x-slot>
        </x-slot>
        <div class="text-center texto">
            <h3>BLACK POOL</h3>
            <p>NIT: 123456789</p>
            <p>Dirección: Chinácota, Norte de Santander</p>
            <p>Teléfono: +57 123 456 789</p>
            <h5>Tipo de Venta: <span x-text="comprobante.venta_mayorista ? 'Mayorista' : 'Detal'"></span></h5>
        </div>

        <div class="row scroll_y p-4">
            <template x-if="(typeof comprobante !== 'undefined')">
                <div class="col-md-12 p-2">
                    <div class="d-flex justify-content-between">
                        <div><strong>Fecha:</strong> <span x-text="comprobante.fecha"></span></div>
                        <div><strong>Venta No.:</strong> <span x-text="comprobante.id"></span></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <div><strong>Cliente:</strong> <span x-text="comprobante.descripcion"></span></div>
                        <div><strong>Vendedor:</strong> <span x-text="`${comprobante.usuario.name} ${comprobante.usuario.last_name}`"></span></div>
                    </div>
                    <hr>

                    <h4>Detalles de los Productos</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Artículo</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, key) in comprobante.det_ventas" :key="key">
                                <tr>
                                    <td x-text="item.producto.nombre"></td>
                                    <td x-text="comprobante.venta_mayorista ? __numberFormat(item.producto.precio_mayorista) : __numberFormat(item.precio_venta)"></td>
                                    <td x-text="item.cant"></td>
                                    <td x-text="comprobante.venta_mayorista ? __numberFormat(item.producto.precio_mayorista * item.cant) : __numberFormat(item.precio_venta * item.cant)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <div class="text-right">
                        <p><strong>Subtotal:</strong> <span x-text="`${__numberFormat(comprobante.monto_total)}`"></span></p>
                        <p><strong>IVA 19%:</strong> <span x-text="`${__numberFormat(comprobante.iva)}`"></span></p>
                        <p><strong>Total:</strong> <span x-text="`${__numberFormat(comprobante.monto_total)}`"></span></p>
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


    <x-modal id="editVentaModal">
        <x-slot name="title">
            Editar Venta
        </x-slot>

        <div class="modal-body">
            <label for="descripcion">Descripción</label>
            <input type="text" wire:model="descripcion" id="descripcion" class="form-control mb-3" />

            <!-- Mostrar si la venta es mayorista o normal -->
            <div class="mb-3">
                <label for="tipoVenta">Tipo de Venta:</label>
                <p id="tipoVenta" class="font-weight-bold">
                    {{ $venta_mayorista ? 'Venta al por mayor' : 'Venta normal' }}
                </p>
            </div>
            <div class="d-flex align-items-center mb-3">
                <div class="flex-grow-1 mr-2">
                    <x-select model="$wire.producto_id" id="producto_id" label="Producto" required="true">
                        <option value="0">Seleccione un producto</option>
                        @foreach ($productosall as $producto)
                            <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div class="mr-2 mt-1" style="width: 80px;">
                    <input type="number" wire:model="cantidad" class="form-control" placeholder="Cantidad" />
                </div>

                <button wire:click="agregarProducto" class="btn btn-primary mt-1"
                    style="min-width: 100px;">Agregar</button>
            </div>

            <label for="productos">Productos</label>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($productos) && is_array($productos))
                        @foreach ($productos as $index => $producto)
                            <tr>
                                <td><input type="text" readonly wire:model="productos.{{ $index }}.nombre"
                                        class="form-control" /></td>
                                <td><input type="number" wire:model="productos.{{ $index }}.cantidad"
                                        class="form-control" /></td>
                                <td>
                                    {{ number_format($producto['precio'], 2) }}
                                </td>
                                <td>
                                    {{ number_format($producto['precio'] * $producto['cantidad'], 2) }}
                                </td>
                                <td>
                                    <button wire:click="eliminarProducto({{ $index }})"
                                        class="btn btn-danger">Eliminar</button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center">No hay productos para mostrar.</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total Venta:</strong></td>
                        <td colspan="2">{{ number_format($montoTotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <label for="cuenta_id">Cuenta</label>
            <select wire:model="cuenta_id" id="cuenta_id" class="form-control mb-3">
                @foreach ($cuentas as $cuenta)
                    <option value="{{ $cuenta->id }}">{{ $cuenta->nombre }}</option>
                @endforeach
            </select>
        </div>


        <x-slot name="footer">
            <button wire:click="updateVenta" class="btn btn-primary">Guardar Cambios</button>
        </x-slot>

    </x-modal>

    @script
        <script>
            document.addEventListener('livewire:init', function() {
                Livewire.on('showToast', (data) => {
                    const toastData = data[0];
                    toastRight(toastData.type, toastData.message);
                });
            });


            Alpine.data('dataalpine', () => ({
                loading: true,
                compras: {},
                comprobante: {},

                init() {
                    this.getTabla()
                    window.addEventListener('openEditModal', () => {
                        $('#editVentaModal').modal('show');
                    });

                    window.addEventListener('closeModal', () => {
                        $('#editVentaModal').modal('hide');
                        this.getTabla();
                        toastRight('success', 'Venta actualizada con éxito.');
                    });

                    $('#producto_id').change(() => {
                        @this.producto_id = $('#producto_id').val()
                    })
                },

                async getTabla() {
                    this.loading = true;
                    this.compras = await @this.getTabla();
                    __destroyTable('#table');
                    this.compras.map(async (i) => {
                        await this.addItem(i);
                    });
                    setTimeout(() => {
                        __resetTable('#table');
                        this.loading = false;
                    }, 500);
                },

                async addItem(i) {
                    console.log(i);

                    const ventaMayorista = i.venta_mayorista ? 'Mayorista' : 'Normal';
                    let tr = `<tr id="tr_${i.id}">
                                <td>${ __formatDateTime( i.fecha ) }</td>
                                <td>${i.usuario.name} ${i.usuario.last_name}</td>
                                <td>${i.descripcion || ''}</td>
                                <td>${i.cuenta.nombre}</td>
                                <td>${ventaMayorista}</td>
                                <td>${__numberFormat( i.monto_total )}</td>
                                <td>
                                    <div class="d-flex">
                                        <x-buttonsm click="showComprobante('${i.id}')"><i class="la la-eye"></i> </x-buttonsm>
                                        @can('editar ventas')
                                            <x-buttonsm click="confirmEdit(${i.id})" color="primary"><i class="la la-edit"></i></x-buttonsm>
                                        @endcan
                                        @can('eliminar ventas')
                                            <x-buttonsm click="confirmDelete('${i.id}')" color="danger"><i class="la la-trash"></i></x-buttonsm>
                                        @endcan
                                    </div>
                                </td>
                            </tr>`;

                    $('#body_table').prepend(tr);
                    return true;
                },

                confirmEdit(id) {
                    @this.editVenta(id);
                },

                async confirmDelete(id) {
                    alertClickCallback('Eliminar Venta',
                        'Al eliminar esta venta, los productos regresarán al inventario y los saldos se ajustarán.',
                        'warning', 'Confirmar', 'Cancelar', async () => {
                            const res = await @this.call('deleteVenta', id);
                            if (res) {
                                $(`#tr_${id}`).addClass('d-none');
                                toastRight('success', 'Venta eliminada con éxito.');
                            } else {
                                toastRight('error', 'Error al eliminar la venta.');
                            }
                        });
                },

                showComprobante(compra_id) {
                    this.comprobante = this.compras.find((i) => i.id == compra_id);
                    $('#comprobante').modal('show');
                }
            }));
        </script>
    @endscript
</div>
