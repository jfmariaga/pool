<x-modal id="form_cuentas" size="sm">
    <x-slot name="title">
        <span x-show="!$wire.cuenta_id">Agregar Cuenta Bancaria</span>
        <span x-show="$wire.cuenta_id">Editar Cuenta Bancaria</span>
    </x-slot>

    <div class="row">
        <!-- Campo para el nombre de la cuenta -->
        <div class="col-md-12 mt-1">
            <x-input model="$wire.nombre" type="text" label="Nombre de la Cuenta" required="true"></x-input>
        </div>

        <!-- Campo para el número de cuenta -->
        <div class="col-md-12 mt-1">
            <x-input model="$wire.numero_de_cuenta" type="text" label="Número de Cuenta" placeholder="Solo números y guiones"></x-input>
        </div>

        <div class="col-md-6 mt-1" x-show="$wire.cuenta_id">
            <x-select model="status" label="Estado"  id="status">
                <option value="">----Seleccionar----</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </x-select>
        </div>
    </div>

    <x-slot name="footer">
        <span>
            <button type="button" class="btn grey btn-outline-secondary" x-on:click="closeModal()" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-outline-primary" x-on:click="saveFront()">Guardar</button>
        </span>
    </x-slot>
</x-modal>
