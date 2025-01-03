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
                            </div>
                            <hr>
                        </div>
                        @foreach ($ventas as $index => $venta)
                            <div class="col-md-4 mt-1">
                                <div class="box-shadow-2">
                                    <div class="card mb-0">
                                        <div class="card-body">
                                            {{-- Título y descripción de cada venta --}}
                                            <h5>{{ $venta['descripcion'] }}</h5>

                                            {{-- Selección de cuenta y monto para cada venta --}}
                                            @if (!empty($venta['productos']))
                                                <div x-data="{ isCreditoSelected: false }">
                                                    <!-- Selección de cuenta -->
                                                    <label for="cuenta">Seleccionar Cuenta</label>
                                                    <select wire:model="cuentasSeleccionadasIds.{{ $index }}"
                                                        class="form-control"
                                                        x-on:change="isCreditoSelected = ($event.target.value == 0)">
                                                        <option value="">Seleccione una cuenta</option>
                                                        @foreach ($cuentas as $cuenta)
                                                            @if ($cuenta->id != 0)
                                                                <!-- Omitir la cuenta de crédito si ya existe en la base de datos con ID 0 -->
                                                                <option value="{{ $cuenta->id }}">
                                                                    {{ $cuenta->nombre }}</option>
                                                            @endif
                                                        @endforeach
                                                        <option value="0">Crédito</option>
                                                        <!-- Opción especial para Crédito -->
                                                    </select>

                                                    <!-- Campo para el monto -->
                                                    <label for="montoCuenta">Monto</label>
                                                    <input type="number" wire:model="montosCuentas.{{ $index }}"
                                                        step="0.01" class="form-control"
                                                        placeholder="Ingrese el monto">

                                                    <!-- Selección de usuario solo si "Crédito" está seleccionado -->
                                                    <div x-show="isCreditoSelected" style="margin-top: 15px;">
                                                        <label for="deudor_id">Seleccionar Deudor</label>
                                                        <select wire:model="deudorIds.{{ $index }}"
                                                            class="form-control">
                                                            <option value="">Seleccione un usuario</option>
                                                            @foreach ($usuarios as $usuario)
                                                                <option value="{{ $usuario->id }}">{{ $usuario->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <button class="btn btn-outline-primary btn_small mt-1"
                                                        wire:click="agregarCuenta({{ $index }})">
                                                        Agregar cuenta
                                                    </button>
                                                </div>

                                                {{-- Lista de cuentas asignadas a la venta --}}
                                                <hr>
                                                <h6>Abonos al saldo:</h6>
                                                <ul class="list-group list-group-flush"
                                                    style="background-color: transparent;">
                                                    @foreach ($venta['cuentasSeleccionadas'] as $cuentaIndex => $cuenta)
                                                        <li class="list-group-item d-flex justify-content-between align-items-center"
                                                            style="background-color: transparent; border: none;">
                                                            <span>
                                                                <strong>{{ $cuenta['nombre'] }}:</strong>
                                                                ${{ number_format($cuenta['monto'], 2) }}
                                                            </span>
                                                            <button
                                                                wire:click="eliminarCuentaSeleccionada({{ $index }}, {{ $cuentaIndex }})"
                                                                class="btn btn-outline-danger btn-sm"
                                                                style="font-size: 0.85em;">
                                                                Eliminar
                                                            </button>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            {{-- Configuración de venta mayorista --}}
                                            <hr>
                                            @if (array_sum(array_column($venta['productos'], 'cantidad')))
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                        wire:change="toggleVentaMayorista({{ $index }})"
                                                        class="form-check-input"
                                                        id="venta_mayorista_{{ $index }}"
                                                        @if ($venta['venta_mayorista']) checked @endif
                                                        @if (!empty($venta['cuentasSeleccionadas'])) disabled @endif>
                                                    <label class="form-check-label"
                                                        for="venta_mayorista_{{ $index }}">Venta al por
                                                        mayor</label>
                                                </div>
                                                <hr>
                                            @endif

                                            {{-- Detalles de productos y total de cada venta --}}
                                            <div class="">
                                                <div class="d-flex">
                                                    <div class="w_150px">
                                                        <b>Cant Productos:</b>
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
                                                <div class="d-flex">
                                                    <div class="w_150px">
                                                        <b>Saldo pendiente:</b>
                                                    </div>
                                                    <span>$
                                                        {{ number_format($venta['saldo_pendiente'] ?? $venta['monto'], 2) }}</span>
                                                </div>


                                                {{-- Botón para abrir el carrito de productos --}}
                                                <center>
                                                    <button class="btn btn-success align-items-center btn_small mt-2"
                                                        wire:click="seleccionarVenta({{ $index }})"
                                                        onclick="$('#modalProductos').modal('show');">
                                                        <i class="fa-solid fa-cart-shopping text-white"></i> Carrito de
                                                        compras
                                                    </button>
                                                </center>
                                            </div>

                                            {{-- Botones para cancelar o cerrar la venta --}}
                                            <div class="">
                                                <hr>
                                                <div class="d-flex justify-content-center">
                                                    <button x-on:click="confirmDelete({{ $index }})"
                                                        class="btn btn-outline-danger btn_small">
                                                        Eliminar
                                                    </button>
                                                    <button
                                                        x-on:click="confirmVenta({{ $index }}, {{ $cuentas }})"
                                                        class="btn btn-outline-primary me-2 ml-1 btn_small">
                                                        Guardar
                                                    </button>
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
