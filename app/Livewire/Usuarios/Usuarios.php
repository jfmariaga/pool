<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class Usuarios extends Component
{
    use WithFileUploads;

    public $users = [];
    public $user_id, $status, $document, $name, $last_name, $email, $phone, $user_name, $password, $picture, $change_picture = false;

    public function render()
    {
        return view('livewire.usuarios.usuarios')->title('Usuarios');
    }

    // Método para obtener los usuarios
    public function getUsers()
    {
        $this->skipRender(); // Evita que el componente se renderice nuevamente
        return User::all();
    }

    // Método para guardar o actualizar un usuario
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
            'email'=> 'required|email|unique:users,email,' . $this->user_id,
            'password'  => $required_password,
        ]);

        // Procesar la imagen si se cambió
        if ($this->picture && $this->change_picture) {
            $name_picture = $this->processImage($this->picture); // Llama a la función para procesar la imagen
        } else {
            $name_picture = $this->user_id ? User::find($this->user_id)->picture : 'default.png';
        }

        // Si es actualización
        if ($this->user_id) {
            $user = User::find($this->user_id);
            if ($user) {
                // Si se cambió la imagen, eliminamos la anterior
                if ($this->change_picture && $user->picture) {
                    Storage::disk('public')->delete('avatars/' . $user->picture);
                }

                // Actualizamos el usuario con los nuevos datos
                $user->update([
                    'document'     => $this->document,
                    'name'         => $this->name,
                    'last_name'    => $this->last_name,
                    'user_name'    => $this->user_name,
                    'email'        => $this->email,
                    'phone'        => $this->phone,
                    'status'       => $this->status ?? 1,
                    'password'     => $this->password ? bcrypt($this->password) : $user->password,
                    'picture'      => $name_picture ?? $user->picture,
                ]);
            }
        } else {
            // Si es una creación de nuevo usuario
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
        }

        // Si el usuario se creó o actualizó correctamente, restablecemos el formulario
        if ($user) {
            $this->reset();
            return $user->toArray();
        } else {
            return false;
        }
    }

    // Método para procesar la imagen base64
    private function processImage($base64_image)
    {
        // Descomponemos la imagen base64
        $image_parts = explode(";base64,", $base64_image);
        $image_type_aux = explode("image/", $image_parts[0]);
        $ext = $image_type_aux[1];
        $imagen = base64_decode($image_parts[1]);

        // Generamos un nombre único para la imagen
        $rand = date('Ymdhs') . Str::random(5);
        $name_picture = 'avatar-' . $rand . '.' . $ext;

        // Redimensionamos y guardamos la imagen
        $img = Image::make($imagen)->widen(100)->encode($ext);
        Storage::disk('public')->put('avatars/' . $name_picture, $img);

        return $name_picture;
    }

    // Método para desactivar un usuario
    public function desactivarUser($user_id)
    {
        $user = User::find($user_id);
        if ($user) {
            $user->update(['status' => 0]);
             return $user->toArray();
        }else{
            return false;
        }
    }

    public function limpiar(){
        $this->reset();
        $this->resetValidation();
    }
}
