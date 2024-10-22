<div x-data="categorias">

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Categorías</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        @can('crear categorias')
                            <a href="javascript:" x-on:click="openForm()" id="btn_form_categoria" class="btn btn-dark"> <i
                                    class="la la-plus"></i> Nuevo</a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="card">
                    <div x-show="!loading">
                        <x-table id="table_categorias" extra="d-none">
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Esatdo</th>
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

    @include('livewire.categoria.form-categoria')


    @script
        <script>
            Alpine.data('categorias', () => ({
                categorias: [],
                loading: true,

                init() { // se ejecuta cuando ya la aplicación esta lista visualmente
                    this.getCategorias()
                    $('#status').change(() => {
                        val = $('#status').val()
                        console.log(val);

                        @this.status = val
                    })
                },

                async getCategorias() {

                    this.loading = true

                    this.categorias = await @this.getCategorias() // consultamos

                    for (const categoria of this.categorias) {
                        add = await this.addcategoriaTable(categoria) // agregamos
                    }

                    setTimeout(() => { // necesario para que no se renderice datatable antes de haber cargado el body
                        __resetTable('#table_categorias')
                        this.loading = false
                    }, 500);

                },

                async addcategoriaTable(categoria, is_update = false) { // agregamos cada categoria a la tabla

                    tr = ``;

                    if (!is_update) {
                        tr += `<tr id="categoria_${categoria.id}">`
                    }

                    tr += `<td>${categoria.nombre}</td>
                        <td>${ categoria.descripcion ? categoria.descripcion :''  }</td>
                        <td>${ categoria.status == 1 ?  '<span style="color: green;">✔</span>' : '<span style="color: red;">✘</span>' }</td>
                        <td>
                            <div class="d-flex">
                                @can('editar categorias')
                                <x-buttonsm click="openForm(${categoria.id})"><i class="la la-edit"></i> </x-buttonsm>
                                @endcan
                                @can('eliminar categorias')
                                <x-buttonsm click="confirmDelete(${categoria.id})" color="danger"><i class="la la-trash"></i> </x-buttonsm>
                                @endcan
                            </div>
                        </td>`

                    if (!is_update) {
                        tr += `</tr>`
                        $('#body_table_categorias').prepend(tr)
                    } else {
                        $(`#categoria_${categoria.id}`).html(tr)
                    }

                },

                async saveFront() {
                    const is_update = @this.categoria_id ? true : false;

                    const categoria = await @this.save()
                    if (categoria) {
                        this.addcategoriaTable(categoria, is_update)
                        $('#form_categorias').modal('hide')
                        if (is_update) {
                            for (const key in this.categorias) {
                                if (this.categorias[key].id == categoria.id) {
                                    this.categorias[key] = categoria;
                                }
                            }
                            toastRight('success', 'Categoría actualizada con éxito');
                        } else {
                            this.categorias.push(categoria);
                            toastRight('success', 'Categoría resgistrada con éxito');
                        }
                    }
                },

                openForm(categoria_id = null) {
                    let categoria_edit = this.categorias.find((categoria) => categoria.id == categoria_id);
                    categoria_edit = categoria_edit ?? {} // sino encunetra resultado declaramos el obteto vacío

                    @this.categoria_id = categoria_edit ? categoria_edit.id : null
                    @this.nombre = categoria_edit ? categoria_edit.nombre : null
                    @this.descripcion = categoria_edit ? categoria_edit.descripcion : null
                    @this.status = categoria_edit ? categoria_edit.status : null
                    $('#form_categorias').modal('show')

                    setTimeout(() => {
                            const statusElement = document.getElementById('status');
                            if (statusElement) {
                                $(statusElement).val(@this.status).trigger('change');
                            }
                        },
                        300
                    );
                },

                confirmDelete(categoria_id) {
                    alertClickCallback('Inactivar',
                        'La categoría pasará a un estado inactivo', 'warning',
                        'Confirmar', 'Cancelar', async () => {
                            const res = await @this.deleteCategoria(categoria_id)
                            if (res) {
                                const is_update = true
                                this.addcategoriaTable(res, is_update)
                                if (is_update) {
                                    for (const key in this.cuentas) {
                                        if (this.cuentas[key].id == res.id) {
                                            this.cuentas[key] = res;
                                        }
                                    }
                                    toastRight('error', 'Categoría inactiva!')
                                }
                            }
                        })
                },
            }))
        </script>
    @endscript
</div>
