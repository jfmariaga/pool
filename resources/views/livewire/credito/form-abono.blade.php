<x-modal id="form_abono">
    <x-slot name="title">
        <span>Abonos</span>
    </x-slot>

    <div class="row">
        <div class="col-md-4">
            <div><strong>Credito No:</strong> <span>{{ $credito_id }}</span></div>
        </div>
        <div class="col-md-4">
            <div><strong>Fecha:</strong> <span>{{ $fecha }}</span></div>
        </div>
        <div class="col-md-4">
            <div><strong>A nombre de:</strong> <span>{{ $deudor }}</span></div>
        </div>
        <div class="col-md-4">
            <div><strong>Valor del credito:</strong> <span>${{ number_format($monto, 2) }}</span></div>
        </div>
        <div class="col-md-4">
            <div><strong>Saldo:</strong> <span>${{ number_format($des_monto, 2) }}</span></div>
        </div>
        <div class="col-md-4">
            <div><strong>Responsable:</strong> <span>{{ $responsable }}</span></div>
        </div>
        <hr>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h4>Historial de abonos</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Abono</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($abonos as $abono)
                        <tr>
                            <td>{{ number_format($abono->monto) }}</td>
                            <td>{{ $abono->fecha }}</td>
                            <td class="d-none">{{ $totalAbonos += $abono->monto }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <th>Total: {{ number_format( $totalAbonos)  }}</th>
                </tfoot>
            </table>
        </div>
    </div>
    @if ($des_monto > 0)
        <div class="row">
            <div class="col-md-12">
                <h4>Realizar abonos</h4>
                <div id="metodosPagoContainer">
                    <template x-for="(metodo, index) in metodosPago" :key="index">
                        <div class="d-flex align-items-center mb-2">
                            <select class="form-control mr-2" x-model="metodo.cuenta_id"
                                @change="actualizarNombreCuenta(index)">
                                <option value="">Seleccione una cuenta</option>
                                @foreach ($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}">{{ $cuenta->nombre }}</option>
                                @endforeach
                            </select>
                            <input type="number" class="form-control mr-2" placeholder="Monto" x-model="metodo.monto"
                                @input="$dispatch('metodo-monto-actualizado', { index: index, monto: metodo.monto })" />
                            <button type="button" class="btn btn-outline-danger btn_small"
                                @click="eliminarMetodoPago(index)">Eliminar</button>
                        </div>
                    </template>
                    <div class="d-flex justify-content-between mt-2">
                        <button type="button" class="btn btn-outline-primary btn_small "
                            @click="agregarMetodoPago">Agregar
                            Método de Pago</button>
                        <button @click="validarYGuardar" class="btn btn-outline-primary btn_small">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif


    <!-- Footer con botones de acción -->
    <x-slot name="footer">
        <span>
            {{-- <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal"
                wire:click="resetForm">Cancelar</button>
            <button type="button" class="btn btn-outline-primary" x-on:click="saveFront()">Guardar</button> --}}
        </span>
    </x-slot>
</x-modal>
