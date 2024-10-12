<div x-data="formajuste">

    {{-- <span class="loader_new"></span> --}}

    <div class="app-content content">
        <div class="content-wrapper">

            <div class="content-header row">
                <div class="content-header-left col-md-6 col-8 mb-2 breadcrumb-new">
                    <h3 class="content-header-title mb-0 d-inline-block br_none">
                        <span x-show="$wire.ajuste_id">Editar ajuste</span>
                        <span x-show="!$wire.ajuste_id">Nuevo ajuste</span>
                    </h3>
                </div>
            </div>

            <div class="content-body">
                <div class="card p-3">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <hr>
                            <h4><b>Seleccionar productos</b></h4>
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
                            <x-input model="buscar" keyup="filtrarProductos( null, 1 )" label="Buscar por nombre" placeholder="Enter para filtar..."></x-input>
                        </div>
                        <div class="col-md-12">
                            <div class="content_prod">
                                <template x-for="( producto , key) in filter_productos" :key="key">
                                    <div class="card_item_prod box-shadow-2" x-on:click="addProducto( producto )">
                                        <img :src="`{{ asset('storage/productos/${ producto.imagenes != `` ? producto.imagenes  : `default.png` }') }}`" alt="prod">
                                        <div class="text_item_prod">
                                            <span x-text="producto.nombre"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="col-md-12 mb-2">
                            <hr>
                            <h4><b>Resumen del ajuste</b></h4>
                            
                            <div class="mt-2" x-show="$wire.detalles.length > 0">
                                <table class="table table-striped w-100">
                                    <thead>
                                        <tr>
                                            <th style="width:15%;">Img</th>
                                            <th style="width:20%;">Producto</th>
                                            <th style="width:20%;">Stock sistema</th>
                                            <th style="width:20%;">Stock real</th>
                                            <th style="width:20%;">Cantidad ajustada</th>
                                            <th style="width:5%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="( item , key) in $wire.detalles" :key="key">
                                            <tr>
                                                <td>
                                                    <img class="producto_table" :src="`{{ asset('storage/productos/${ item.imagenes != `` ? item.imagenes  : `default.png` }') }}`" alt="prod">
                                                </td>
                                                <td x-text="item.nombre"></td>
                                                <td>
                                                    <span x-text="item.stock_sistema"></span>
                                                </td>
                                                <td>
                                                    <x-input model="item.stock_real"></x-input>
                                                    <template x-if="( typeof item.error_negativo && item.error_negativo )">
                                                        <span class="italic_sub c_red">No puede ajustar a negativo</span>
                                                    </template>
                                                    <template x-if="( typeof item.error_igual && item.error_igual )">
                                                        <span class="italic_sub c_red">No ha modificado el stock</span>
                                                    </template>
                                                </td>
                                                <td>
                                                    <span :class="( item.stock_real - item.stock_sistema ) <= 0 ? 'c_red' : 'c_green'"  x-text="item.stock_real - item.stock_sistema"></span>
                                                </td>
                                                <td>
                                                    <x-buttonsm  click="removeProducto( key )" color="danger"><i class="la la-trash"></i> </x-buttonsm>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <center>
                                <a href="{{ route('ajuste-inventario') }}" class="btn btn-outline-dark">
                                    Cancelar
                                </a>
                                <a href="javascript:" x-on:click="guardar()" class="btn btn-outline-success">
                                    <span x-show="$wire.ajuste_id">Actualizar</span>
                                    <span x-show="!$wire.ajuste_id">Guardar</span>
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
                    window.location.href = "{{ route('ajuste-inventario') }}"
                }, 1200);
            });

            Alpine.data('formajuste', () => ({

                list_productos:     [], // original
                filter_productos:   [], // cambia con los filtros
                errors:             [], // para validar el form dinámico
                buscar:             '',

                init() { // se ejecuta cuando ya la aplicación esta lista visualmente

                    this.getProductos()

                    $('#categoria_id').change( ()=>{
                        val = $('#categoria_id').val()
                        this.filtrarProductos(val)
                    })
                },

                // consulta los productos y los almacena de manera local
                async getProductos(){
                    this.list_productos     = await @this.getProductos()
                    this.filter_productos   = [...this.list_productos]
                },

                // agrega un producto al ajuste
                addProducto( {...producto} ){
                    // revisamos si ya está en la lista
                    const exist = @this.detalles.find( (i) => i.id == producto.id )

                    if( exist ){ // si existe solo le sumamos 1
                        toastRight('warning', 'El producto ya está en la lista de ajuste')
                    }else{
                        producto.stock_sistema  = producto.stock
                        producto.stock_real     = producto.stock
                        @this.detalles.push( producto )
                    }
                },

                // filtra los porductos
                filtrarProductos( categoria_id = null, filtrar = null ){
                    
                    if( categoria_id  ){// filtramos por categoria

                        if( categoria_id == 0 ){
                            this.filter_productos = [...this.list_productos]
                        }else{
                            this.filter_productos = [...this.list_productos.filter( (i) => i.categoria_id == categoria_id )]
                        }

                    }else if( this.buscar && this.buscar.length > 2  ){ // filtramos por el buscador

                        const buscar = __eliminarAcentos( this.buscar ).toLowerCase()
                        this.filter_productos = [...this.list_productos.filter( (i) => __eliminarAcentos( i.nombre.toLowerCase() ).includes( buscar ) )]
                        
                    }else{
                        this.filter_productos = [...this.list_productos]
                    }
                },

                // quita un producto de la lista
                removeProducto( key ){
                    @this.detalles = @this.detalles.filter( ( i, index ) => index != key )
                },

                async guardar(){
                    if ( @this.detalles.length > 0 ) {
                        continuar = true

                        // revisamos los campos dinámicos
                        @this.detalles.map( ( item, key ) => {

                            item.error_negativo = false
                            item.error_igual    = false

                            if( item.stock_real < 0 ){ // que la cantidad no sea menor a 0
                                continuar           = false
                                item.error_negativo = true 
                            }else if( item.stock_sistema == item.stock_real ){ // que la cantidad no sea inferior a lo que ya vendi
                                continuar           = false
                                item.error_igual    = true 
                            }

                        })

                        if( continuar ){
                            @this.guardar()
                        }else{
                            toastRight('error', 'Formulario con errores!')
                        }
                    }else{
                        toastRight('warning', 'Debe seleccinar por lo menos un producto')
                    }
                     
                }

            }))
        </script>
    @endscript
</div>
