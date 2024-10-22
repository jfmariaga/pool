<x-modal id="form_roles" size="lg">
    <x-slot name="title">
        <span x-show="!$wire.role_id">Agregar Rol</span>
        <span x-show="$wire.role_id">Editar Rol</span>
    </x-slot>

    <div class="row">
        <div class="col-md-12 mt-1">
            <x-input model="$wire.name" type="text" label="Nombre del Rol" required="true"></x-input>
        </div>

        <div class="col-md-12 mt-3">
            <label for="permisos">Permisos</label>

            <div class="row">
                <div class="col-md-4">
                    <h5 class="text-uppercase text-primary">Dashboard</h5>
                    <div class="form-group">
                        @foreach ($allPermissions->whereIn('name', ['ver dashboard']) as $permission)
                            <div class="form-check">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="form-check-input" id="permiso_{{ $permission->id }}">
                                <label class="form-check-label" for="permiso_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        @endforeach
                    </div>
                    <h5 class="text-uppercase text-primary mt-3">Usuarios</h5>
                    <div class="form-group">
                        @foreach ($allPermissions->whereIn('name', ['ver usuarios', 'crear usuarios', 'editar usuarios', 'eliminar usuarios']) as $permission)
                            <div class="form-check">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="form-check-input" id="permiso_{{ $permission->id }}">
                                <label class="form-check-label" for="permiso_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        @endforeach
                    </div>

                    <h5 class="text-uppercase text-primary mt-3">Categorías</h5>
                    <div class="form-group">
                        @foreach ($allPermissions->whereIn('name', ['ver categorias', 'crear categorias', 'editar categorias', 'eliminar categorias']) as $permission)
                            <div class="form-check">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="form-check-input" id="permiso_{{ $permission->id }}">
                                <label class="form-check-label" for="permiso_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        @endforeach
                    </div>

                    <h5 class="text-uppercase text-primary mt-3">Ajuste de Inventario</h5>
                    <div class="form-group">
                        @foreach ($allPermissions->whereIn('name', ['ver ajuste-inventario', 'crear ajuste-inventario', 'editar ajuste-inventario', 'eliminar ajuste-inventario']) as $permission)
                            <div class="form-check">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="form-check-input" id="permiso_{{ $permission->id }}">
                                <label class="form-check-label" for="permiso_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-md-4">
                    <h5 class="text-uppercase text-primary">Productos</h5>
                    <div class="form-group">
                        @foreach ($allPermissions->whereIn('name', ['ver productos', 'crear productos', 'editar productos', 'eliminar productos']) as $permission)
                            <div class="form-check">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="form-check-input" id="permiso_{{ $permission->id }}">
                                <label class="form-check-label" for="permiso_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        @endforeach
                    </div>

                    <h5 class="text-uppercase text-primary mt-3">Proveedores</h5>
                    <div class="form-group">
                        @foreach ($allPermissions->whereIn('name', ['ver proveedores', 'crear proveedores', 'editar proveedores', 'eliminar proveedores']) as $permission)
                            <div class="form-check">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="form-check-input" id="permiso_{{ $permission->id }}">
                                <label class="form-check-label" for="permiso_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        @endforeach
                    </div>

                    <h5 class="text-uppercase text-primary mt-3">Cuentas</h5>
                    <div class="form-group">
                        @foreach ($allPermissions->whereIn('name', ['ver cuentas', 'crear cuentas', 'editar cuentas', 'eliminar cuentas']) as $permission)
                            <div class="form-check">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="form-check-input" id="permiso_{{ $permission->id }}">
                                <label class="form-check-label" for="permiso_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        @endforeach
                    </div>

                    <h5 class="text-uppercase text-primary mt-3">Movimientos</h5>
                    <div class="form-group">
                        @foreach ($allPermissions->whereIn('name', ['ver movimientos', 'crear movimientos', 'editar movimientos', 'eliminar movimientos']) as $permission)
                            <div class="form-check">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="form-check-input" id="permiso_{{ $permission->id }}">
                                <label class="form-check-label" for="permiso_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-md-4">
                    <h5 class="text-uppercase text-primary">Cierre de Caja</h5>
                    <div class="form-group">
                        @foreach ($allPermissions->whereIn('name', ['ver cierre-caja', 'crear cierre-caja', 'editar cierre-caja', 'eliminar cierre-caja']) as $permission)
                            <div class="form-check">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="form-check-input" id="permiso_{{ $permission->id }}">
                                <label class="form-check-label" for="permiso_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        @endforeach
                    </div>

                    <h5 class="text-uppercase text-primary mt-3">Compras</h5>
                    <div class="form-group">
                        @foreach ($allPermissions->whereIn('name', ['ver compras', 'crear compras', 'editar compras', 'eliminar compras']) as $permission)
                            <div class="form-check">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="form-check-input" id="permiso_{{ $permission->id }}">
                                <label class="form-check-label" for="permiso_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        @endforeach
                    </div>

                    <h5 class="text-uppercase text-primary mt-3">Ventas</h5>
                    <div class="form-group">
                        @foreach ($allPermissions->whereIn('name', ['ver ventas', 'crear ventas', 'editar ventas', 'eliminar ventas']) as $permission)
                            <div class="form-check">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="form-check-input" id="permiso_{{ $permission->id }}">
                                <label class="form-check-label" for="permiso_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        @endforeach
                    </div>

                    <h5 class="text-uppercase text-primary mt-3">Roles</h5>
                    <div class="form-group">
                        @foreach ($allPermissions->whereIn('name', ['ver roles', 'crear roles', 'editar roles', 'eliminar roles']) as $permission)
                            <div class="form-check">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="form-check-input" id="permiso_{{ $permission->id }}">
                                <label class="form-check-label" for="permiso_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <span>
            <button type="button" class="btn grey btn-outline-secondary" x-on:click="closeModal()" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-outline-primary" x-on:click="saveFront()">Guardar</button>
        </span>
    </x-slot>
</x-modal>
