<?php

namespace App\Livewire\Cuentas;

use Livewire\Component;
use App\Models\Cuenta;
use App\Models\Movimiento;

class Cuentas extends Component
{
    public $cuentas = [];
    public $nombre, $numero_de_cuenta, $status, $cuenta_id, $saldo;


    public function render()
    {
        return view('livewire.cuentas.cuentas')->title('Cuentas');
    }

    public function getCuentas(){
        $this->skipRender();
        $cuentas = Cuenta::all()->toArray();
        foreach( $cuentas as $key => $c ){
            $ingresos = Movimiento::where('cuenta_id', $c['id'])->where('tipo', 'ingreso')->sum('monto');
            $egresos  = Movimiento::where('cuenta_id', $c['id'])->where('tipo', 'egreso')->sum('monto');
            $cuentas[ $key ]['saldo'] = $ingresos - $egresos;
        }
        return $cuentas;
    }

    public function save()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'numero_de_cuenta' => [
                'nullable',
                'regex:/^[0-9\-]+$/', // Solo números y guiones
                'unique:cuentas,numero_de_cuenta,' . $this->cuenta_id,
            ],
        ]);

        if( $this->cuenta_id ){
            $cuenta = Cuenta::find( $this->cuenta_id );
            if( isset( $cuenta->id ) ){
                $cuenta->nombre = $this->nombre;
                $cuenta->numero_de_cuenta = $this->numero_de_cuenta;
                $cuenta->status = $this->status;
                $cuenta->save();
            }
        }else{
            $cuenta = Cuenta::create([
                'nombre' => $this->nombre,
                'numero_de_cuenta' => $this->numero_de_cuenta,
                'saldo' => 0,
                'status' => 1,
            ]);
        }

        if( $cuenta ){
            $this->reset();
            return $cuenta->toArray();
        }else{
            return false;
        }
    }

    public function inactivarCuenta($cuenta_id){
        $cuenta = Cuenta::find($cuenta_id);
        if ($cuenta) {
            $cuenta->update(['status' => 0]);
             return $cuenta->toArray();
        }else{
            return false;
        }

    }

    public function limpiar(){
        $this->reset();
        $this->resetValidation();
    }

}
