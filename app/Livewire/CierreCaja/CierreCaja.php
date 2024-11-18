<?php

namespace App\Livewire\CierreCaja;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

use App\Models\CierreCaja as ModelCierreCaja;
use App\Models\Compra;
use App\Models\Cuenta;
use App\Models\DetCierreCaja;
use App\Models\Movimiento;
use App\Models\User;
use App\Models\Venta;
use App\Notifications\CierreCajaNotificacion;

class CierreCaja extends Component
{

    // detalles de cierre por cuenta
    public $det_cuentas     = [];

    // valores generados después del ultimo cierre
    public $total_compras   = 0;
    public $total_ventas    = 0;
    public $total_creditos  = 0;
    public $abonos_creditos = 0;
    public $total_egresos   = 0;
    public $total_ingresos  = 0;
    public $total_inicio    = 0;
    public $total_cierre    = 0;

    public function mount()
    {
        $this->getUltCierre();
    }

    public function render()
    {
        return view('livewire.cierre-caja.cierre-caja')->title('Cierre de caja');
    }

    // Método para obtener los usuarios
    public function getData()
    {
        $this->skipRender(); // Evita que el componente se renderice nuevamente
        $cierres = ModelCierreCaja::with('usuario', 'detalles.cuenta')->get();
        return $cierres;
    }

    // obtiene la info del ultimo cierre y prepara todo para un nuevo cierre
    public function getUltCierre()
    {

        $ult_cierre = ModelCierreCaja::with('detalles')->orderBy('id', 'desc')->first();

        // para saber hasta donde contó el último cierre de caja
        $ult_venta      = 0;
        $ult_compra     = 0;
        $ult_movimiento = 0;

        // si ya existe un cierre, tomamos como base los últimos IDs procesados
        if (isset($ult_cierre->id)) {

            $ult_cierre = $ult_cierre->toArray();

            $ult_venta          = $ult_cierre['ult_venta'];
            $ult_compra         = $ult_cierre['ult_compra'];
            $ult_movimiento     = $ult_cierre['ult_movimiento'];

            // si ya había un ultimo cierre, el inicio del nuevo es el fin del ultimo
            $this->total_inicio = $ult_cierre['total_cierre'];
        }

        // consultamos los movimientos generados después del ultimo cierre
        $movimientos            = Movimiento::where('id', '>', $ult_movimiento)->get();

        // organizamos el cierre por cada cuenta
        $cuentas = Cuenta::select('id', 'nombre')->where('status', 1)->where('id','<>', 0)->get()->toArray();
        if ($cuentas) {

            // organizamos la matriz de las cuentas
            foreach ($cuentas as $cuenta) {
                $this->det_cuentas[$cuenta['id']]['nombre']  = $cuenta['nombre']; // con lo que empezó
                $this->det_cuentas[$cuenta['id']]['inicio']  = 0; // con lo que empezó
                $this->det_cuentas[$cuenta['id']]['ingreso'] = 0; // lo que le ingresó
                $this->det_cuentas[$cuenta['id']]['egreso']  = 0; // lo que salió
                $this->det_cuentas[$cuenta['id']]['cierre']  = 0; // con lo que terminó
            }

            // el inicio de la cuenta es el total cierre del ultimo cierre de esa cuenta
            if (isset($ult_cierre['detalles']) && $ult_cierre['detalles']) {
                foreach ($ult_cierre['detalles'] as $det_cierre) {
                    $this->det_cuentas[$det_cierre['cuenta_id']]['inicio'] = $det_cierre['total_cierre'];
                }
            }

            // organizamos los movimientos por cuentas
            foreach ($movimientos as $movimiento) {
                // las ventas a crédito no entran enseguida a las cuentas
                if( $movimiento->cuenta_id > 0 ){ // veta de contado
                    if ($movimiento->tipo == 'ingreso') { // suma
                        $this->det_cuentas[$movimiento->cuenta_id]['ingreso'] += $movimiento->monto;
                    } else { // resta
                        $this->det_cuentas[$movimiento->cuenta_id]['egreso']  += $movimiento->monto;
                    }
                }
            }

            // calculamos el cierre por cuenta
            foreach ($this->det_cuentas as $key => $cuenta) {
                $this->det_cuentas[$key]['cierre']  = $cuenta['inicio'] + ($cuenta['ingreso'] - $cuenta['egreso']);
            }
        }

        // organizamos los totales

        // calcular ventas con la vuelta rara de devolución saldo
        $ventas_positivas       = $movimientos->whereNotNull('venta_id')->where('tipo', 'ingreso')->where('cuenta_id', '>', '0')->whereNull('credito_id')->sum('monto');
        $ventas_negativas       = $movimientos->whereNotNull('venta_id')->where('tipo', 'egreso')->where('cuenta_id', '>', '0')->whereNull('credito_id')->sum('monto');
        $this->total_ventas     = $ventas_positivas - $ventas_negativas;
        $this->total_creditos   = $movimientos->whereNotNull('venta_id')->where('tipo', 'ingreso')->where('cuenta_id', '=', '0')->whereNull('credito_id')->sum('monto');
        $this->abonos_creditos   = $movimientos->where('tipo', 'ingreso')->where('credito_id', '>', '0')->sum('monto');

        // calcular ventas normales
        // $this->total_ventas     = $movimientos->whereNotNull('venta_id')->sum('monto');

        $this->total_compras    = $movimientos->whereNotNull('compra_id')->sum('monto'); // compras
        $this->total_egresos    = $movimientos->whereNull('venta_id')->whereNull('compra_id')->where('tipo', 'egreso')->sum('monto'); // egresos manuales
        $this->total_ingresos   = $movimientos->whereNull('venta_id')->whereNull('compra_id')->whereNull('credito_id')->where('tipo', 'ingreso')->sum('monto'); // ingresos manuales

        $this->total_cierre     = $this->total_inicio + $this->total_ventas + $this->abonos_creditos - $this->total_compras - $this->total_egresos + $this->total_ingresos;
    }

