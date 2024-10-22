<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Roles extends Component
{
    public $name;
    public $permissions = [];
    public $role_id;
    public $allPermissions;
    public $selectedPermissions = [];

    // Reglas de validación
    protected $rules = [
        'name' => 'required|string|max:255|unique:roles,name',
        'selectedPermissions' => 'array',
    ];

    // Cargar todos los permisos
    public function mount()
    {
        $this->allPermissions = Permission::all();
    }

    // Cargar los roles
    public function cargarRoles()
    {
        return Role::with('permissions')->get()->toArray();
    }

    // Editar un rol existente
    // public function editRole($id)
    // {
    //     $role = Role::findById($id);
    //     $this->role_id = $role->id;
    //     $this->name = $role->name;
    //     $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
    // }

    // Guardar o actualizar el rol
    public function submit()
    {
        // Si estamos editando un rol, ignoramos el rol actual en la validación de unicidad
        if ($this->role_id) {
            $this->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $this->role_id,
                'selectedPermissions' => 'array',
            ]);

            // Actualizar el rol existente
            $role = Role::findById($this->role_id);
            $role->name = $this->name;
            $role->syncPermissions($this->selectedPermissions);
        } else {
            // Para creación, simplemente validamos como antes
            $this->validate([
                'name' => 'required|string|max:255|unique:roles,name',
                'selectedPermissions' => 'array',
            ]);

            // Crear un nuevo rol
            $role = Role::create(['name' => $this->name]);
            $role->syncPermissions($this->selectedPermissions);
        }

        if ($role) {
            $role->load('permissions'); // Cargar los permisos
            $this->resetForm();
            return $role->toArray();
        } else {
            return false;
        }
    }

    public function deleteRole($id)
    {
        // Buscar el rol por ID y eliminarlo
        $role = Role::findById($id);

        if ($role) {
            $role->delete();
            return true;
        }

        return false;
    }


    // Restablecer el formulario después de guardar
    public function resetForm()
    {
        $this->name = '';
        $this->selectedPermissions = [];
        $this->role_id = null;
    }

    public function render()
    {
        return view('livewire.roles.roles')->title('Roles');
    }
}
