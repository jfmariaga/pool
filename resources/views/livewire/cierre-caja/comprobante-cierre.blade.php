<x-modal id="comprobante-cierre">

    <x-slot name="title">
        <span>Comprobante Cierre</span>
        <span x-text="comprobante.fecha"></span>
    </x-slot>

    <div class="row">
        <div class="col-md-4">
            <div class="card box-shadow-1 p-2">
                <b class="fs_12">Ventas de contado</b>
                <span class="mt-1" x-text="__numberFormat( comprobante.total_ventas )"></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card box-shadow-1 p-2">
                <b class="fs_12">Ventas a Crédito</b>
                <span class="mt-1" x-text="__numberFormat( comprobante.total_creditos )"></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card box-shadow-1 p-2">
                <b class="fs_12">Abono a Créditos</b>
                <span class="mt-1" x-text="__numberFormat( comprobante.abonos_creditos )"></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card box-shadow-1 p-2">
                <b class="fs_12">Total Compras</b>
                <span class="mt-1" x-text="__numberFormat( comprobante.total_compras )"></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card box-shadow-1 p-2">
                <b class="fs_12">Egresos Manuales</b>
                <span class="mt-1" x-text="__numberFormat( comprobante.total_egresos )"></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card box-shadow-1 p-2">
                <b class="fs_12">Ingresos Manuales</b>
                <span class="mt-1" x-text="__numberFormat( comprobante.total_ingresos )"></span>
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
                    <template x-for="( item , key) in comprobante.detalles" :key="key">
                        <tr>
                            <td><span x-text="item.cuenta.nombre"></span></td>
                            <td><span x-text="__numberFormat( item.total_inicio )"></span></td>
                            <td><span x-text="__numberFormat( item.total_ingresos )"></span></td>
                            <td><span x-text="__numberFormat( item.total_egresos )"></span></td>
                            <td><span x-text="__numberFormat( item.total_cierre )"></span></td>
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
                    <span x-text="__numberFormat(comprobante.total_inicio)"></span>
                </div>
            </div>
            <div class="d-flex">
                <div class="w_160px">
                    <b>Total Cierre Caja:</b>
                </div>
                <div>
                    <span x-text="__numberFormat(comprobante.total_cierre)"></span>
                </div>
            </div>
        </div>

    </div>

    <x-slot name="footer">
        <span>
            <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Cerrar</button>
        </span>
    </x-slot>
</x-modal>