    // ejecuta el cierre de caja hasta ese momento
    public function cierreCaja()
    {

        $this->skipRender(); // Evita que el componente se renderice nuevamente

        $ult_compra     = Compra::orderBy('id', 'desc')->first();
        $ult_venta      = Venta::orderBy('id', 'desc')->first();
        $ult_movimiento = Movimiento::orderBy('id', 'desc')->first();
        // dd($ult_compra->id ,$ult_venta,$ult_movimiento->id);

        // creamos el cierre de caja
        $cierre = ModelCierreCaja::create([
            'user_id'        => Auth::id(),
            'fecha'          => date('Y-m-d H:i'),
            'ult_compra'     => $ult_compra ? $ult_compra->id : 0,
            'ult_venta'      => $ult_venta ? $ult_venta->id : 0,
            'ult_movimiento' => $ult_movimiento ? $ult_movimiento->id : 0,
            'total_inicio'   => $this->total_inicio,
            'total_cierre'   => $this->total_cierre,
            'total_ventas'   => $this->total_ventas,
            'total_compras'  => $this->total_compras,
            'total_egresos'  => $this->total_egresos,
            'total_ingresos' => $this->total_ingresos,
            'total_creditos' => $this->total_creditos,
            'abonos_creditos'=> $this->abonos_creditos,
        ]);

        if (isset($cierre->id)) {

            // guardamos los detalles de cierre por cada cuenta
            foreach ($this->det_cuentas as $cuenta_id => $det) {
                $det = DetCierreCaja::create([
                    'cierre_id'     => $cierre->id,
                    'cuenta_id'     => $cuenta_id,
                    'total_inicio'  => $det['inicio'],
                    'total_cierre'  => $det['cierre'],
                    'total_ingresos' => $det['ingreso'],
                    'total_egresos' => $det['egreso'],
                ]);
            }

            $superAdmin = User::whereHas('roles', function ($query) {
                $query->where('name', 'SuperAdmin');
            })->first();

            if ($superAdmin) {
                $superAdmin->notify(new CierreCajaNotificacion($cierre->toArray()));
            }

            $this->getUltCierre();

            // bloqueamos el edit y delete, de ventas, compras y movimientos, hechos hasta este momento
            if ($ult_venta) {
                Venta::where('id', '<=', $ult_venta->id)->update(['block' => 1]);
            }
            Compra::where('id', '<=', $ult_compra->id)->update(['block' => 1]);
            Movimiento::where('id', '<=', $ult_movimiento->id)->update(['block' => 1]);

            // retornamos el nuevo cierre para agregarlo a la tabla
            return ModelCierreCaja::where('id', $cierre->id)->with('usuario', 'detalles.cuenta')->first()->toArray();
        }

        return false;
    }

