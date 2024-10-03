<?php

namespace App\Livewire\Compras;

use Livewire\Component;

use App\Models\Compra;
use App\Models\Cuenta;
use App\Models\DetCompra;
use App\Models\Movimiento;
use App\Models\Proveedores as Proveedor;

class Compras extends Component
{
    public $proveedores = [], $cuentas = [];
    public $desde, $hasta, $proveedor_id = 0, $cuenta_id = 0;

    public function mount(){
        $this->proveedores  = Proveedor::where( 'status', 1 )->get()->toArray();
        $this->cuentas      = Cuenta::where( 'status', 1 )->get()->toArray();

        if( !$this->desde && !$this->hasta ){
            $this->desde = date('Y-m-d', strtotime( date('Y-m-d') . ' - 1 month' ));
            $this->hasta = date('Y-m-d');
        }
    }

    public function render()
    {
        return view('livewire.compras.compras')->title('Compras');
    }

    public function getTabla(){
        
        $this->skipRender(); 

        if( $this->desde && $this->hasta ){ 
            $compras = Compra::where('fecha' , '>=', $this->desde)->where('fecha', '<=', $this->hasta)->with('proveedor', 'usuario', 'cuenta', 'detalles.producto')->get();
        }else{
            $compras = Compra::with('proveedor', 'usuario', 'cuenta', 'detalles.producto')->get();
        }

        if( $this->proveedor_id ){
            $compras = $compras->where('proveedor_id', $this->proveedor_id);
        }

        if( $this->cuenta_id ){
            $compras = $compras->where('cuenta_id', $this->cuenta_id);
        }

        $compras = $compras->toArray();

        $compras_res = [];

        // revisamos si se puede eliminar la compra
        foreach( $compras as $key => $det ){
            $det[ 'puede_eliminar' ] = true;
            foreach( $det['detalles'] as $d ){
                // si el stock no coincide con el stock_compra, es porque ya se vendió o se ajustó uno de sus detalles
                if( $d['stock'] != $d['stock_compra'] ){
                    $det[ 'puede_eliminar' ] = false;
                    break;
                }
            }

            $compras_res[] = $det;
        }

        return $compras_res;
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

        $compra = Compra::find($id);

        if ($compra) {

            DetCompra::where('compra_id', $id)->delete();

            Movimiento::where('compra_id', $id)->delete();

            $compra->delete();

        }
        return true;
    }
}
