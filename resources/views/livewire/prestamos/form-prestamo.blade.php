<x-modal id="form_prestamo">
    <x-slot name="title">
        <span x-show="!$wire.prestamo_id">Nuevo préstamo</span>
        <span x-show="$wire.prestamo_id">Editar préstamo</span>
        <span x-text="tab === 'retroventa' ? ' - Retroventa' : ' - Personal'"></span>
    </x-slot>

    <div class="row">
        <div class="col-md-3 mt-1">
            <x-input type="number" model="$wire.cliente_cedula" label="Cédula" id="cliente_cedula"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <x-input model="$wire.cliente_nombre" label="Nombre del cliente" required="true"></x-input>
        </div>
        <div class="col-md-3 mt-1">
            <x-input type="number" model="$wire.cliente_telefono" label="Teléfono"></x-input>
        </div>
        <div class="col-12 mt-1" x-show="$wire.cliente_encontrado">
            <small class="text-info"><i class="la la-info-circle"></i> Cliente existente: se usarán el nombre y
                teléfono ya registrados para esta cédula.</small>
        </div>

        <!-- Retroventa -->
        <div class="col-md-6 mt-1" x-show="tab === 'retroventa'">
            <x-select model="$wire.inversionista_id" id="inversionista_id" label="Inversionista" required="true">
                <option value="">----Seleccionar----</option>
                @foreach ($inversionistas as $inv)
                    <option value="{{ $inv->id }}" data-tasa="{{ $inv->tasa * 100 }}">{{ $inv->nombre }} ({{ number_format($inv->tasa * 100, 2) }}%)</option>
                @endforeach
            </x-select>
        </div>
        <div class="col-md-3 mt-1" x-show="tab === 'retroventa'">
            <x-input model="$wire.num_contrato" label="# Contrato"></x-input>
        </div>
        <div class="col-md-3 mt-1" x-show="tab === 'retroventa'">
            <x-input type="number" model="$wire.peso" label="Peso (gr)"></x-input>
        </div>
        <div class="col-md-4 mt-1" x-show="tab === 'retroventa'">
            <x-input type="number" model="$wire.tasa_interes_inversionista" label="Tasa inversionista %"
                required="true"></x-input>
        </div>
        <div class="col-md-4 mt-1" x-show="tab === 'retroventa'">
            <x-input type="number" model="$wire.tasa_interes_casa" label="Tasa casa %" required="true"></x-input>
        </div>
        <div class="col-md-4 mt-1" x-show="tab === 'retroventa'">
            <label class="d-block">Total cliente %</label>
            <input type="text" class="form-control" disabled
                :value="((parseFloat($wire.tasa_interes_inversionista) || 0) + (parseFloat($wire.tasa_interes_casa) || 0)).toFixed(2) + ' %'">
        </div>
        <div class="col-md-12 mt-1" x-show="tab === 'retroventa'">
            <x-textarea model="$wire.descripcion_prenda" label="Descripción de la prenda" rows="2"></x-textarea>
        </div>
        <div class="col-md-12 mt-2" x-show="tab === 'retroventa'">
            <label>Foto de la prenda</label>
            <div class="contenedor-img" onclick="$('#img-prenda').click()">
                <span x-show="!$wire.foto_prenda" class="text-white">Cargar imagen</span>
                <img :src="$wire.foto_prenda" x-show="$wire.foto_prenda">
            </div>
            <input type="file" x-on:change="getImgPrenda()" id="img-prenda" class="form-control d-none" accept="image/*">
        </div>

        <!-- Personal -->
        <div class="col-md-6 mt-1" x-show="tab === 'personal'">
            <x-select model="$wire.cartera_id" id="cartera_id" label="Cartera" required="true">
                <option value="">----Seleccionar----</option>
                @foreach ($carteras as $c)
                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                @endforeach
            </x-select>
        </div>
        <div class="col-md-3 mt-1" x-show="tab === 'personal'">
            <x-input type="number" model="$wire.tasa_interes_cartera" label="Tasa cartera %" required="true"></x-input>
        </div>
        <div class="col-md-3 mt-1" x-show="tab === 'personal'">
            <x-input type="number" model="$wire.tasa_interes_casa" label="Tasa casa %" required="true"></x-input>
        </div>
        <div class="col-md-4 mt-1" x-show="tab === 'personal'">
            <label class="d-block">Total cliente %</label>
            <input type="text" class="form-control" disabled
                :value="((parseFloat($wire.tasa_interes_cartera) || 0) + (parseFloat($wire.tasa_interes_casa) || 0)).toFixed(2) + ' %'">
        </div>

        <div class="col-md-4 mt-1">
            <x-input type="date" model="$wire.fecha_inicio" label="Fecha de inicio" required="true"></x-input>
        </div>
        <div class="col-md-4 mt-1">
            <x-input model="$wire.monto" label="Monto (capital)" required="true" class="mask_decimales"></x-input>
        </div>
        <div class="col-md-12 mt-1">
            <x-textarea model="$wire.observacion" label="Observación" rows="2"></x-textarea>
        </div>
        <div class="col-12 mt-1" x-show="tab === 'retroventa'">
            <small class="text-muted">El interés corre sobre el saldo de capital. El plazo de retroventa es de 4 meses y
                se reinicia cada vez que el cliente paga los intereses corridos. La tasa que paga el cliente es
                siempre la suma de la tasa del inversionista y la tasa de la casa. El monto y las tasas solo se
                pueden editar mientras el préstamo no tenga pagos.</small>
        </div>
        <div class="col-12 mt-1" x-show="tab === 'personal'">
            <small class="text-muted">El interés corre sobre el saldo de capital. La tasa que paga el cliente es
                siempre la suma de la tasa de la cartera y la tasa de la casa. El monto y las tasas solo se pueden
                editar mientras el préstamo no tenga pagos.</small>
        </div>
    </div>

    <x-slot name="footer">
        <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal"
            wire:click="resetForm">Cancelar</button>
        <button type="button" class="btn btn-outline-primary" x-on:click="guardarPrestamo()">Guardar</button>
    </x-slot>
</x-modal>
