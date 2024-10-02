<?php

namespace App\Livewire\Proveedores;

use App\Models\Proveedores as ModelsProveedores;
use Livewire\Component;

class Proveedores extends Component
{
    public $proveedores = [];
    public $proveedor_id;
    public $nit, $nombre, $direccion, $telefono, $correo, $contacto, $status;

    public function getProveedores()
    {
        $this->skipRender(); // evita que el componente se vuelva a renderizar
        $proveedores = ModelsProveedores::where('status', 1)->get();
        return $proveedores;
    }

    public function save()
    {
        $this->validate([
            'nit'     => 'required|unique:proveedores,nit,' . $this->proveedor_id,
            'nombre'  => 'required',
        ]);

        if ($this->proveedor_id) { // update
            $proveedor = ModelsProveedores::find($this->proveedor_id);
            if (isset($proveedor->id)) {

                $proveedor->nit        = $this->nit;
                $proveedor->nombre     = $this->nombre;
                $proveedor->direccion = $this->direccion;
                $proveedor->telefono   = $this->telefono;
                $proveedor->email      = $this->correo;
                $proveedor->contacto   = $this->contacto;
                $proveedor->status     = $this->status;
                $proveedor->save();
            }
        } else { // insert
            $proveedor = ModelsProveedores::create([
                'nit'       => $this->nit,
                'nombre'    => $this->nombre,
                'direccion' => $this->direccion,
                'telefono'  => $this->telefono,
                'email'     => $this->correo,
                'contacto'  => $this->contacto,
                'status'    => 1,
            ]);
        }

        if (isset($proveedor) && $proveedor) {
            $this->reset();
            return $proveedor->toArray();
        } else {
            return false;
        }
    }

    public function deleteProveedor($item_id)
    {
        $proveedor = ModelsProveedores::find($item_id);

        if ($proveedor) {
            $proveedor->update(['status' => 0]);
            return $proveedor->toArray();
        } else {
            return false;
        }
    }

    public function render()
    {
        return view('livewire.proveedores.proveedores')->title('Proveedores');
    }
}
