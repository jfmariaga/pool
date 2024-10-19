<x-modal id="form-cierre">

    <x-slot name="title">
        <span>Cierre de Caja</span>
    </x-slot>

    <div class="row">
        <div class="col-md-3">
            <div class="card box-shadow-1 p-2">
                <b class="fs_12">Total Ventas</b>
                <span class="mt-1" x-text="__numberFormat( $wire.total_ventas )"></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card box-shadow-1 p-2">
                <b class="fs_12">Total Compras</b>
                <span class="mt-1" x-text="__numberFormat( $wire.total_compras )"></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card box-shadow-1 p-2">
                <b class="fs_12">Egresos Manuales</b>
                <span class="mt-1" x-text="__numberFormat( $wire.total_egresos )"></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card box-shadow-1 p-2">
                <b class="fs_12">Ingresos Manuales</b>
                <span class="mt-1" x-text="__numberFormat( $wire.total_ingresos )"></span>
            </div>
        </div>
        <div class="col-md-12">
            <hr>
            <b>Cierre por Cuenta</b>
            <table class="table table-striped w-100 mt-1">
                <thead>
                    <tr>
                        <th>Cuenta</th>
                        <th>Inicio</th>
                        <th>Ingresos</th>
                        <th>Egresos</th>
                        <th>Cierre</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="( item , key) in $wire.det_cuentas" :key="key">
                        <tr>
                            <td><span x-text="item.nombre"></span></td>
                            <td><span x-text="__numberFormat( item.inicio )"></span></td>
                            <td><span x-text="__numberFormat( item.ingreso )"></span></td>
                            <td><span x-text="__numberFormat( item.egreso )"></span></td>
                            <td><span x-text="__numberFormat( item.cierre )"></span></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="col-md-12">
            <hr>
            <div class="d-flex">
                <div class="w_160px">
                    <b>Total Apertura Caja:</b>
                </div>
                <div>
                    <span x-text="__numberFormat($wire.total_inicio)"></span>
                </div>
            </div>
            <div class="d-flex">
                <div class="w_160px">
                    <b>Total Cierre Caja:</b>
                </div>
                <div>
                    <span x-text="__numberFormat($wire.total_cierre)"></span>
                </div>
            </div>
        </div>

    </div>

    <x-slot name="footer">
        <template x-if="loading_cierre">
            <x-spinner></x-spinner>
        </template>
        <template x-if="!loading_cierre">
            <span>
                <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-outline-primary" x-on:click="saveFront()">Guardar Cierre</button>
            </span>
        </template>
    </x-slot>
</x-modal>
