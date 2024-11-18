<?php

namespace App\Livewire\Movimientos;

use App\Models\Adjunto;
use App\Models\Movimiento;
use App\Models\Cuenta;
use App\Models\User;
use App\Traits\General;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use PhpParser\Node\Stmt\If_;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Intervention\Image\ImageManagerStatic as Image;




class Movimientos extends Component
{
    use General;
    use WithfileUploads;


    public $movimientos = [];
    public $cuentas = [];
    public $usuarios = [];
    public $cuenta_id, $tipo, $valor, $fecha, $descripcion, $movimiento_id, $usuario_id;
    public $cuenta_destino_id;
    public $desde, $hasta;
    public $cuenta_id_filter, $tipo_filter;
    public $adjunto;


    public function mount()
    {
        $this->cuentas = Cuenta::where('status', 1)->where('id','<>', 0)->get();
        $this->usuarios = User::where('status', 1)->get();
        $this->fecha   = now()->format('Y-m-d');

        if (!$this->desde && !$this->hasta) {
            $this->desde = date('Y-m-d', strtotime(date('Y-m-d') . ' - 1 month'));
            $this->hasta = date('Y-m-d');
        }
    }

    public function render()
    {
        return view('livewire.movimientos.movimientos')->title('Gestión de Movimientos');
    }

    public function getMovimientos()
    {
        $this->skipRender();

        $movimientos = Movimiento::query()->with('usuario', 'cuenta', 'adjuntos');

        if ($this->desde && $this->hasta) {
            $movimientos->whereBetween('fecha', [$this->desde, $this->hasta]);
        }

        if ($this->cuenta_id_filter) {
            $movimientos->where('cuenta_id', $this->cuenta_id_filter);
        }

        if ($this->usuario_id) {
            $movimientos->where('usuario_id', $this->usuario_id);
        }

        if ($this->tipo_filter && $this->tipo_filter !== '0') {
            $movimientos->where('tipo', $this->tipo_filter);
        }

        return $movimientos->get();
    }

    public function save()
    {
        $valorLimpio = $this->__limpiarNumDecimales($this->valor);
        // dd($valorLimpio);
        $newDate = date("Y-m-d", strtotime($this->fecha));
        if ($valorLimpio <= 0) {
            $this->addError('valor', 'El valor debe ser mayor a 0.');
            return false;
        }

        $this->validate([
            'cuenta_id'    => 'required|exists:cuentas,id',
            'tipo'         => 'required',
            'valor'        => 'required',
            'fecha'        => 'required',
            'descripcion'  => 'nullable|max:255',
        ]);

        // Lógica para transferencias
        if ($this->tipo == 'transferencia') {
            $this->validate([
                'cuenta_destino_id' => [
                    'required',
                    'exists:cuentas,id',
                    'different:cuenta_id',
                ],
            ], [
                'cuenta_destino_id.different' => 'La cuenta de origen debe ser distinta a la cuenta de destino.',
            ]);
            // Restar el monto de la cuenta de origen
            $cuentaOrigen = Cuenta::find($this->cuenta_id);
            $cuentaOrigen->saldo -= $valorLimpio;
            $cuentaOrigen->save();

            // Sumar el monto a la cuenta de destino
            $cuentaDestino = Cuenta::find($this->cuenta_destino_id);
            $cuentaDestino->saldo += $valorLimpio;
            $cuentaDestino->save();

            $movimiento = Movimiento::create([
                'cuenta_id'   => $this->cuenta_id,
                'tipo'        => 'egreso',
                'monto'       => $valorLimpio,
                'fecha'       => $newDate,
                'descripcion' => 'Transferencia a cuenta ' . $cuentaDestino->nombre,
                'usuario_id'  => Auth::id(),
            ]);

            $this->adjuntar($movimiento->id);

            $movimiento =   Movimiento::create([
                'cuenta_id'   => $this->cuenta_destino_id,
                'tipo'        => 'ingreso',
                'monto'       => $valorLimpio,
                'fecha'       => $newDate,
                'descripcion' => 'Transferencia desde cuenta ' . $cuentaOrigen->nombre,
                'usuario_id'  => Auth::id(),
            ]);

            $this->adjuntar($movimiento->id);


            if ($movimiento) {
                $movimiento->load(['cuenta', 'usuario']);
                $this->resetForm();
                $this->mount();
                return $movimiento->toArray();
            } else {
                return false;
            }
        }

        // Lógica para ingresos y egresos
        if ($this->movimiento_id) {
            // Editar movimiento existente
            $movimiento = Movimiento::find($this->movimiento_id);
            $cuentaAnterior = Cuenta::find($movimiento->cuenta_id);

            // Revertir el impacto del movimiento anterior en el saldo de la cuenta
            if ($movimiento->tipo == 'ingreso') {
                $cuentaAnterior->saldo -= $movimiento->monto;
            } else {
                $cuentaAnterior->saldo += $movimiento->monto;
            }
            $cuentaAnterior->save();

            $movimiento->update([
                'cuenta_id'   => $this->cuenta_id,
                'tipo'        => $this->tipo,
                'monto'       => $valorLimpio,
                'fecha'       => $newDate,
                'descripcion' => $this->descripcion,
                'usuario_id'  => Auth::id(),
            ]);

            $this->adjuntar($movimiento->id);
        } else {
            $movimiento = Movimiento::create([
                'cuenta_id'   => $this->cuenta_id,
                'tipo'        => $this->tipo,
                'monto'       => $valorLimpio,
                'fecha'       => $newDate,
                'descripcion' => $this->descripcion,
                'usuario_id'  => Auth::id(),
            ]);

            $this->adjuntar($movimiento->id);
        }

        $cuentaNueva = Cuenta::find($this->cuenta_id);

        if ($this->tipo == 'ingreso') {
            $cuentaNueva->saldo += $movimiento->monto;
        } else {
            $cuentaNueva->saldo -= $movimiento->monto;
        }

        $cuentaNueva->save();

        if ($movimiento) {
            $movimiento->load(['cuenta', 'usuario', 'adjuntos']);
            $this->resetForm();
            $this->mount();
            return $movimiento->toArray();
        } else {
            return false;
        }
    }

    public function adjuntar($id)
    {
        $movimiento = Movimiento::find($id);

        if ($this->adjunto && is_object($this->adjunto)) {
            if ($movimiento->adjuntos->isNotEmpty()) {
                foreach ($movimiento->adjuntos as $adjunto) {
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
                'movimiento_id' => $id,
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

    public function deleteMovimiento($id)
    {
        $movimiento = Movimiento::find($id);

        if ($movimiento->compra_id === null && $movimiento->venta_id === null) {
            $cuenta = Cuenta::find($movimiento->cuenta_id);

            if ($movimiento->tipo == 'ingreso') {
                $cuenta->saldo -= $movimiento->monto;
            } elseif ($movimiento->tipo == 'egreso') {
                $cuenta->saldo += $movimiento->monto;
            }

            $cuenta->save();

            if ($movimiento->adjuntos->isNotEmpty()) {
                foreach ($movimiento->adjuntos as $adjunto) {
                    if (Storage::exists($adjunto->ruta)) {
                        Storage::delete($adjunto->ruta);
                    }
                    $adjunto->delete();
                }
            }

            $movimiento->delete();
            $this->getMovimientos();
            return true;
        } else {
            return false;
        }
    }

    public function resetForm()
    {
        $this->reset(['cuenta_id', 'tipo', 'valor', 'fecha', 'descripcion', 'movimiento_id', 'adjunto']);
        $this->resetValidation();
    }
}
