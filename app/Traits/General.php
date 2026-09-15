<?php
namespace App\Traits;

use Illuminate\Support\Facades\Auth;

use App\User;
use App\Models\Role;

trait General {

    public function limpiarNum( $val ){
        if( $val ){
            $val = str_replace('$', '', $val);
            $val = str_replace(' ', '', $val);
            $val = str_replace('.', '', $val);
            $val = str_replace(',', '', $val);
            return $val != '' ? $val : 0;
        }else{
            return 0;
        }
    }

    // este no quita el punto por los decimales
    public function __limpiarNumDecimales( $val ){
        if( $val ){
            $val = str_replace('$', '', $val);
            $val = str_replace(' ', '', $val);
            $val = str_replace(',', '', $val);
            return $val != '' ? $val : 0;
        }else{
            return 0;
        }
    }

    public function formatFecha( $fecha ){
        $fecha = strtotime($fecha);
        $dia = date('d', $fecha);
        $mes = date('F', $fecha);
        $meses_ES = array("Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic");
        $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        return date('d', $fecha).' de '.str_replace($meses_EN, $meses_ES, $mes).' '.date('Y', $fecha);
    }

    public function formatFechaFlujo( $fecha ){
        $fecha = strtotime($fecha);
        $dia = date('d', $fecha);
        $mes = date('F', $fecha);
        $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
        $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        return str_replace($meses_EN, $meses_ES, $mes).' '.date('Y', $fecha);
    }

    public function getDiaFecha( $fecha ){
        $fecha = strtotime($fecha);
        $dia = date('D', $fecha);
        $dia_ES = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
        $dia_EN = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
        return str_replace($dia_EN, $dia_ES, $dia);
    }

    public function getMesAnoStr( $fecha ){
        $fecha  = strtotime($fecha);
        $mes    = date('F', $fecha);
        $ano    = date('Y', $fecha);
        $meses_ES = array("Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic");
        $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        return str_replace($meses_EN, $meses_ES, $mes).' '.$ano;
        // return str_replace($meses_EN, $meses_ES, $mes).' '.date('Y', $fecha);
    }

    // verifica si el usuario logueado tiene un permiso para mostrar o ejecutar algunas acciones
    public function checkPermisos( $permisos_validate ){


        if( isset( Auth::user()->rol->permisos ) ){

            $rol = Auth::user()->rol;


            if( $rol->id === 1 ){ // El master siempre tiene acceso a todo

                return true;

            }else{

                $permisos_usuario = json_decode( $rol->permisos, true );

                $continue = false;

                // revisamos si alguno de los permisos enviados, esta asignado a los permisos del usuario
                if( is_array( $permisos_validate ) ){
                    foreach( $permisos_validate as $validate ){
                        if( in_array( $validate, $permisos_usuario ) ){
                            $continue = true;
                        }
                    }
                }

                if ( $continue ) {
                    return true;
                }else{
                    return false;
                }

            }


        }else{

            return false;

        }

        // $role =  Auth::user()->rol->nombre;
        // if( isset( $role ) ){
        //     if ( in_array($role, $roles_permitidos)) {
        //         return true;
        //     }else{
        //         return false;
        //     }
        // }else{
        //     return false;
        // }
    }

    public function getMonth( $month ){
        switch ($month) {
            case '1':
                return 'Enero';
                break;
            case '2':
                return 'Febrero';
                break;
            case '3':
                return 'Marzo';
                break;
            case '4':
                return 'Abril';
                break;
            case '5':
                return 'Mayo';
                break;
            case '6':
                return 'Junio';
                break;
            case '7':
                return 'Julio';
                break;
            case '8':
                return 'Agosto';
                break;
            case '9':
                return 'Septiembre';
                break;
            case '10':
                return 'Octubre';
                break;
            case '11':
                return 'Noviembre';
                break;
            case '12':
                return 'Diciembre';
                break;
        }
    }

}
