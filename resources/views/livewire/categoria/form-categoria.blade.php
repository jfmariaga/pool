<x-modal id="form_categorias" size="sm">
    <x-slot name="title">
        <span x-show="!$wire.categoria_id">Agregar categoría</span>
        <span x-show="$wire.categoria_id">Editar categoría</span>
    </x-slot>
    <div class="row">
        <div class="col-md-12 mt-1">
            <x-input model="$wire.nombre" type="text" label="Nombre" required="true"></x-input>
        </div>
        <div class="col-md-12 mt-1" x-show="$wire.categoria_id">
            <x-select model="status" label="Estado"  id="status">
                <option value="">----Seleccionar----</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </x-select>
        </div>
        <div class="col-md-12 mt-1">
            <x-textarea model="$wire.descripcion" label="Decripción"  placeholder="Decripción (Opcional)"></x-textarea>
        </div>
    </div>

    <x-slot name="footer">
        <span>
            <button type="button" class="btn grey btn-outline-secondary" x-on:click="closeModal()" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-outline-primary" x-on:click="saveFront()">Guardar</button>
        </span>
    </x-slot>
</x-modal>
