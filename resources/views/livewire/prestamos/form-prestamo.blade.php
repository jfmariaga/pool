<x-modal id="form_prestamo">
    <x-slot name="title">
        <span x-show="!$wire.prestamo_id">Nuevo préstamo</span>
        <span x-show="$wire.prestamo_id">Editar préstamo</span>
        <span x-text="tab === 'retroventa' ? ' - Retroventa' : ' - Personal'"></span>
    </x-slot>

    <div class="row">
        <div class="col-md-6 mt-1">
            <x-input model="$wire.cliente_nombre" label="Nombre del cliente" required="true"></x-input>
        </div>
        <div class="col-md-3 mt-1">
            <x-input model="$wire.cliente_cedula" label="Cédula"></x-input>
        </div>
        <div class="col-md-3 mt-1">
            <x-input model="$wire.cliente_telefono" label="Teléfono"></x-input>
        </div>

        <!-- Retroventa -->
        <div class="col-md-6 mt-1" x-show="tab === 'retroventa'">
            <x-select model="$wire.inversionista_id" id="inversionista_id" label="Inversionista" required="true">
                <option value="">----Seleccionar----</option>
                @foreach ($inversionistas as $inv)
                    <option value="{{ $inv->id }}">{{ $inv->nombre }} ({{ number_format($inv->tasa * 100, 2) }}%)</option>
                @endforeach
            </x-select>
        </div>
        <div class="col-md-3 mt-1" x-show="tab === 'retroventa'">
            <x-input model="$wire.num_contrato" label="# Contrato"></x-input>
        </div>
        <div class="col-md-3 mt-1" x-show="tab === 'retroventa'">
            <x-input type="number" model="$wire.peso" label="Peso (gr)"></x-input>
        </div>
        <div class="col-md-12 mt-1" x-show="tab === 'retroventa'">
            <x-textarea model="$wire.descripcion_prenda" label="Descripción de la prenda" rows="2"></x-textarea>
        </div>

        <!-- Personal -->
        <div class="col-md-6 mt-1" x-show="tab === 'personal'">
            <x-select model="$wire.cartera" id="cartera" label="Cartera" required="true">
                <option value="">----Seleccionar----</option>
                @foreach ($carteras as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </x-select>
        </div>

        <div class="col-md-4 mt-1">
            <x-input type="date" model="$wire.fecha_inicio" label="Fecha de inicio" required="true"></x-input>
        </div>
        <div class="col-md-4 mt-1">
            <x-input model="$wire.monto" label="Monto (capital)" required="true" class="mask_decimales"></x-input>
        </div>
        <div class="col-md-4 mt-1">
            <x-input type="number" model="$wire.tasa_interes" label="Tasa mensual %"
                placeholder="Retroventa 7% · Personal 5% (Laura 3%)"></x-input>
        </div>
        <div class="col-md-12 mt-1">
            <x-textarea model="$wire.observacion" label="Observación" rows="2"></x-textarea>
        </div>
        <div class="col-12 mt-1">
            <small class="text-muted">El interés corre sobre el saldo de capital. El plazo de retroventa es de 4 meses y
                se reinicia cada vez que el cliente paga los intereses corridos. El monto y la tasa solo se pueden editar
                mientras el préstamo no tenga pagos.</small>
        </div>
    </div>

    <x-slot name="footer">
        <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal"
            wire:click="resetForm">Cancelar</button>
        <button type="button" class="btn btn-outline-primary" x-on:click="guardarPrestamo()">Guardar</button>
    </x-slot>
</x-modal>
