<x-modal id="form_socios">
    <x-slot name="title">
        <span x-show="!$wire.socio_id">Agregar socio</span>
        <span x-show="$wire.socio_id">Editar socio</span>
    </x-slot>
    <div class="row">
        <div class="col-md-6 mt-1">
            <x-input model="$wire.documento" label="NIT o Cédula"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <x-input model="$wire.nombre" label="Nombre" required="true"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <x-input model="$wire.direccion" label="Direccion"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <x-input model="$wire.telefono" type="number" label="Teléfono"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <x-input model="$wire.correo" type="email" label="Correo"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <x-input model="$wire.contacto" label="Contacto"></x-input>
        </div>
        <div class="col-md-6 mt-1" x-show="$wire.socio_id">
            <x-select model="status" label="Estado" id="status">
                <option value="">----Seleccionar----</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </x-select>
        </div>
    </div>

    <x-slot name="footer">
        <span>
            <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-outline-primary" x-on:click="saveFront()">Guardar</button>
        </span>
    </x-slot>
</x-modal>
