<?php

namespace App\Livewire\Credito;

use App\Models\Abono;
use App\Models\Adjunto;
use App\Models\Credito as ModelsCredito;
use App\Models\Cuenta;
use App\Models\Movimiento;
use App\Models\User;
use App\Traits\General;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;


class Credito extends Component
{
    use WithFileUploads;
    use General;


    public $creditos = [];
    public $usuarios = [];
    public $deudor_id, $responsable_id, $monto, $fecha, $estado = 'pendiente', $credito_id;
    public $abonos = [], $abono_monto, $abono_fecha;
    public $desde, $hasta;
    public $estado_filter, $venta_id, $tipo, $deudor_id_filter;
    public $adjunto, $des_monto, $deudor, $responsable;
    public $cuenta_id;
    public $cuentas;
    public $totalAbonos;
    public $metodosPago = [];

    public $listeners = [
        'metodo-monto-actualizado' => 'actualizarMontoMetodoPago',
    ];

    public function mount()
    {
        $this->usuarios = User::where('status', 1)->get();
        $this->cuentas = Cuenta::where('id', '<>', 0)->get();
        $this->metodosPago = [];
        if (!$this->desde && !$this->hasta) {
            $this->desde = date('Y-m-d', strtotime(date('Y-m-d') . ' - 1 month'));
            $this->hasta = date('Y-m-d');
        }
    }

    public function actualizarMontoMetodoPago($index, $monto)
    {
        // Actualizar el monto en la posición correcta
        $this->metodosPago[$index]['monto'] = $monto;
    }

