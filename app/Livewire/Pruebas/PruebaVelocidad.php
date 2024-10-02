<?php

namespace App\Livewire\Pruebas;

use Livewire\Component;

class PruebaVelocidad extends Component
{

    public $count = 1;
    public $change = true;
    public $text = '', $input_prueba;
    public $list = ['Orange', 'Blue', 'Green']; // array sencillo
    public $listArray = [ ['nombre'=> 'Juan', 'edad' => '30']]; // array de arrays, falta probar arrays bidimencionales

    public function render()
    {
        $this->change = !$this->change; // solo para ver en el front cuando se ejecuta una llamada con render al servidor
        return view('livewire.pruebas.prueba-velocidad')->layout('components.layouts.login');
    }

    public function increment()
    {
        $this->count++;
    }

    public function decrement()
    {
        $this->count--;
    }

    public function probandoRes(){
        $this->skipRender(); // evita que el componente se vuelva a renderizar
        return 'Hola juan';
    }

    public function save(){
        $this->validate([
            'input_prueba' => 'required',
        ]);
    }
}

// 
