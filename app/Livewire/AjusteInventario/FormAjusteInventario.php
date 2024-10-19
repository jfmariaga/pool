<?php

namespace App\Livewire\AjusteInventario;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\DetCompra;

use App\Models\AjusteInventario;
use App\Models\DetAjusteInventario;

use App\Traits\General;

class FormAjusteInventario extends Component
{
    use General;

    public $ajuste_id;
    public $categorias = [];

    public $detalles    = []; // lista con los prod que se van agregando

    public function mount($ajuste_id = null)
    {
        $this->ajuste_id = $ajuste_id;

        // si viene es porque estamos editando
        if ($this->ajuste_id) {
            $ajuste = AjusteInventario::where('id', $this->ajuste_id)->with('detalles.producto')->first()->toArray();
            // dd( $ajuste );
            if (isset($ajuste['id']) && $ajuste['id']) {

                foreach ($ajuste['detalles'] as $det) {
                    $this->detalles[] = [
                        'id'                => $det['producto_id'],
                        'nombre'            => $det['producto']['nombre'],
                        'imagenes'          => $det['producto']['imagenes'],
                        'stock_sistema'     => $det['cant_sistema'],
                        'stock_real'        => $det['cant_real'],
                    ];
                }
                // dd( $this->detalles );
            }
        }

        $this->categorias  = Categoria::where('status', 1)->get();
    }

    public function render()
    {
        return view('livewire.ajuste-inventario.form-ajuste-inventario')->title('Formulario Ajuste Inventario');
    }

    public function getProductos()
    {
        // $productos = Producto::with('categoria')->get()->toArray();
        // return $productos;

        $productos = Producto::with('categoria')->get(); // Traemos los productos con su relación de categoría

        // consultamos el stock disponible del producto
        foreach( $productos as $key => $producto ){
            $productos[$key]->stock = $producto->stocks->sum('stock');
            unset( $productos[$key]->stocks ); // pa no generar peso el en res, ya que esto puede crecer mucho
        }

        $productos = $productos->toArray();
        return $productos;
    }

    public function guardar(){

        if (!$this->ajuste_id) { // crear

            $ajuste = AjusteInventario::create([
                'fecha'                 => date('Y-m-d'),
                'user_id'               => Auth::id(),
            ]);

        } else { // editar

            // devolvemos lo que se ajusto antes
            // ya que se cargara de nuevo
            $this->devolverAjuste();

            $ajuste = AjusteInventario::find($this->ajuste_id);

        }

        if (isset($ajuste->id)) {

            $ajuste->cantidades_positivas   = 0;
            $ajuste->cantidades_negativas   = 0;
            $ajuste->count_productos        = 0;

            // agregamos los detalles
            foreach ($this->detalles as $det) {

                // evidenciamos si el ajuste el positivo o negativo
                $cant_ajuste = $det['stock_real'] - $det['stock_sistema'];

                // consultamos la ultima compra del producto
                $ultima_compra = DetCompra::where('producto_id', $det['id'])->orderBy('id', 'desc')->first();

                // si no existe ultima compra la creamos con precio de compra 0
                if( isset( $ultima_compra->id ) ){

                    // ajustamos la cantidad
                    $ultima_compra->stock += $cant_ajuste;

                    $ultima_compra->save();

                }else{
                    
                    $ultima_compra = DetCompra::create([
                        'compra_id'     => 0,
                        'producto_id'   => $det['id'],
                        'stock'         => $det['stock_real'],
                        'stock_compra'  => $det['stock_real'],
                        'precio_compra' => 0,
                    ]);

                }
                
                if( $cant_ajuste < 0 ){ // ajuste negativo

                    $ajuste->cantidades_negativas += abs( $cant_ajuste );
                    
                }else{ // ajuste positivo
                    
                    $ajuste->cantidades_positivas += $cant_ajuste;

                }

                // creamos el detalle de ajuste
                $det_ajuste = DetAjusteInventario::create([
                    'ajuste_id'         => $ajuste->id,
                    'producto_id'       => $det['id'],
                    'det_compra_id'     => $ultima_compra->id,
                    'cant_sistema'      => $det['stock_sistema'],
                    'cant_real'         => $det['stock_real'],
                    'cant_ajustada'     => $cant_ajuste,
                ]);

                $ajuste->count_productos++;

            }

            // para guardar el total
            $ajuste->save();

            $this->dispatch('formOK');
        }
    }

    public function devolverAjuste(){
        $ajuste = AjusteInventario::where('id', $this->ajuste_id)->with('detalles')->first();

        if ( isset($ajuste->id) && $ajuste->id ) {

            foreach ($ajuste->detalles as $det) {

                // obetenemos el stock que se habia modificado
                $det_compra = DetCompra::where('id', $det->det_compra_id )->first();

                if( isset( $det_compra->id ) ){
   
                    if( $det->cant_ajustada < 0 ){ // si era negativo lo sumamos al stock nuevamente

                        $det_compra->stock += abs( $det->cant_ajustada );
                        
                    }else{ // si era positivo lo restamos

                        $det_compra->stock -= $det->cant_ajustada;
    
                    }

                    $det_compra->save();

                }

                // borramos el detalle ya que se cargarán los nuevo
                $det->delete();                

            }

        }
    }
}