    public function validarYGuardar()
    {
        $montoTotalMetodosPago = round(array_sum(array_column($this->metodosPago, 'monto')), 2);
        $montoTotalCredito = round($this->des_monto, 2);

        if ($montoTotalMetodosPago > $montoTotalCredito) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'El monto del abono no puede superar el saldo pendiente del crédito.'
            ]);
            return;
        }

        foreach ($this->metodosPago as $metodo) {
            if (empty($metodo['cuenta_id']) || !isset($metodo['monto'])) {
                $this->dispatch('showToast', [
                    'type' => 'error',
                    'message' => 'Cada método de pago debe tener una cuenta, nombre y monto específicos.'
                ]);
                return;
            }
        }

        DB::transaction(function () use ($montoTotalMetodosPago) {
            $credito = ModelsCredito::find($this->credito_id);
            $credito->des_monto -= $montoTotalMetodosPago;
            if ($credito->des_monto <= 0) {
                $credito->estado = 'pago';
            }
            $credito->save();

            // Crear un movimiento para cada cuenta en los métodos de pago
            foreach ($this->metodosPago as $metodo) {
                if (isset($metodo['cuenta_id'])) {
                    Movimiento::create([
                        'credito_id' => $credito->id,
                        'cuenta_id' => $metodo['cuenta_id'],
                        'tipo' => 'ingreso',
                        'monto' => $metodo['monto'],
                        'descripcion' => "Ingreso por abono al credito #{$credito->id} en cuenta {$metodo['nombre']}",
                        'usuario_id' => Auth::id(),
                        'fecha' => now()
                    ]);
                    Abono::create([
                        'credito_id' => $credito->id,
                        'monto' => $metodo['monto'],
                        'fecha' => now()
                    ]);
                }
            }
            $this->getCredito();
            $this->metodosPago = [];
        });

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Abono realizado correctamente.']);
        $this->dispatch('closeModal');
    }

    public function getCredito()
    {
        $this->skipRender();
        $creditos = ModelsCredito::query()->with('deudor', 'responsable', 'abonos', 'ventas', 'adjuntos');

        if ($this->desde && $this->hasta) {
            $creditos->whereBetween('fecha', [$this->desde, $this->hasta]);
        }

        if ($this->deudor_id_filter) {
            $creditos->where('deudor_id', $this->deudor_id_filter);
        }

        if ($this->responsable_id) {
            $creditos->where('responsable_id', $this->responsable_id);
        }

        if ($this->estado_filter && $this->estado_filter !== '0') {
            $creditos->where('estado', $this->estado_filter);
        }
        return $creditos->get();
    }

    public function saveCredito()
    {
        $this->validate([
            'deudor_id' => 'required|exists:users,id',
            'monto' => 'required|min:1',
        ]);

        $valorLimpio = $this->__limpiarNumDecimales($this->monto);

        if ($this->credito_id) {
            $credito = ModelsCredito::find($this->credito_id);

            if ($credito->abonos->isEmpty() && $credito->venta_id === null) {
                $credito->update([
                    'deudor_id' => $this->deudor_id,
                    'responsable_id' => Auth::id(),
                    'monto' => $valorLimpio,
                    'des_monto' => $valorLimpio,
                    'fecha' => $credito->fecha,
                    'tipo' => 'Prestamo'
                ]);
                $this->adjuntar($credito->id);
            } else {
                return false;
            }
        } else {
            $credito = ModelsCredito::create([
                'deudor_id' => $this->deudor_id,
                'responsable_id' => Auth::id(),
                'monto' => $valorLimpio,
                'des_monto' => $valorLimpio,
                'fecha' => now(),
                'tipo' => 'Prestamo'
            ]);
            $this->adjuntar($credito->id);
        }

        if ($credito) {
            $credito->load(['deudor', 'responsable', 'abonos', 'ventas', 'adjuntos']);
            $this->resetForm();
            $this->mount();
            return $credito->toArray();
        } else {
            return false;
        }
    }

    public function getAbono($id)
    {
        $credito = ModelsCredito::with('deudor', 'responsable', 'abonos', 'ventas', 'adjuntos')->find($id);

        if (!$credito) {
            $this->dispatch('showToast', [['type' => 'error', 'message' => 'Credito no encontrada.']]);
            return;
        }

        $this->credito_id = $credito->id;
        // $this->monto = $credito->monto;
        $this->monto = (float) $credito->monto;
        // $this->des_monto = $credito->des_monto;
        $this->des_monto = (float) $credito->des_monto;
        $this->deudor = $credito->deudor->name;
        $this->responsable = $credito->responsable->name;
        $this->fecha = $credito->fecha;
        $this->abonos = $credito->abonos;
        $this->dispatch('openAbonoModal');

        // dd($credito);
    }

    public function adjuntar($id)
    {
        $credito = ModelsCredito::find($id);

        if ($this->adjunto && is_object($this->adjunto)) {
            if ($credito->adjuntos->isNotEmpty()) {
                foreach ($credito->adjuntos as $adjunto) {
                    if (Storage::exists($adjunto->ruta)) {
                        Storage::delete($adjunto->ruta);
                    }
                    $adjunto->delete();
                }
            }

            $nombre_original = $this->adjunto->getClientOriginalName();
            $nombre_sin_extension = pathinfo($nombre_original, PATHINFO_FILENAME);
            $extension = $this->adjunto->getClientOriginalExtension();
            $nombre_db = Str::slug($nombre_sin_extension);
            $nombre_a_guardar = $nombre_db . '.' . $extension;

            $ruta = $this->adjunto->storeAs('public/adjuntos', $nombre_a_guardar);

            Adjunto::create([
                'ruta' => $ruta,
                'credito_id' => $id,
            ]);

            $this->reset('adjunto');
        }
    }

    public function getIcon($extension)
    {
        $icons = [
            'pdf' => asset('icons/pdf-icon.png'),
            'doc' => asset('icons/word-icon.png'),
            'docx' => asset('icons/word-icon.png'),
            'zip' => asset('icons/zip-icon.png'),
            'rar' => asset('icons/zip-icon.png'),
            'xls' => asset('icons/excel-icon.png'),
            'xlsx' => asset('icons/excel-icon.png'),
        ];

        return $icons[$extension] ?? asset('icons/default-icon.png');
        $this->resetValidation();
    }

    public function deleteCredito($id)
    {
        $credito = ModelsCredito::find($id);

        if ($credito->abonos->isEmpty() && $credito->venta_id === null) {
            if ($credito->adjuntos->isNotEmpty()) {
                foreach ($credito->adjuntos as $adjunto) {
                    if (Storage::exists($adjunto->ruta)) {
                        Storage::delete($adjunto->ruta);
                    }
                    $adjunto->delete();
                }
            }

            $credito->delete();
            $this->getcredito();
            return true;
        } else {
            return false;
        }
    }

    public function resetForm()
    {
        $this->reset(['deudor_id', 'responsable_id', 'monto', 'fecha', 'estado', 'credito_id', 'adjunto']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.credito.credito');
    }
}
