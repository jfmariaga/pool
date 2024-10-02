<x-modal id="form_movimientos">
    <x-slot name="title">
        <!-- Título dinámico: Agregar o Editar Movimiento -->
        <span x-show="!$wire.movimiento_id">Agregar Movimiento</span>
        <span x-show="$wire.movimiento_id">Editar Movimiento</span>
    </x-slot>

    <div class="row">
        <!-- Campo Cuenta -->
        <div class="col-md-6 mt-1">
            <x-select model="$wire.cuenta_id" label="Cuenta" required="true" id="cuenta_id">
                <option value="">----Seleccionar----</option>
                @foreach ($cuentas as $cuenta)
                    <option value="{{ $cuenta->id }}">{{ $cuenta->nombre }}</option>
                @endforeach
            </x-select>
        </div>

        <!-- Campo Tipo de Movimiento -->
        <div class="col-md-6 mt-1">
            <x-select model="$wire.tipo" label="Tipo de Movimiento" required="true" id="tipo">
                <option value="">----Seleccionar----</option>
                <option value="ingreso">Ingreso</option>
                <option value="egreso">Egreso</option>
            </x-select>
        </div>

        <!-- Campo Valor -->
        <div class="col-md-6 mt-1">
            <x-input model="$wire.valor" label="Valor" required="true" dataMask="$ #.##0,00"></x-input>
        </div>

        <!-- Campo Fecha -->
        <div class="col-md-6 mt-1">
            <x-input model="$wire.fecha" id="fecha" label="Fecha" type="date" required="true"></x-input>
        </div>
    </div>

    <!-- Footer con botones de acción -->
    <x-slot name="footer">
        <span>
            <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal"
                wire:click="resetForm">Cancelar</button>
            <button type="button" class="btn btn-outline-primary" x-on:click="saveFront()">Guardar</button>
        </span>
    </x-slot>
</x-modal>
