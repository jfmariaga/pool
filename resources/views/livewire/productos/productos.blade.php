<div x-data="productos">

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">Productos</h3>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        <a href="javascript:" x-on:click="openForm()" id="btn_form_producto" class="btn btn-dark"> <i
                                class="la la-plus"></i> Nuevo Producto</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="card">
                    <div x-show="!loading">
                        <x-table id="table_productos" extra="d-none">
                            <tr>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Precio Base</th>
                                <th>Categoría</th>
                                <th>Stock infinito</th>
                                <th>Disponible</th>
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

    @include('livewire.productos.form-producto')

    @script
        <script>
            Alpine.data('productos', () => ({
                productos: [],
                loading: true,

                init() { // Se ejecuta cuando ya la aplicación está lista visualmente
                    this.getProductos();
                    $('#categoria_id').change(() => {
                        val = $('#categoria_id').val()
                        @this.categoria_id = val
                    })
                },

                async getProductos() {
                    this.loading = true;
                    this.productos = await @this.getProductos(); // Consultamos productos desde Livewire

                    for (const producto of this.productos) {
                        add = await this.addProductoTable(producto); // Agregamos los productos a la tabla
                    }

                    setTimeout(() => {
                        __resetTable('#table_productos');
                        this.loading = false;
                    }, 500);
                },


                async addProductoTable(producto, is_update = false) {

                    let tr = ``;

                    if (!is_update) {
                        tr += `<tr id="producto_${producto.id}">`;
                    }

                    let formatter = new Intl.NumberFormat('es-CO', {
                        style: 'currency',
                        currency: 'COP',
                        minimumFractionDigits: 0
                    });

                    let valorFormateado = formatter.format(producto.precio_base);

                    tr += `
                        <td> <img class="producto_table" src="{{ asset('storage/productos/${ producto.imagenes ? producto.imagenes : `default.png` }') }}" alt="imagen producto"></td>
                        <td>${producto.nombre}</td>
                        <td>${valorFormateado}</td>
                        <td>${producto.categoria ? producto.categoria.nombre : 'Sin categoría'}</td>
                        <td>${producto.stock_infinito ? '∞' : 'No'}</td>
                        <td>${producto.disponible == 1 ? '<span style="color: green;">✔</span>' : '<span style="color: red;">✘</span>'}</td>
                        <td>
                            <div class="d-flex">
                                <x-buttonsm click="openForm('${producto.id}')"><i class="la la-edit"></i> </x-buttonsm>
                                <x-buttonsm click="confirmDelete('${producto.id}')" color="danger"><i class="la la-trash"></i> </x-buttonsm>
                            </div>
                        </td>`;

                    if (!is_update) {
                        tr += `</tr>`;
                        $('#body_table_productos').prepend(tr);
                    } else {
                        $(`#producto_${producto.id}`).html(tr);
                    }
                },

                async saveFront() {
                    const is_update = @this.producto_id ? true : false;
                    const producto = await @this.save(); // Guardamos producto desde Livewire

                    if (producto) {
                        this.addProductoTable(producto, is_update);

                        $('#form_productos').modal('hide');
                        if (is_update) {
                            for (const key in this.productos) {
                                if (this.productos[key].id == producto.id) {
                                    this.productos[key] = producto;
                                }
                            }
                            toastRight('success', 'Producto actualizado con éxito');
                        } else {
                            this.productos.push(producto);
                            toastRight('success', 'Producto registrado con éxito');
                        }
                    }
                },

                openForm(producto_id = null) {
                    let producto_edit = this.productos.find((producto) => producto.id == producto_id);
                    producto_edit = producto_edit ?? {}; // Si no encuentra resultado, declaramos el objeto vacío


                    @this.producto_id = producto_edit ? producto_edit.id : null;
                    @this.nombre = producto_edit ? producto_edit.nombre : null;
                    @this.precio_base = producto_edit ? __numberFormat(producto_edit.precio_base) : null;
                    @this.precio_mayorista = producto_edit ? __numberFormat(producto_edit.precio_mayorista) : null;
                    @this.categoria_id = producto_edit ? producto_edit.categoria_id : null;
                    @this.descripcion = producto_edit ? producto_edit.descripcion : null;
                    @this.stock_infinito = producto_edit ? producto_edit.stock_infinito : null;
                    @this.disponible = producto_edit ? producto_edit.disponible : '';

                    @this.imagen =
                        `{{ asset('storage/productos/${ producto_edit.hasOwnProperty(`imagenes`) ? producto_edit.imagenes != `` ? producto_edit.imagenes  : `default.png` : `default.png` }') }}`
                    @this.changeImagen = false
                    setTimeout(() => {
                        const categoriaElement = document.getElementById('categoria_id');
                        if (categoriaElement) {
                            $(categoriaElement).val(@this.categoria_id).trigger('change');
                        }
                    }, 300);

                    $('#form_productos').modal('show');
                },

                confirmDelete(producto_id) {
                    alertClickCallback('Inacticvar',
                        'El producto dejará de estar disponible para la venta', 'warning',
                        'Confirmar', 'Cancelar', async () => {
                            const res = await @this.deleteProducto(producto_id);
                            if (res) {
                                const is_update = true
                                this.addProductoTable(res, is_update)
                                if (is_update) {
                                    for (const key in this.cuentas) {
                                        if (this.cuentas[key].id == res.id) {
                                            this.cuentas[key] = res;
                                        }
                                    }
                                    toastRight('error', 'Producto inactivo!');
                                }
                            }
                        });
                },

                getImg() {
                    var file = document.getElementById('img-producto')['files'][0];
                    var reader = new FileReader();
                    var baseString;
                    reader.onloadend = function() {
                        base64 = reader.result;
                        @this.imagen = base64
                        @this.changeImagen = true // para saber si se cambió
                    };
                    reader.readAsDataURL(file);
                },
            }));
        </script>
    @endscript
</div>
