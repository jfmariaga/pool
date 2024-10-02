<?php

namespace App\Livewire\Movimientos;

use App\Models\Movimiento;
use App\Models\Cuenta;
use App\Traits\General;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Movimientos extends Component
{
    use General;

    public $movimientos = [];
    public $cuentas = [];
    public $cuenta_id, $tipo, $valor, $fecha, $descripcion, $movimiento_id;

    public function mount()
    {
        $this->cuentas = Cuenta::where('status', 1)->get();
        $this->fecha   = now()->format('d-m-Y');
        $this->tipo    = 'ingreso';
    }

    public function render()
    {
        return view('livewire.movimientos.movimientos')->title('Gestión de Movimientos');
    }

    public function getMovimientos()
    {
        $this->movimientos = Movimiento::with(['cuenta', 'usuario'])->get();
        return $this->movimientos;
    }

    public function save()
    {
        // dd($this->tipo);
        $valorLimpio = $this->limpiarNum($this->valor);
        $newDate = date("Y-m-d", strtotime($this->fecha));
        $this->validate([
            'cuenta_id' => 'required|exists:cuentas,id',
            'tipo' => 'required',
            'valor' => 'required',
            'fecha' => 'required',
        ]);


        if ($this->movimiento_id) {
            $movimiento = Movimiento::find($this->movimiento_id);
            $cuentaAnterior = Cuenta::find($movimiento->cuenta_id);
            // Revertir el impacto del movimiento anterior en el saldo de la cuenta
            if ($movimiento->tipo == 'ingreso') {
                // Si fue un ingreso, restamos el valor anterior para "deshacer" ese impacto
                $cuentaAnterior->saldo -= $movimiento->monto;
                // dd($cuenta->saldo);
            } else {
                // Si fue un egreso, sumamos el valor anterior para "deshacer" ese impacto
                $cuentaAnterior->saldo += $movimiento->monto;
            }

            $cuentaAnterior->save();
            // dd($cuenta->saldo);

            $movimiento->update([
                'cuenta_id' => $this->cuenta_id,
                'tipo'      => $this->tipo,
                'monto'     => $valorLimpio,
                'fecha'     => $newDate,
                'usuario_id' => Auth::id(),
            ]);
        } else {
            $movimiento =  Movimiento::create([
                'cuenta_id' => $this->cuenta_id,
                'tipo'      => $this->tipo,
                'monto'     => $valorLimpio,
                'fecha'     => $newDate,
                'usuario_id' => Auth::id(),
            ]);
        }

        // Actualizar el saldo de la cuenta según el tipo de movimiento

        // Actualizar el saldo de la cuenta nueva
        $cuentaNueva = Cuenta::find($this->cuenta_id);

        if ($this->tipo == 'ingreso') {
            $cuentaNueva->saldo += $movimiento->monto;
        } else {
            $cuentaNueva->saldo -= $movimiento->monto;
        }

        $cuentaNueva->save();

        if ($movimiento) {
            $movimiento->load(['cuenta', 'usuario']);
            $this->reset();
            return $movimiento->toArray();
        } else {
            return false;
        }
    }

    // public function deleteMovimiento($id)
    // {
    //     $movimiento = Movimiento::find($id);

    //     if ($movimiento) {
    //         $movimiento->delete();
    //         $this->getMovimientos();
    //     }
    // }

    public function resetForm()
    {
        $this->reset(['cuenta_id', 'tipo', 'valor', 'fecha', 'descripcion', 'movimiento_id']);
        $this->resetValidation();
    }
}
