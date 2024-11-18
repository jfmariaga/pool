<div x-data="roles">
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Gestión de Roles</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        @can('crear roles')
                            <a href="javascript:" x-on:click="openForm()" id="btn_form_role" class="btn btn-dark">
                                <i class="la la-plus"></i> Nuevo Rol
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="card">
                    <div x-show="!loading">
                        <x-table id="table_roles" extra="d-none">
                            <tr>
                                <th>Nombre del Rol</th>
                                <th>Permisos</th>
                                <th>Acciones</th>
                            </tr>
                        </x-table>
                    </div>
                    <div x-show="loading">
                        <x-spinner></x-spinner>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('livewire.roles.form-role')

    @script
        <script>
            Alpine.data('roles', () => ({
                roles: [],
                loading: true,

                init() {
                    this.getRoles();
                },

                async getRoles() {
                    this.loading = true;
                    this.roles = await @this.cargarRoles();

                    for (const role of this.roles) {
                        await this.addRoleTable(role);
                    }

                    setTimeout(() => {
                        __resetTable('#table_roles');
                        this.loading = false;
                    }, 500);
                },

                async addRoleTable(role, is_update = false) {
                    let tr = ``;

                    if (!is_update) {
                        tr += `<tr id="role_${role.id}">`;
                    }

                    // Mostrar solo las categorías de permisos
                    const categorias = this.getPermisosPorCategoria(role.permissions);

                    tr += `
                        <td>${role.name}</td>
                        <td>${categorias.join(', ')}</td>
                        <td>
                            <div class="d-flex">
                                @can('editar roles')
                                <x-buttonsm click="openForm(${role.id})"><i class="la la-edit"></i></x-buttonsm>
                                @endcan
                                @can('eliminar roles')
                                <x-buttonsm click="confirmDelete(${role.id})" color="danger"><i class="la la-trash"></i></x-buttonsm>
                                @endcan
                            </div>
                        </td>`;

                    if (!is_update) {
                        tr += `</tr>`;
                        $('#body_table_roles').prepend(tr);
                    } else {
                        $(`#role_${role.id}`).html(tr);
                    }
                },

                // Función que agrupa los permisos por categoría
                getPermisosPorCategoria(permissions) {
                    const categorias = {
                        "Dashboard": ["ver dashboard"],
                        "Usuarios": ["ver usuarios", "crear usuarios", "editar usuarios", "eliminar usuarios"],
                        "Categorías": ["ver categorias", "crear categorias", "editar categorias",
                            "eliminar categorias"
                        ],
                        "Productos": ["ver productos", "crear productos", "editar productos",
                            "eliminar productos"
                        ],
                        "Proveedores": ["ver proveedores", "crear proveedores", "editar proveedores",
                            "eliminar proveedores"
                        ],
                        "Cuentas": ["ver cuentas", "crear cuentas", "editar cuentas", "eliminar cuentas"],
                        "Cierre de Caja": ["ver cierre-caja", "crear cierre-caja", "editar cierre-caja",
                            "eliminar cierre-caja"
                        ],
                        "Compras": ["ver compras", "crear compras", "editar compras", "eliminar compras"],
                        "Ventas": ["ver ventas", "crear ventas", "editar ventas", "eliminar ventas"],
                        "Ajuste de Inventario": ["ver ajuste-inventario", "crear ajuste-inventario",
                            "editar ajuste-inventario", "eliminar ajuste-inventario"
                        ],
                        "Movimientos": ["ver movimientos", "crear movimientos", "editar movimientos",
                            "eliminar movimientos"
                        ],
                        "Roles": ["ver roles", "crear roles", "editar roles", "eliminar roles"],
                        "Creditos": ["ver creditos", "crear creditos", "editar creditos", "eliminar creditos"]
                    };

                    // Filtrar las categorías con permisos seleccionados
                    let categoriasSeleccionadas = [];
                    for (const [categoria, perms] of Object.entries(categorias)) {
                        if (permissions.some(p => perms.includes(p.name))) {
                            categoriasSeleccionadas.push(categoria);
                        }
                    }
                    return categoriasSeleccionadas;
                },

                async saveFront() {
                    const is_update = @this.role_id ? true : false;

                    const role = await @this.submit();
                    if (role) {
                        this.addRoleTable(role, is_update);
                        $('#form_roles').modal('hide');
                        if (is_update) {
                            for (const key in this.roles) {
                                if (this.roles[key].id === role.id) {
                                    this.roles[key] = role;
                                }
                            }
                            toastRight('success', 'Rol actualizado con éxito');
                        } else {
                            this.roles.push(role);
                            toastRight('success', 'Rol registrado con éxito');
                        }
                    }
                },

                openForm(role_id = null) {
                    let role_edit = this.roles.find(role => role.id === role_id);
                    role_edit = role_edit ?? {};

                    @this.role_id = role_edit.id || null;
                    @this.name = role_edit.name || '';
                    @this.selectedPermissions = role_edit.permissions?.map(perm => perm.name) || [];
                    $('#form_roles').modal('show');
                },

                confirmDelete(role_id) {
                    alertClickCallback('Eliminar Rol', '¿Estás seguro de que quieres eliminar este rol?', 'warning',
                        'Confirmar', 'Cancelar', async () => {
                            const res = await @this.deleteRole(role_id);
                            if (res) {
                                $(`#role_${role_id}`).remove();
                                toastRight('error', 'Rol eliminado');
                            }
                        });
                },
            }));
        </script>
    @endscript
</div>
