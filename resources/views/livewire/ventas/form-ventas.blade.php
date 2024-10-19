<div x-data="dataalpine">

    <div class="app-content content">
        <div class="content-wrapper">

            <div class="content-header row">
                <div class="content-header-left col-md-6 col-8 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">
                        <span>Gestor de ventas</span>
                    </h3>
                </div>
            </div>

            <div class="content-body">
                <div class="card p-3">
                    <div class="row">
                        <div class="col-12 ">
                            <b>Abrir nueva venta</b>
                            <div class="row mt-1">
                                <div class="col-md-5 d-flex w-100">
                                    <div class="w-100">
                                        <input type="text" wire:model="descripcion"
                                            placeholder="Descripción de la venta" class="form-control">
                                        @error('descripcion')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <button wire:click="abrirNuevaVenta" class="btn btn-outline-dark ml-2"
                                        style="height:40px;">Abrir Venta</button>
                                </div>
                                {{-- <div class="col-md-4">
                                    <x-select model="$wire.producto_id" id="producto_id" label="Producto" required="true">
                                        <option value="0">Seleccione un producto</option>
                                        @foreach ($productosall as $producto)
                                            <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                                        @endforeach
                                    </x-select>
                                </div> --}}
                            </div>
                            <hr>
                        </div>
                        @foreach ($ventas as $index => $venta)
                            <div class="col-md-4 mt-1">
                                <div class="box-shadow-2">
                                    <div class="card mb-0">
                                        <div class="card-body">

                                            <h5 class="card-title">{{ $venta['descripcion'] }}</h5>
                                            <br>
                                            <div class="">
                                                <label for="cuenta">Metodo de pago</label>
                                                <select wire:model="ventas.{{ $index }}.cuenta_id"
                                                    class="form-control mb-2">
                                                    <option value="">Metodo de pago</option>
                                                    @foreach ($cuentas as $cuenta)
                                                        <option value="{{ $cuenta->id }}">{{ $cuenta->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('ventas.' . $index . '.cuenta_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <hr>
                                            @if (array_sum(array_column($venta['productos'], 'cantidad')))
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                        wire:change="toggleVentaMayorista({{ $index }})"
                                                        class="form-check-input"
                                                        id="venta_mayorista_{{ $index }}"
                                                        @if ($venta['venta_mayorista']) checked @endif>
                                                    <label class="form-check-label"
                                                        for="venta_mayorista_{{ $index }}">Venta al por
                                                        mayor</label>
                                                </div>
                                                <hr>
                                            @endif
                                            <div class="">
                                                <div class="d-flex">
                                                    <div class="w_150px">
                                                        <b>Cant Productos :</b>
                                                    </div>
                                                    <div>{{ array_sum(array_column($venta['productos'], 'cantidad')) }}
                                                    </div>
                                                </div>
                                                <div class="d-flex">
                                                    <div class="w_150px">
                                                        <b>Total venta:</b>
                                                    </div>
                                                    <span>$ {{ number_format($venta['monto'], 2) }}</span>
                                                </div>

                                                <center>
                                                    <button class="btn btn-success align-items-center btn_small mt-2"
                                                        wire:click="seleccionarVenta({{ $index }})"
                                                        onclick="$('#modalProductos').modal('show');">
                                                        <i class="fa-solid fa-cart-shopping text-white"></i> Carrito de
                                                        compras
                                                    </button>
                                                </center>

                                            </div>

                                            <div class="">
                                                <hr>

                                                <div class="d-flex justify-content-center">
                                                    <button x-on:click="confirmDelete({{ $index }})"
                                                        class="btn btn-outline-danger btn_small">Cancelar
                                                        Venta</button>

                                                    <button
                                                        x-on:click="confirmVenta({{ $index }}, {{ $cuentas }})"
                                                        class="btn btn-outline-primary me-2 ml-1 btn_small">Cerrar
                                                        Venta</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal id="modalProductos" wire:ignore.self>

        <x-slot name="title">
            @if (isset($ventas[$currentVenta]['descripcion']))
                <span>Carrito: {{ $ventas[$currentVenta]['descripcion'] }}</span>
            @else
                <span>Carrito de compras</span>
            @endif
        </x-slot>

        <div class="modal-body">
            <div class="row">
                <div class="col-7">
                    <x-select model="$wire.producto_id" id="producto_id" label="Producto" required="true">
                        <option value="0">Seleccione un producto</option>
                        @foreach ($productosall as $producto)
                            <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div class="col-3">
                    <label for="cantidad" class="form-label">Cantidad</label>
                    <input type="number" id="cantidad" wire:model="cantidad" class="form-control">
                </div>
                <div class="col-md-2 mt-2">
                    <button class="btn btn-primary" wire:click="agregarProducto">Agregar</button>
                </div>
            </div>
        </div>

        @if (isset($ventas[$currentVenta]) && !empty($ventas[$currentVenta]['productos']))
            {{-- {{ json_encode( $ventas[$currentVenta] ) }} --}}
            <div class="scroll_y scroll_x" style="height:50vh;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cant</th>
                            <th>Precio</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ventas[$currentVenta]['productos'] as $key => $producto)
                            <tr>
                                <td>{{ $producto['nombre'] }}</td>
                                <td>{{ $producto['cantidad'] }}</td>
                                <td>${{ number_format($producto['precio'], 2) }}</td>
                                <td>${{ number_format($producto['precio'] * ($producto['cantidad'] > 0 ? $producto['cantidad'] : 0), 2) }}
                                </td>
                                <td><a x-on:click="alertClickCallback('Eliminar Producto', '¿Estás seguro de eliminar este producto?', 'warning', 'Confirmar', 'Cancelar', async () => { await $wire.eliminarProducto({{ $currentVenta }}, {{ $key }}) })"
                                        style="cursor:pointer;">
                                        <i class="fa fa-times"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total Venta:</strong></td>
                            <td>${{ number_format($ventas[$currentVenta]['monto'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <center>
                <div class="mt-2">No se han agregado productos...</div>
            </center>
        @endif

        <x-slot name="footer">
            <button type="button" class="btn grey btn-outline-dark" data-dismiss="modal">Cancelar</button>
        </x-slot>
    </x-modal>

    @script
        <script>
            Livewire.on('showToast', (data) => {
                const toastData = data[0];
                toastRight(toastData.type, toastData.message);
            });

            Alpine.data('dataalpine', () => ({

                list_productos: [], // original
                filter_productos: [], // cambia con los filtros
                buscar: '',

                init() { // se ejecuta cuando ya la aplicación esta lista visualmente

                    this.getProductos()

                    $('#categoria_id').change(() => {
                        val = $('#categoria_id').val()
                        this.filtrarProductos(val)
                    })
                    $('#producto_id').change(() => {
                        @this.producto_id = $('#producto_id').val()
                    })

                },

                // consulta los productos y los almacena de manera local
                async getProductos() {
                    this.list_productos = await @this.getProductos()
                    this.filter_productos = [...this.list_productos]
                },

                // filtra los porductos
                filtrarProductos(categoria_id = null, filtrar = null) {

                    if (categoria_id) { // filtramos por categoria

                        if (categoria_id == 0) {
                            this.filter_productos = [...this.list_productos]
                        } else {
                            this.filter_productos = [...this.list_productos.filter((i) => i.categoria_id ==
                                categoria_id)]
                        }

                    } else if (this.buscar && this.buscar.length > 2) { // filtramos por el buscador

                        const buscar = __eliminarAcentos(this.buscar).toLowerCase()
                        this.filter_productos = [...this.list_productos.filter((i) => __eliminarAcentos(i.nombre
                            .toLowerCase()).includes(buscar))]

                    } else {
                        this.filter_productos = [...this.list_productos]
                    }
                },

                // agregar un producto al carrito
                agregarProducto(producto_id) {
                    @this.agregarProducto(producto_id)
                },

                // confirmar eliminar venta
                confirmDelete(index) {
                    alertClickCallback('Cancelar',
                        'Se cancelará la venta', 'warning',
                        'Confirmar', 'Cancelar', async () => {
                            const res = await @this.cancelarVenta(index)
                            if (res) {
                                toastRight('error', 'Venta cancelada!')
                            }
                        })
                },

                // confirmar registrar venta
                confirmVenta(index) {
                    alertClickCallback('Confirmar Venta',
                        'Se cerrará la venta', 'warning',
                        'Confirmar', 'Cancelar', async () => {
                            const res = await @this.cerrarVenta(index)
                            if (res) {
                                toastRight('success', 'Venta finalizada!')
                            }
                        })
                },

            }))
        </script>
    @endscript
</div>
