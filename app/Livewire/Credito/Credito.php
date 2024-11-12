<?php

namespace App\Livewire\Credito;

use App\Models\Abono;
use App\Models\Credito as ModelsCredito;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Credito extends Component
{
    public $creditos = [];
    public $usuarios = [];
    public $deudor_id, $responsable_id, $monto, $fecha, $estado = 'pendiente', $credito_id;
    public $abonos = [], $abono_monto, $abono_fecha;
    public $desde, $hasta;
    public $estado_filter, $venta_id, $tipo, $deudor_id_filter;


    public function mount()
    {
        $this->usuarios = User::where('status', 1)->get();
        // $this->fecha = now()->format('Y-m-d');
        if (!$this->desde && !$this->hasta) {
            $this->desde = date('Y-m-d', strtotime(date('Y-m-d') . ' - 1 month'));
            $this->hasta = date('Y-m-d');
        }
    }

    public function getCredito()
    {
        $this->skipRender();
        $creditos = ModelsCredito::query()->with('deudor', 'responsable', 'abonos', 'ventas');

        if ($this->desde && $this->hasta) {
            $creditos->whereBetween('fecha', [$this->desde, $this->hasta]);
        }

        if ($this->deudor_id) {
            $creditos->where('deudor_id', $this->deudor_id);
        }

        if ($this->responsable_id) {
            $creditos->where('responsable_id', $this->responsable_id);
        }

        if ($this->estado_filter && $this->estado_filter !== '0') {
            $creditos->where('estado', $this->estado_filter);
        }
        return $creditos->get();
        // dd($creditos);
    }

    public function saveCredito()
    {
        $this->validate([
            'deudor_id' => 'required|exists:users,id',
            'responsable_id' => 'required|exists:users,id',
            'monto' => 'required|numeric|min:1',
            'fecha' => 'required|date',
        ]);

        $credito = Credito::updateOrCreate(
            ['id' => $this->credito_id],
            [
                'deudor_id' => $this->deudor_id,
                'responsable_id' => $this->responsable_id,
                'monto' => $this->monto,
                'fecha' => $this->fecha,
                'estado' => $this->estado,
            ]
        );

        $this->resetForm();
        $this->getCredito();
    }

    public function saveAbono($credito_id)
    {
        $this->validate([
            'abono_monto' => 'required|numeric|min:1',
            'abono_fecha' => 'required|date',
        ]);

        Abono::create([
            'credito_id' => $credito_id,
            'monto' => $this->abono_monto,
            'fecha' => $this->abono_fecha,
        ]);

        $this->getCredito();
        $this->resetAbonoForm();
    }

    private function resetForm()
    {
        $this->reset(['deudor_id', 'responsable_id', 'monto', 'fecha', 'estado', 'credito_id']);
    }

    private function resetAbonoForm()
    {
        $this->reset(['abono_monto', 'abono_fecha']);
    }
    public function render()
    {
        return view('livewire.credito.credito');
    }
}
