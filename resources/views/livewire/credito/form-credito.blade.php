<x-modal id="form_credito">
    <x-slot name="title">
        <span x-show="!$wire.credito_id">Nuevo Credito</span>
        <span x-show="$wire.credito_id">Editar Credito</span>
    </x-slot>

    <div class="row">
        <!-- Campo Tipo de Movimiento -->
        <div class="col-md-6 mt-1">
            <x-select model="$wire.deudor_id" label="A nombre de:" required="true" id="deudor_id">
                <option value="">----Seleccionar----</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id }}">{{ $usuario->name }} {{$usuario->last_name}}</option>
                @endforeach
            </x-select>
        </div>

        <!-- Campo Valor -->
        <div class="col-md-6 mt-1">
            <x-input model="$wire.monto" label="Valor" required="true" class="mask_decimales"></x-input>
        </div>

        {{-- <div class="col-md-12 mt-1">
            <label for="Adjunto">Soporte</label>
            <input class="form-control" type="file" wire:model="adjunto">
        </div>
        <div class="col-12 mt-1">
            <template x-if="@this.adjunto && @this.credito_id">
                <div>
                    <b>Soporte:</b>
                    <a :href="`${@this.adjunto.replace('public/', 'storage/')}`" target="_blank">Ver Adjunto</a>
                </div>
            </template>

            @if ($adjunto)
                <div class="mt-2 d-flex justify-content-center">
                    <div class="text-center mx-2">
                        @if (in_array($adjunto->extension(), ['jpg', 'png']))
                            <div class="d-flex justify-content-center">
                                <img src="{{ $adjunto->temporaryUrl() }}" alt="" class="img-fluid"
                                    style="max-width: 100px;">
                            </div>
                        @else
                            <div class="d-flex justify-content-center">
                                <img src="{{ $this->getIcon($adjunto->extension()) }}" alt="" class="img-fluid"
                                    style="max-width: 100px;">
                            </div>
                        @endif
                        <span>{{ $adjunto->getClientOriginalName() }}</span>
                    </div>
                </div>
            @endif
        </div> --}}
    </div>

    <!-- Footer con botones de acción -->
    <x-slot name="footer">
        <span>
            <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal"
                wire:click="resetForm">Cancelar</button>
            <button type="button" class="btn btn-outline-primary" x-on:click="saveFront()">Guardar</button>
        </span>
    </x-slot>
</x-modal>
