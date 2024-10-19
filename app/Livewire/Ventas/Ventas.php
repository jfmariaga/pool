<?php

namespace App\Livewire\Ventas;

use Livewire\Component;

use App\Models\Venta;

class Ventas extends Component
{
    public function render()
    {
        return view('livewire.ventas.ventas');
    }

    public function getTabla(){

        $this->skipRender();

        // por ahora se trae, todo luego se debe optimizar
        $ventas = Venta::with('detVentas.producto','usuario','cuenta')->get()->toArray();
        // dd($ventas);
        return $ventas;
    }

    public function delete( $id ){
        $this->skipRender(); // evita el render

        // $compra = Compra::find($id);

        // if ($compra) {

        //     DetCompra::where('compra_id', $id)->delete();

        //     Movimiento::where('compra_id', $id)->delete();

        //     $compra->delete();

        // }
        // return true;
    }
}
