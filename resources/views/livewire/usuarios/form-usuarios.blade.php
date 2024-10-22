<x-modal id="form_usuarios">
    <x-slot name="title">
        <span x-show="!$wire.user_id">Agregar usuario</span>
        <span x-show="$wire.user_id">Editar usuario</span>
    </x-slot>

    <div class="row">
        <div class="col-md-6 mt-1">
            <x-input model="$wire.document" type="number" label="Documento" required="true"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <x-input model="$wire.name" label="Nombre" required="true"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <x-input model="$wire.last_name" label="Apellido" required="true"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <x-input model="$wire.user_name" label="Username" required="true"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <x-input type="password" model="$wire.password" label="Contraseña" required="true"
                placeholder="********"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <x-input model="$wire.phone" type="number" label="Teléfono"></x-input>
        </div>
        <div class="col-md-6 mt-1">
            <x-input model="$wire.email" label="Correo"></x-input>
        </div>

        <div class="col-md-6 mt-1">
            <x-select model="$wire.role_id" label="Rol Asignado" required="true" id="role">
                <option value="">----Seleccionar----</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </x-select>
        </div>

        <div class="col-md-6 mt-1" x-show="$wire.user_id">
            <x-select model="$wire.status" label="Estado" id="status">
                <option value="">----Seleccionar----</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </x-select>
        </div>

        <div class="col-md-6 mt-1">
            <div class="media mb-2">
                <a class="mr-2" href="#">
                    <img :src="$wire.picture" alt="users avatar" class="users-avatar-shadow rounded-circle"
                        height="64" width="64" style="object-fit: cover;">
                </a>
                <div class="media-body">
                    <label for="">Avatar </label>
                    <div class="col-12 px-0 d-flex">
                        <a href="javascript:" onclick="$('#picture').click()"
                            class="btn btn-sm btn-primary mr-25">seleccionar</a>
                    </div>
                </div>
            </div>
            <input type="file" class="custom-file-input d-none" id="picture" accept="image/*" @change="getImg">
            @error('picture')
                <span class="c_red">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <x-slot name="footer">
        <span>
            <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-outline-primary" x-on:click="saveFront()">Guardar</button>
        </span>
    </x-slot>
</x-modal>