    public function corregirCierres(){
        $cierres = ModelCierreCaja::orderBy('id', 'asc')->get();

        // borramos los detalles anteriores
        DetCierreCaja::where('id','>', '0')->delete();

        foreach( $cierres  as $cierre ){

            $total_inicio = 0;
            $total_cierre = 0;
            $total_ventas = 0;
            $total_compras = 0;
            $total_egresos = 0;
            $total_ingresos = 0;

            $det_cuentas = [];

            // consultamos el cierre que le sigue
            $ult_cierre         = ModelCierreCaja::where('id', '<', $cierre->id )->with('detalles')->orderBy('id', 'desc')->first();
            $cierre_siguiente   = ModelCierreCaja::where('id', '>', $cierre->id )->orderBy('id', 'asc')->first();

            // para saber hasta donde contó el último cierre de caja
            $ult_venta      = 0;
            $ult_compra     = 0;
            $ult_movimiento = 0;

            // si ya existe un cierre, tomamos como base los últimos IDs procesados
            if (isset($ult_cierre->id)) {

                $ult_cierre = $ult_cierre->toArray();

                $ult_venta          = $ult_cierre['ult_venta'];
                $ult_compra         = $ult_cierre['ult_compra'];
                $ult_movimiento     = $ult_cierre['ult_movimiento'];

                // si ya había un ultimo cierre, el inicio del nuevo es el fin del ultimo
                $total_inicio = $ult_cierre['total_cierre'];

                // consultamos los movimientos generados después del ultimo cierre
                $movimientos    = Movimiento::where('id', '>', $ult_cierre['ult_movimiento'])->where('id', '<=', $cierre->ult_movimiento)->get();

            }else{

                // consultamos los movimientos generados después del ultimo cierre
                $movimientos    = Movimiento::where('id', '<=', $cierre->ult_movimiento)->get();

            }

            // organizamos el cierre por cada cuenta
            $cuentas = Cuenta::select('id', 'nombre')->where('status', 1)->get()->toArray();
            if ($cuentas) {

                // organizamos la matriz de las cuentas
                foreach ($cuentas as $cuenta) {
                    $det_cuentas[$cuenta['id']]['nombre']  = $cuenta['nombre']; // con lo que empezó
                    $det_cuentas[$cuenta['id']]['inicio']  = 0; // con lo que empezó
                    $det_cuentas[$cuenta['id']]['ingreso'] = 0; // lo que le ingresó
                    $det_cuentas[$cuenta['id']]['egreso']  = 0; // lo que salió
                    $det_cuentas[$cuenta['id']]['cierre']  = 0; // con lo que terminó
                }

                // el inicio de la cuenta es el total cierre del ultimo cierre de esa cuenta
                if (isset($ult_cierre['detalles']) && $ult_cierre['detalles']) {
                    foreach ($ult_cierre['detalles'] as $det_cierre) {
                        $det_cuentas[$det_cierre['cuenta_id']]['inicio'] = $det_cierre['total_cierre'];
                    }
                }

                // organizamos los movimientos por cuentas
                foreach ($movimientos as $movimiento) {
                    if ($movimiento->tipo == 'ingreso') { // suma
                        $det_cuentas[$movimiento->cuenta_id]['ingreso'] += $movimiento->monto;
                    } else { // resta
                        $det_cuentas[$movimiento->cuenta_id]['egreso']  += $movimiento->monto;
                    }
                }

                // calculamos el cierre por cuenta
                foreach ($this->det_cuentas as $key => $cuenta) {
                    $det_cuentas[$key]['cierre']  = $cuenta['inicio'] + ($cuenta['ingreso'] - $cuenta['egreso']);
                }
            }

            // organizamos los totales

            // calcular ventas con la vuelta rara de devolución saldo
            $ventas_positivas       = $movimientos->whereNotNull('venta_id')->where('tipo', 'ingreso')->sum('monto');
            $ventas_negativas       = $movimientos->whereNotNull('venta_id')->where('tipo', 'egreso')->sum('monto');
            $total_ventas     = $ventas_positivas - $ventas_negativas;

            $total_compras    = $movimientos->whereNotNull('compra_id')->sum('monto');
            $total_egresos    = $movimientos->whereNull('venta_id')->whereNull('compra_id')->where('tipo', 'egreso')->sum('monto');
            $total_ingresos   = $movimientos->whereNull('venta_id')->whereNull('compra_id')->where('tipo', 'ingreso')->sum('monto');

            $total_cierre     = $total_inicio + $total_ventas - $total_compras - $total_egresos + $total_ingresos;


            $cierre->total_inicio   = $total_inicio;
            $cierre->total_cierre   = $total_cierre;
            $cierre->total_ventas   = $total_ventas;
            $cierre->total_compras  = $total_compras;
            $cierre->total_egresos  = $total_egresos;
            $cierre->total_ingresos = $total_ingresos;

            // guardamos los detalles de cierre por cada cuenta
            foreach ($det_cuentas as $cuenta_id => $det) {
                $det = DetCierreCaja::create([
                    'cierre_id'     => $cierre->id,
                    'cuenta_id'     => $cuenta_id,
                    'total_inicio'  => $det['inicio'],
                    'total_cierre'  => $det['cierre'],
                    'total_ingresos' => $det['ingreso'],
                    'total_egresos' => $det['egreso'],
                ]);
            }

            $cierre->save();

        }
        // dd( $cierres );
    }
}
