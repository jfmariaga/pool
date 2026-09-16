<x-modal id="form_cartera" size="md">
    <x-slot name="title">
        <span x-show="!$wire.cart_id">Nueva cartera</span>
        <span x-show="$wire.cart_id">Editar cartera</span>
    </x-slot>

    <div class="row">
        <div class="col-md-12 mt-1">
            <x-input model="$wire.cart_nombre" label="Nombre" required="true"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <label>Activo</label>
            <select class="form-control" x-model="$wire.cart_activo">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>
    </div>

    <x-slot name="footer">
        <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-outline-primary" x-on:click="guardarCartera()">Guardar</button>
    </x-slot>
</x-modal>
