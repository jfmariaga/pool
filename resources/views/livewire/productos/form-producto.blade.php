<x-modal id="form_productos">
    <x-slot name="title">
        <span x-show="!$wire.producto_id">Agregar producto</span>
        <span x-show="$wire.producto_id">Editar producto</span>
    </x-slot>
    <div class="row">
        <!-- Campo Nombre del Producto -->
        <div class="col-md-6 mt-1">
            <x-input model="$wire.nombre" label="Nombre del Producto" required="true"></x-input>
        </div>

        <!-- Campo Categoría -->
        <div class="col-md-6 mt-1" >
            <x-select model="$wire.categoria_id" label="Categoría" required="true" id="categoria_id">
                <option value="">----Seleccionar----</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}">
                        {{ $categoria->nombre }}
                    </option>
                @endforeach
            </x-select>
        </div>

        <!-- Precio base -->
        <div class="col-md-6 mt-1">
            <x-input model="$wire.precio_base" label="Precio Venta" dataMask="$ #.##0,00" required="true"></x-input>
        </div>

        <!-- Precio Mayorista (opcional) -->
        <div class="col-md-6 mt-1">
            <x-input model="$wire.precio_mayorista" dataMask="$ #.##0,00" label="Precio Mayorista"></x-input>
        </div>

        <!-- Descripción -->
        <div class="col-md-12 mt-1">
            <x-textarea model="$wire.descripcion" label="Descripción"></x-textarea>
        </div>

        <!-- Estado: Disponible -->
        <div class="col-md-6 mt-1">
            <x-checkbox model="$wire.disponible" label="Disponible" required="true"></x-checkbox>
        </div>

        <!-- Stock infinito -->
        <div class="col-md-6 mt-1">
            <x-checkbox model="$wire.stock_infinito" label="Stock Infinito"></x-checkbox>
        </div>

        <!-- Subir imagen -->
        <div class="col-md-12 mt-2">
            <label>Imágenes (formatos .JPG o PNG)</label>
            <div class="contenedor-img" onclick="$('#img-producto').click()">
                <span x-show="!$wire.imagen" class="text-white">Cargar imagen</span>
                <img :src="$wire.imagen" x-show="$wire.imagen">
            </div>
            <input type="file" x-on:change="getImg()" id="img-producto" class="form-control d-none"  accept="image/*">
            @error('imagen')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Footer con los botones -->
    <x-slot name="footer">
        <span>
            <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal"
                wire:click="resetForm">Cancelar</button>
            <button type="button" class="btn btn-outline-primary" x-on:click="saveFront()">Guardar</button>
        </span>
    </x-slot>
</x-modal>
