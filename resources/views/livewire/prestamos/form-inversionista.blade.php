<x-modal id="form_inversionista" size="md">
    <x-slot name="title">
        <span x-show="!$wire.inv_id">Nuevo inversionista</span>
        <span x-show="$wire.inv_id">Editar inversionista</span>
    </x-slot>

    <div class="row">
        <div class="col-md-12 mt-1">
            <x-input model="$wire.inv_nombre" label="Nombre" required="true"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <x-input type="number" model="$wire.inv_tasa" label="Tasa mensual %" required="true"
                placeholder="ej. 5"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <x-input type="number" model="$wire.inv_telefono" label="Teléfono"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <label>Activo</label>
            <select class="form-control" x-model="$wire.inv_activo">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>
    </div>

    <x-slot name="footer">
        <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-outline-primary" x-on:click="guardarInversionista()">Guardar</button>
    </x-slot>
</x-modal>
