<?php

namespace App\Livewire\Compras;

use Livewire\Component;

use App\Models\Compra;
use App\Models\Cuenta;
use App\Models\DetCompra;
use App\Models\Movimiento;

class Compras extends Component
{
    public function render()
    {
        return view('livewire.compras.compras')->title('Compras');
    }

    public function getTabla(){
        $compras = Compra::with('proveedor', 'usuario', 'cuenta', 'detalles.producto')->get()->toArray();

        // revisamos si se puede eliminar la compra
        foreach( $compras as $key => $det ){
            $compras[ $key ][ 'puede_eliminar' ] = true;
            foreach( $det['detalles'] as $d ){
                if( $d['stock'] != $d['stock_compra'] ){
                    $compras[ $key ][ 'puede_eliminar' ] = false;
                    break;
                }
            }
        }
        // dd( $compras );
        return $compras;
    }

    public function puede_eliminar()
    {
        $detalles = $this->detalles();
        foreach( $detalles as $d ){
            if( $d->stock != $stock_compra ){
                return false;
            }
        }

        return true;
    }

    public function eliminarCompra( $id ){
        $this->skipRender(); // evita el render
        // DetCompra::where('compra_id', $id )->delete();
        // Compra::find( $id )->delete();
        $compra = Compra::find($id);

        if ($compra) {
            $cuenta = Cuenta::find($compra->cuenta_id);

            // Devolver el total de la compra al saldo de la cuenta
            $cuenta->saldo += $compra->total;
            $cuenta->save();

            DetCompra::where('compra_id', $id)->delete();

            Movimiento::where('compra_id', $id)->delete();

            $compra->delete();

        }
        return true;
    }
}
