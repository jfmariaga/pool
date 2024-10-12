<div class="container  mt-4">
    <h2>Ventas Abiertas</h2>

    <div class="row">
        <div class="mb-3 col-6">
            <input type="text" wire:model="descripcion" placeholder="Descripción de la venta" class="form-control">
            @error('descripcion')
                <span class="text-danger">{{ $message }}</span>
            @enderror

            <button wire:click="abrirNuevaVenta" class="btn btn-primary mt-2">Abrir Nueva Venta</button>
        </div>
    </div>

    <div class="row">
        @foreach ($ventas as $index => $venta)
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">{{ $venta['descripcion'] }}</h5>
                        <p class="card-text">Total venta: ${{ number_format($venta['monto'], 2) }}</p>

                        <button class="btn btn-primary d-flex align-items-center"
                            wire:click="seleccionarVenta({{ $index }})" onclick="openModal()">
                            <i class="fa-solid fa-cart-shopping fa-2x"></i>
                            <span class="badge bg-light text-dark  mr-1 mb-2"
                                style="font-size: 0.75rem;">{{ array_sum(array_column($venta['productos'], 'cantidad')) }}</span>
                        </button>

                        <div class="mt-2">
                            <label for="cuenta">Metodo de pago</label>
                            <select wire:model="ventas.{{ $index }}.cuenta_id" class="form-control mb-2">
                                <option value="">Metodo de pago</option>
                                @foreach ($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}">{{ $cuenta->nombre }}</option>
                                @endforeach
                            </select>
                            @error('ventas.' . $index . '.cuenta_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                            <div class="d-flex justify-content-between">
                                <button onclick="confirmDelete({{ $index }})" class="btn btn-danger">Cancelar
                                    Venta</button>

                                <button onclick="confirmVenta({{ $index }})"
                                    class="btn btn-success me-2">Cerrar Venta</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <x-modal id="modalProductos" wire:ignore.self>
        <x-slot name="title">
            <span>Agregar Productos</span>
        </x-slot>
        <div class="modal-body">
            <div class="row">
                <div class="mb-3 col-8">
                    <label for="producto" class="form-label">Producto</label>
                    <select id="producto" class="form-control" wire:model="producto_id">
                        <option value="">Seleccione un producto</option>
                        @foreach ($productosall as $producto)
                            <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 col-4">
                    <label for="cantidad" class="form-label">Cantidad</label>
                    <input type="number" id="cantidad" wire:model="cantidad" class="form-control">
                </div>
            </div>
        </div>

        <h5>Productos Agregados</h5>
        @if (isset($ventas[$currentVenta]) && !empty($ventas[$currentVenta]['productos']))
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nombre del Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ventas[$currentVenta]['productos'] as $producto)
                        <tr>
                            <td>{{ $producto['nombre'] }}</td>
                            <td>{{ $producto['cantidad'] }}</td>
                            <td>${{ number_format($producto['precio'], 2) }}</td>
                            <td>${{ number_format($producto['precio'] * $producto['cantidad'], 2) }}</td>
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
        @else
            <p>No hay productos agregados a la venta.</p>
        @endif

        <x-slot name="footer">
            <span>
                <button class="btn btn-primary" wire:click="agregarProducto">Agregar a Venta</button>
            </span>
        </x-slot>
    </x-modal>
    <script>
        function openModal() {
            $('#modalProductos').modal('show');
        }

        function confirmDelete(index) {
            alertClickCallback('Cancelar',
                'Se cancelará la venta', 'warning',
                'Confirmar', 'Cancelar', async () => {
                    const res = await @this.cancelarVenta(index)
                    if (res) {
                        toastRight('error', 'Venta cancelada!')
                    }
                })
        }

        function confirmVenta(index) {
            alertClickCallback('Confirmar',
                'Se cerrará la venta', 'success',
                'Confirmar', 'Cancelar', async () => {
                    const res = await @this.cerrarVenta(index)
                    if (res) {
                        toastRight('error', 'Venta cancelada!')
                    }
                })
        }

        document.addEventListener('livewire:init', function() {
            Livewire.on('showToast', (data) => {
                const toastData = data[0];
                toastRight(toastData.type, toastData.message);
            });
        });
    </script>
</div>
