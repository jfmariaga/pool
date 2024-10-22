<div x-data="usuarios">

    {{-- <span class="loader_new"></span> --}}

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Usuarios</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        @can('crear usuarios')
                            <a href="javascript:" x-on:click="openForm()" id="btn_form_personal" class="btn btn-dark"> <i
                                    class="la la-plus"></i> Nuevo</a>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="card">
                    <div x-show="!loading">
                        <x-table id="table_users" extra="d-none">
                            <tr>
                                <th>Imagen</th>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Celular</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acc</th>
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

    {{-- solo lo sacamos para no hacer este tan extenso --}}
    @include('livewire.usuarios.form-usuarios')

    @script
        <script>
            Alpine.data('usuarios', () => ({
                users: [],
                roles: @json($roles),
                loading: true,

                init() {
                    this.getUsers();

                    $('#role').change(() => {
                        val = $('#role').val()
                        @this.role_id = val
                    })
                },

                async getUsers() {
                    this.loading = true;
                    this.users = await @this.getUsers();

                    for (const user of this.users) {
                        await this.addUser(user);
                    }

                    setTimeout(() => {
                        __resetTable('#table_users');
                        this.loading = false;
                    }, 500);
                },

                async addUser(user, is_update = false) {
                    let tr = ``;

                    if (!is_update) {
                        tr += `<tr id="user_${user.id}">`;
                    }

                    const roles = user.roles.map(role => role.name).join(', ');

                    tr += `
            <td>
                <img class="avatar_table" src="{{ asset('storage/avatars/${user.picture ? user.picture : `default.png`}') }}" alt="avatar">
            </td>
            <td>${user.document}</td>
            <td>${user.name} ${user.last_name}</td>
            <td>${user.phone}</td>
            <td>${roles ? roles : ''}</td>
            <td>${user.status == 1 ?  '<span style="color: green;">✔</span>' : '<span style="color: red;">✘</span>'}</td>
            <td>
                <div class="d-flex">
                    @can('editar usuarios')
                    <x-buttonsm click="openForm('${user.id}')"><i class="la la-edit"></i></x-buttonsm>
                    @endcan
                    @can('eliminar usuarios')
                    <x-buttonsm click="confirmDelete('${user.id}')" color="danger"><i class="la la-trash"></i></x-buttonsm>
                    @endcan
                </div>
            </td>`;

                    if (!is_update) {
                        tr += `</tr>`;
                        $('#body_table_users').prepend(tr);
                    } else {
                        $(`#user_${user.id}`).html(tr);
                    }
                },

                getImg() {
                    const file = document.getElementById('picture')['files'][0];
                    const reader = new FileReader();
                    reader.onloadend = function() {
                        base64 = reader.result;
                        @this.picture = base64;
                        @this.change_picture = true;
                    };
                    reader.readAsDataURL(file);
                },

                async saveFront() {
                    const is_update = @this.user_id ? true : false;
                    const user = await @this.save();
                    if (user) {
                        this.addUser(user, is_update);
                        $('#form_usuarios').modal('hide');
                        if (is_update) {
                            for (const key in this.users) {
                                if (this.users[key].id == user.id) {
                                    this.users[key] = user;
                                }
                            }
                            toastRight('success', 'Usuario actualizado con éxito');
                        } else {
                            this.users.push(user);
                            toastRight('success', 'Usuario registrado con éxito');
                        }
                    }
                },

                openForm(user_id = null) {
                    let user_edit = this.users.find((user) => user.id == user_id);
                    user_edit = user_edit ?? {};

                    @this.user_id = user_edit ? user_edit.id : null;
                    @this.document = user_edit ? user_edit.document : null;
                    @this.name = user_edit ? user_edit.name : null;
                    @this.last_name = user_edit ? user_edit.last_name : null;
                    @this.user_name = user_edit ? user_edit.user_name : null;
                    @this.phone = user_edit ? user_edit.phone : null;
                    @this.email = user_edit ? user_edit.email : null;
                    @this.status = user_edit ? user_edit.status : null;
                    @this.picture = user_edit.picture ?
                        `{{ asset('storage/avatars/${user_edit.picture}') }}` :
                        `{{ asset('storage/avatars/default.png') }}`;
                    @this.role_id = user_edit.roles && user_edit.roles.length > 0 ? user_edit.roles[0].id : '';

                    // @this.password = user_edit ? user_edit.password: null;
                    @this.change_picture = false;

                    $('#form_usuarios').modal('show');

                    setTimeout(() => {
                        const statusElement = document.getElementById('status');
                        if (statusElement) {
                            $(statusElement).val(@this.status).trigger('change');
                        }

                        const roleElement = document.getElementById('role');
                        if (roleElement) {
                            $(roleElement).val(@this.role_id).trigger('change');
                        }
                    }, 300);
                },

                confirmDelete(user_id) {
                    alertClickCallback('Desactivar',
                        'El usuario no se eliminará por completo, pasará a estar inactivo en el sistema',
                        'warning', 'Confirmar', 'Cancelar', async () => {
                            const res = await @this.desactivarUser(user_id);
                            if (res) {
                                this.addUser(res, true);
                                toastRight('error', 'Usuario inactivado');
                            }
                        });
                },
            }));
        </script>
    @endscript
</div>
