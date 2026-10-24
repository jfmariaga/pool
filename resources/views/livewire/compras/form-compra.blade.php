<div x-data="formcompras">

    {{-- <span class="loader_new"></span> --}}

    <div class="app-content content">
        <div class="content-wrapper">

            <div class="content-header row">
                <div class="content-header-left col-md-6 col-8 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">
                        <span x-show="$wire.compra_id">Editar compra</span>
                        <span x-show="!$wire.compra_id">Nueva compra</span>
                    </h3>
                </div>
            </div>

            <div class="content-body">
                <div class="card p-3">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <h4><b>Datos generales</b></h4>
                        </div>
                        <div class="col-md-4">
                            <x-select model="$wire.proveedor_id" id="proveedor_id" label="Proveedor" required="true">
                                <option value="0">Seleccionar...</option>
                                @foreach ($proveedores as $i)
                                    <option value="{{ $i->id }}">{{ $i->nombre }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-4">
                            <x-select model="$wire.cuenta_id" id="cuenta_id" label="Método de pago" required="true">
                                <option value="0">Seleccionar...</option>
                                @foreach ($cuentas as $i)
                                    <option value="{{ $i->id }}">{{ $i->nombre }}
                                        {{ $i->numero_de_cuenta ? ' - ' . $i->numero_de_cuenta : '' }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-4">
                            <label for="Adjunto">Soporte</label>
                            <input class="form-control" type="file" wire:model="adjunto">
                        </div>
                        <div class="col-12 mt-1">
                            <template x-if="@this.url && @this.compra_id">
                                <div>
                                    <b>Soporte:</b>
                                    <a :href="new URL(@this.url.replace('public/', 'storage/'), window.location.origin).href" target="_blank">Ver Adjunto</a>
                                </div>
                            </template>

                            @if ($adjunto)
                                <div class="mt-2 d-flex justify-content-center">
                                    <div class="text-center mx-2">
                                        @if (in_array($adjunto->extension(), ['jpg', 'png']))
                                            <div class="d-flex justify-content-center">
                                                <img src="{{ $adjunto->temporaryUrl() }}" alt=""
                                                    class="img-fluid" style="max-width: 100px;">
                                            </div>
                                        @else
                                            <div class="d-flex justify-content-center">
                                                <img src="{{ $this->getIcon($adjunto->extension()) }}" alt=""
                                                    class="img-fluid" style="max-width: 100px;">
                                            </div>
                                        @endif
                                        <span>{{ $adjunto->getClientOriginalName() }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-12 mb-2">
                            <hr>
                            <h4><b>Agregar productos</b></h4>
                        </div>
                        <div class="col-md-4">
                            <x-select model="categoria_id" id="categoria_id" label="Filtrar por categoría">
                                <option value="0">Todas...</option>
                                @foreach ($categorias as $i)
                                    <option value="{{ $i->id }}">{{ $i->nombre }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-4">
                            <x-input model="buscar" keyup="filtrarProductos( null, 1 )" label="Buscar por nombre"
                                placeholder="Enter para filtar..."></x-input>
                        </div>
                        <div class="col-md-12">
                            <div class="content_prod">
                                <template x-for="( producto , key) in filter_productos" :key="key">
                                    <div class="card_item_prod box-shadow-2" x-on:click="addProducto( producto )">
                                        <img :src="`{{ asset('storage/productos/${ producto.imagenes != `` ? producto.imagenes  : `default.png` }') }}`"
                                            alt="prod">
                                        {{-- <img src="{{ asset('storage/productos/default.png') }}" alt="prod"> --}}
                                        <div class="text_item_prod">
                                            <span x-text="producto.nombre"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="col-md-12 mb-2">
                            <hr>
                            <h4><b>Resumen de la compra</b></h4>

                            <div class="mt-2" x-show="$wire.detalles.length > 0">
                                <table class="table table-striped w-100">
                                    <thead>
                                        <tr>
                                            <th style="width:10%;">Img</th>
                                            <th style="width:14%;">Producto</th>
                                            <th style="width:13%;">Cantidad</th>
                                            <th style="width:13%;">Precio de compra C/U</th>
                                            <th style="width:12%;">SubTotal</th>
                                            <th style="width:33%;">Socio</th>
                                            <th style="width:5%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="( item , key) in $wire.detalles" :key="key">
                                            <tr>
                                                <td>
                                                    <img class="producto_table"
                                                        :src="`{{ asset('storage/productos/${ item.imagenes != `` ? item.imagenes  : `default.png` }') }}`"
                                                        alt="prod">
                                                </td>
                                                <td x-text="item.nombre"></td>
                                                <td>
                                                    <div class="d-flex">
                                                        <div class="">
                                                            <x-input type="tel" model="item.cantidad"></x-input>
                                                            <template
                                                                x-if="( typeof item.error_cantidad && item.error_cantidad )">
                                                                <span class="italic_sub c_red">la cantidad no puede ser
                                                                    0</span>
                                                            </template>
                                                            <template
                                                                x-if="( typeof item.error_vendidos && item.error_vendidos )">
                                                                <span class="italic_sub c_red"
                                                                    x-text="'La cantidad no puede ser inferior a ' + item.vendidos"></span>
                                                            </template>
                                                            <div class="italic_sub c_orange" x-show="item.vendidos > 0"
                                                                x-text="'Vendidos: ' + item.vendidos"></div>
                                                        </div>
                                                        <div class="ml_5 d-block">
                                                            <div class="">
                                                                <a type="buttom" x-on:click="item.cantidad++"><i
                                                                        class="fa-solid fa-caret-up fs-22"></i></a>
                                                            </div>
                                                            <div class="">
                                                                <a type="buttom"
                                                                    x-on:click="if( item.cantidad > 0 ) item.cantidad-- "><i
                                                                        class="fa-solid fa-caret-down fs-22"></i></a>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <x-input model="item.precio_compra"
                                                        class="mask_decimales"></x-input>
                                                    <template
                                                        x-if="( typeof item.error_precio_compra && item.error_precio_compra )">
                                                        <span class="italic_sub c_red">la precio de compra no puede ser
                                                            0</span>
                                                    </template>
                                                </td>
                                                <td
                                                    x-text="__numberFormat( __limpiarNumDecimales( item.precio_compra ) * item.cantidad )">
                                                </td>
                                                <td>
                                                    <template x-if="aplicaSocios( item.categoria_id )">
                                                        <div>
                                                            <template x-for="(s, sidx) in item.socios" :key="sidx">
                                                                <div class="mb-2 p-1"
                                                                    style="border:1px solid #e0e0e0; border-radius:4px;">
                                                                    <select class="form-control form-control-sm mb-1"
                                                                        x-model="s.socio_id">
                                                                        <option value="">Socio...</option>
                                                                        @foreach ($socios as $i)
                                                                            <option value="{{ $i->id }}">{{ $i->nombre }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <div class="d-flex align-items-center"
                                                                        style="gap:4px;">
                                                                        <input type="text"
                                                                            class="form-control form-control-sm mask_decimales"
                                                                            style="flex:1;" x-model="s.monto">
                                                                        <a href="javascript:"
                                                                            x-on:click="item.socios.splice(sidx, 1)">
                                                                            <i class="la la-trash c_red"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                            <a href="javascript:" class="italic_sub"
                                                                x-on:click="item.socios.push({ socio_id: '', monto: 0 })">
                                                                <i class="la la-plus"></i> Agregar socio
                                                            </a>
                                                            <div class="italic_sub mt-1"
                                                                x-show="item.socios.length > 0"
                                                                :class="sumSocios(item) == (__limpiarNumDecimales(item.precio_compra) * item.cantidad) ? 'c_green' : 'c_red'">
                                                                Aportado:
                                                                <span x-text="__numberFormat(sumSocios(item))"></span>
                                                                /
                                                                <span
                                                                    x-text="__numberFormat(__limpiarNumDecimales(item.precio_compra) * item.cantidad)"></span>
                                                            </div>
                                                            <template x-if="item.socios.length > 1">
                                                                <span class="badge badge-info">En sociedad
                                                                    (<span x-text="item.socios.length"></span>
                                                                    socios)</span>
                                                            </template>
                                                            <template
                                                                x-if="( typeof item.error_socios && item.error_socios )">
                                                                <div class="italic_sub c_red">Verifica los
                                                                    socios: deben tener socio seleccionado y la
                                                                    suma de los montos debe ser igual al subtotal
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                    <template x-if="!aplicaSocios( item.categoria_id )">
                                                        <span class="italic_sub">N/A</span>
                                                    </template>
                                                </td>
                                                <td>
                                                    <template x-if="item.vendidos == 0">
                                                        <x-buttonsm click="removeProducto( key )" color="danger"><i
                                                                class="la la-trash"></i> </x-buttonsm>
                                                    </template>
                                                    <template x-if="item.vendidos > 0">
                                                        <x-buttonsm
                                                            click="alertMessage('Lo sentimos', 'No puede eliminar un producto que ya ha tenido ventas', 'error')"
                                                            color="danger"><i class="la la-trash"></i> </x-buttonsm>
                                                    </template>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                                <div class="mt-3">
                                    <hr>
                                    <h4 class="f_right"><b>Total Compra:</b> <span x-text="totalCompra"></span></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <center>
                                <a href="{{ route('compras') }}" class="btn btn-outline-dark">
                                    Cancelar
                                </a>
                                <a href="javascript:" x-on:click="guardar()" class="btn btn-outline-success">
                                    <span x-show="$wire.compra_id">Actualizar</span>
                                    <span x-show="!$wire.compra_id">Guardar</span>
                                </a>
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('formOK', () => {
                alertMessage('', '', 'success', false)
                setTimeout(() => {
                    window.location.href = "{{ route('compras') }}"
                }, 1200);
            });

            Alpine.data('formcompras', () => ({

                list_productos: [], // original
                filter_productos: [], // cambia con los filtros
                errors: [], // para validar el form dinámico
                buscar: '',
                categorias_socios: @js($categorias->where('aplica_socios', 1)->pluck('id')->values()),

                aplicaSocios(categoria_id) {
                    return this.categorias_socios.map(String).includes(String(categoria_id))
                },

                init() { // se ejecuta cuando ya la aplicación esta lista visualmente

                    this.getProductos()

                    $('#categoria_id').change(() => {
                        val = $('#categoria_id').val()
                        this.filtrarProductos(val)
                    })
                    $('#proveedor_id').change(() => {
                        @this.proveedor_id = $('#proveedor_id').val()
                    })
                    $('#cuenta_id').change(() => {
                        @this.cuenta_id = $('#cuenta_id').val()
                    })
                },

                // consulta los productos y los almacena de manera local
                async getProductos() {
                    this.list_productos = await @this.getProductos()
                    this.filter_productos = [...this.list_productos]
                },

                // agrega un producto a la compra
                addProducto({
                    ...producto
                }) {
                    // revisamos si ya está en la lista
                    const exist = @this.detalles.find((i) => i.id == producto.id)

                    if (exist) { // si existe solo le sumamos 1
                        exist.cantidad++
                        toastRight('warning', 'El producto ya está en la lista, se agregó 1 cantidad')
                    } else {
                        producto.cantidad = 1
                        producto.precio_compra = 0
                        producto.vendidos = 0 // se usa luego en el editar
                        producto.socios = [] // socios que aportaron si la prenda se compra en sociedad
                        @this.detalles.push(producto)
                    }
                },

                // filtra los porductos
                filtrarProductos(categoria_id = null, filtrar = null) {

                    if (categoria_id) { // filtramos por categoria

                        if (categoria_id == 0) {
                            this.filter_productos = [...this.list_productos]
                        } else {
                            this.filter_productos = [...this.list_productos.filter((i) => i.categoria_id ==
                                categoria_id)]
                        }

                    } else if (this.buscar && this.buscar.length > 2) { // filtramos por el buscador

                        const buscar = __eliminarAcentos(this.buscar).toLowerCase()
                        this.filter_productos = [...this.list_productos.filter((i) => __eliminarAcentos(i.nombre
                            .toLowerCase()).includes(buscar))]

                    } else {
                        this.filter_productos = [...this.list_productos]
                    }
                },

                // quita un producto de la lista
                removeProducto(key) {
                    @this.detalles = @this.detalles.filter((i, index) => index != key)
                },

                // suma de los montos aportados por los socios de un item
                sumSocios(item) {
                    return (item.socios || []).reduce((acc, s) => acc + __limpiarNumDecimales(s.monto), 0)
                },

                get totalCompra() {
                    let total = 0
                    @this.detalles.map((item) => {
                        total += (__limpiarNumDecimales(item.precio_compra) * item.cantidad)
                    })
                    return __numberFormat(total)
                },

                async guardar() {
                    if (@this.detalles.length > 0) {
                        continuar = true

                        // revisamos los campos dinámicos
                        @this.detalles.map((item, key) => {

                            item.error_cantidad = false
                            item.error_precio_compra = false
                            item.error_vendidos = false
                            item.error_socios = false

                            if (item.cantidad <= 0) { // que la cantidad no sea menor a 0
                                continuar = false
                                item.error_cantidad = true
                            } else if (item.cantidad < item
                                .vendidos) { // que la cantidad no sea inferior a lo que ya vendi
                                continuar = false
                                item.error_vendidos = true
                            }

                            if (item.precio_compra <= 0) { // que lleve precio de compra
                                continuar = false
                                item.error_precio_compra = true
                            }

                            // si se asignaron socios, deben estar completos y sumar el subtotal de la línea
                            if (item.socios && item.socios.length > 0) {
                                const subtotal = __limpiarNumDecimales(item.precio_compra) * item.cantidad
                                const incompleto = item.socios.some((s) => !s.socio_id || !s.monto)
                                const noCuadra = Math.abs(this.sumSocios(item) - subtotal) > 1
                                const socio_ids = item.socios.map((s) => s.socio_id)
                                const duplicado = socio_ids.length !== new Set(socio_ids).size

                                if (incompleto || noCuadra || duplicado) {
                                    continuar = false
                                    item.error_socios = true
                                }
                            }
                        })

                        if (continuar) {
                            @this.guardar()
                        } else {
                            toastRight('error', 'Formulario con errores!')
                        }
                    } else {
                        toastRight('warning', 'Debe seleccinar por lo menos un producto')
                    }

                }

            }))
        </script>
    @endscript
</div>
