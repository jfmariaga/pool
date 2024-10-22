<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class Usuarios extends Component
{
    use WithFileUploads;

    public $users = [];
    public $roles;
    public $user_id, $status, $document, $name, $last_name, $email, $phone, $user_name, $password, $picture, $role_id, $change_picture = false;

    public function mount()
    {
        // Cargamos los roles disponibles
        $this->roles = Role::all();
    }

    public function render()
    {
        return view('livewire.usuarios.usuarios')->title('Usuarios');
    }

    public function selectedRole($role_id)
    {
        // Asignamos el rol seleccionado
        $this->role_id = $role_id;
    }

    public function getUsers()
    {
        $this->skipRender();
        // Traemos los usuarios con sus roles
        return User::with('roles')->get();
    }

    public function save()
    {
        // Determinar si la contraseña es requerida o no
        $required_password = $this->user_id ? '' : 'required|min:8';

        // Validar los campos
        $this->validate([
            'document'  => 'required',
            'name'      => 'required',
            'last_name' => 'required',
            'user_name' => 'required|min:8',
            'email'     => 'required|email|unique:users,email,' . $this->user_id,
            'password'  => $this->user_id
                ? 'nullable|min:8|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/|regex:/[\W_]/'
                : 'required|min:8|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/|regex:/[\W_]/',
            'role_id'   => 'required', // Validamos que el rol esté seleccionado
        ]);

        $role = Role::findById($this->role_id);

        // Procesar la imagen si se cambió
        if ($this->picture && $this->change_picture) {
            $name_picture = $this->processImage($this->picture);
        } else {
            $name_picture = $this->user_id ? User::find($this->user_id)->picture : 'default.png';
        }

        if ($this->user_id) {
            // Actualizar usuario existente
            $user = User::find($this->user_id);
            if ($user) {
                if ($this->change_picture && $user->picture) {
                    Storage::disk('public')->delete('avatars/' . $user->picture);
                }
                // Crear un array con los datos a actualizar
                $dataToUpdate = [
                    'document'  => $this->document,
                    'name'      => $this->name,
                    'last_name' => $this->last_name,
                    'user_name' => $this->user_name,
                    'email'     => $this->email,
                    'phone'     => $this->phone,
                    'status'    => $this->status ?? 1,
                    'picture'   => $name_picture ?? $user->picture,
                ];

                // Solo actualizar la contraseña si se ha proporcionado una nueva
                if (!empty($this->password)) {
                    $dataToUpdate['password'] = bcrypt($this->password);
                }

                $user->update($dataToUpdate);

                // Sincronizar roles
                $user->syncRoles($role);
            }
        } else {
            // Crear nuevo usuario
            $user = User::create([
                'document'  => $this->document,
                'name'      => $this->name,
                'last_name' => $this->last_name,
                'user_name' => $this->user_name,
                'password'  => bcrypt($this->password),
                'email'     => $this->email,
                'phone'     => $this->phone,
                'picture'   => $name_picture,
                'status'    => 1,
            ]);

            // Asignar rol al usuario
            $user->assignRole($role);
        }

        // Si el usuario se creó o actualizó correctamente, restablecemos el formulario
        if ($user) {
            $user->load(['roles']);
            $this->reset();
            $this->mount();
            return $user->toArray();
        } else {
            return false;
        }
    }

    private function processImage($base64_image)
    {
        $image_parts = explode(";base64,", $base64_image);
        $image_type_aux = explode("image/", $image_parts[0]);
        $ext = $image_type_aux[1];
        $imagen = base64_decode($image_parts[1]);
        $rand = date('Ymdhs') . Str::random(5);
        $name_picture = 'avatar-' . $rand . '.' . $ext;
        $img = Image::make($imagen)->widen(100)->encode($ext);
        Storage::disk('public')->put('avatars/' . $name_picture, $img);

        return $name_picture;
    }

    public function desactivarUser($user_id)
    {
        $user = User::find($user_id);
        if ($user) {
            $user->update(['status' => 0]);
            $user->load(['roles']);
            $this->mount();
            return $user->toArray();
        } else {
            return false;
        }
    }

    public function limpiar()
    {
        $this->reset();
        $this->resetValidation();
    }
}
