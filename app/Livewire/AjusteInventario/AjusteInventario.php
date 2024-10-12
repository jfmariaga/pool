<?php

namespace App\Livewire\AjusteInventario;

use Livewire\Component;

use App\Models\AjusteInventario as Ajuste;
use App\Models\DetAjusteInventario as DetAjuste;

class AjusteInventario extends Component
{

    public $desde, $hasta;

    public function mount(){

        if( !$this->desde && !$this->hasta ){
            $this->desde = date('Y-m-d', strtotime( date('Y-m-d') . ' - 1 month' ));
            $this->hasta = date('Y-m-d');
        }
    }

    public function render()
    {
        return view('livewire.ajuste-inventario.ajuste-inventario');
    }

    public function getTabla(){
        
        $this->skipRender(); 
        $ajustes = ajuste::where('fecha', '>=', $this->desde)->where('fecha', '<=', $this->hasta)->with('usuario', 'detalles.producto')->get()->toArray();
        return $ajustes;
    }

    public function eliminarAjuste( $id ){
        $this->skipRender(); // evita el render

        $ajuste = Ajuste::find($id);

        if ($ajuste) {

            // hay que devolver las cantidades ajustadas

            DetAjuste::where('ajuste_id', $id)->delete();

            $ajuste->delete();

        }
        return true;
    }
}
