<div x-data="prueba">
    <style>
        .green{
            color: green;
        }
        .orange{
            color: orange;
        }
    </style>

    <div class="row justify-content-center">
        <div class="col-md-4 mt-5 mb-5 bg-white">
            <h1 class="text-align-center mt-2">
                <span :class="$wire.change ? 'green' : 'orange'">Funciones básicas útiles</span>
            </h1>        
            <br>
            <p><b>Nota:</b>Los cambios en Alpine aplican aún cuando este contenido en un wire:ignore, esto abre un mundo de posibilidades</p>
            <hr>
            <div>
                <p>Incrementador normal V.2, cada cambio una petición al back</p>
                <h1>{{ $count }}</h1>
                <button wire:click="increment">+</button>
                <button wire:click="decrement">-</button>
            </div>
            <hr>
            <div>
                <p>Incrementador V.3 carga regulada al back</p>
                <h1 x-text="$wire.count"></h1>
                <button x-on:click="$wire.count++">+</button>
                <button x-on:click="$wire.count--">-</button>
            </div>
            <hr>
            <div>
                <p>Cambiar el estado de una variable e el front</p><br>
                <button x-on:click="$wire.change = !$wire.change">Change front</button>
                <span :class="$wire.change ? 'green' : 'orange'">Cambio el color sin ir al Back</span>
                <br>
                <p>Sincronizar input con etiqueta solo el front</p><br>
                <input type="text" class="form-control"  x-model="$wire.text"><br>
                <span class="mt-1" x-text="$wire.text"></span>
            </div>
            <hr>
            <div>
                <p>Agregar item a un array</p>
                <button x-on:click="$wire.list.push('Agregando ando')">Agregar item</button>
                <template x-for="(value, index) in $wire.list">
                    <li>
                        <span x-text="index"></span>: <span x-text="value"></span>
                    </li>
                </template>
            </div>
            <hr>
            <div>
                <p>Agregando iten con asteroides jajja</p>
                <button x-on:click="addListArray()">Agregar a la lista</button>
                <template x-for="(value, index) in $wire.listArray">
                    <li style='display:flex;'>
                        <input type="text" x-model="value.nombre" class="form-control" >
                        <input type="text" x-model="value.edad" class="form-control" >
                    </li>
                </template>
                <p>Esto es lo que está en el back</p>
                {{ json_encode( $listArray ) }}
            </div>
            <hr>
            <div wire:ignore>
                <p>Select2 solo front</p>
                <input type="text" class="form-control" x-model="input" x-on:keyup.enter="addSelect()" placeholder="Agregar valor al Select..">
                <select class="select2 w-100 mt-1" name="state" id="select1">
                    <option value="F">Freezer</option>
                    <option value="B">Vegueta</option>
                </select>
            </div>
            <hr>
            <div wire:ignore class="mb-2">
                <p>Probando llamada a Func externa</p>
                <button x-on:click="actFuncExterior()">Func Exterior</button>
                <button x-on:click="probandoPromesa()">La cereza del pastel, probando llamada async sin emitir render</button>
                <input class="form-control mt-3" x-mask:dynamic="$money($input)" placeholder="input mask...">
            </div>
            <hr>
            <div class="">
                {{ $input_prueba }}
                <p>Probando forms desde Componentes</p>
                <x-forms.input label="Label prueba" name="input_prueba"></x-forms.input>
                <span x-text="$wire.input_prueba"></span>
                <button class="mt-2" wire:click="save()">Validar</button>
            </div>
        </div>
    </div>

    @script
        <script>
            console.log('entrando ando')

            Alpine.data('prueba', () => ({
                text_aux: 'hola',
                input: '',
    
                prueba() {
                    this.text_aux = 'Kakaroto'; // para acceder a una variable local utilizamos this
                    console.log( @this.decrement() ) // podemos llamar funciones igual
                },
                addListArray(){
                    @this.listArray.push({nombre: '', edad: ''});
                },
                addSelect(){
                    var newOption = new Option(this.input, 5, false, false); //id hipotetico
                    $('#select1').append(newOption).trigger('change');
                    this.input = '';
                },
                actFuncExterior(){
                    funcExterior()
                },
                async probandoPromesa(){ // va al seridor y trae la respuesta sin recargar
                    const res = await @this.probandoRes()
                    console.log(res)
                    alert( res )
                }
            }))

        </script>
    @endscript
</div>