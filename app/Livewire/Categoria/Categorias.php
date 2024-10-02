<?php

namespace App\Livewire\Categoria;

use App\Models\Categoria;
use Livewire\Component;

class Categorias extends Component
{
    public $categorias = [];
    public $nombre, $descripcion, $categoria_id, $status;


    public function render()
    {
        return view('livewire.categoria.categorias')->title('Categorias');
    }

    public function getCategorias(){
        $this->skipRender(); // evita que el componente se vuelva a renderizar
        $categorias = Categoria::all();
        return $categorias;
    }

    public function save(){
        $this->validate([
            'nombre'  => 'required',
        ]);

        if( $this->categoria_id ){ // update
            $categoria = Categoria::find( $this->categoria_id );
            if( isset( $categoria->id ) ){

                $categoria->nombre      = $this->nombre;
                $categoria->descripcion = $this->descripcion;
                $categoria->status      = $this->status;
                $categoria->save();

            }
        }else{ // insert
            $categoria = Categoria::create([
                'nombre'      => $this->nombre,
                'descripcion' => $this->descripcion ?? '',
                'status' => 1,
            ]);
        }

        if( isset( $categoria ) && $categoria ){
            $this->reset();
            return $categoria->toArray();
        }else{
            return false;
        }
    }

    public function deleteCategoria($item_id){
        $categoria = Categoria::find($item_id);

        if ($categoria) {
            $categoria->update(['status' => 0]);
             return $categoria->toArray();
        }else{
            return false;
        }
    }

    public function limpiar(){
        $this->reset();
        $this->resetValidation();
    }
}
