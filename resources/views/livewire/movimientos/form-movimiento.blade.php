<x-modal id="form_movimientos">
    <x-slot name="title">
        <!-- Título dinámico: Agregar o Editar Movimiento -->
        <span x-show="!$wire.movimiento_id">Agregar Movimiento</span>
        <span x-show="$wire.movimiento_id">Editar Movimiento</span>
    </x-slot>

    <div class="row">
        <!-- Campo Tipo de Movimiento -->
        <div class="col-md-6 mt-1">
            <x-select model="$wire.tipo" label="Tipo de Movimiento" required="true" id="tipo">
                <option value="">----Seleccionar----</option>
                <option value="ingreso">Ingreso</option>
                <option value="egreso">Egreso</option>
                <option value="transferencia">Transferencia</option>
            </x-select>
        </div>

        <!-- Campo Cuenta -->
        <div class="col-md-6 mt-1">
            <x-select model="$wire.cuenta_id" label="Cuenta" required="true" id="cuenta_id">
                <option value="">----Seleccionar----</option>
                @foreach ($cuentas as $cuenta)
                    <option value="{{ $cuenta->id }}">{{ $cuenta->nombre }}</option>
                @endforeach
            </x-select>
        </div>

        <div class="col-md-6 mt-1" x-show="$wire.tipo == 'transferencia'">
            <x-select model="$wire.cuenta_destino_id" label="Cuenta de Destino" required="true" id="cuenta_destino_id">
                <option value="">----Seleccionar----</option>
                @foreach ($cuentas as $cuenta)
                    <option value="{{ $cuenta->id }}">{{ $cuenta->nombre }}</option>
                @endforeach
            </x-select>
        </div>

        <!-- Campo Valor -->
        <div class="col-md-6 mt-1">
            <x-input model="$wire.valor" label="Valor" required="true" class="mask_decimales"></x-input>
        </div>

        <!-- Campo Fecha -->
        <div class="col-md-6 mt-1">
            <x-input model="$wire.fecha" id="fecha" label="Fecha" type="date" required="true"></x-input>
        </div>

        <div class="col-md-12 mt-1">
            <x-textarea model="$wire.descripcion" placeholder="Descripción del movimiento"></x-textarea>
        </div>

        <div class="col-md-12 mt-1">
            <label for="Adjunto">Soporte</label>
            <input class="form-control" type="file" wire:model="adjunto">
        </div>
        <div class="col-12 mt-1">
            <template x-if="@this.adjunto && @this.movimiento_id">
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
        </div>
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
