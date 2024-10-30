<?php

namespace App\Livewire\Compras;

use App\Models\Adjunto;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

use App\Models\Proveedores as Proveedor;
use App\Models\Cuenta;
use App\Models\Producto;
use App\Models\Categoria;

use App\Models\Compra;
use App\Models\DetCompra;
use App\Models\Movimiento;
use App\Traits\General;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class FormCompra extends Component
{
    use General, WithFileUploads;

    public $compra_id;
    public $proveedores = [], $cuentas = [], $categorias = [];
    public $proveedor_id, $cuenta_id;
    public $adjunto, $url;

    public $detalles    = []; // lista con los prod que se van agregando
    public $id_dets_old = []; // para saber al editar que items fueron eliminados

    public function mount($compra_id = null)
    {
        $this->compra_id = $compra_id;

        // si viene es porque estamos editando
        if ($this->compra_id) {
            $compra = Compra::where('id', $this->compra_id)->with(['detalles.producto', 'adjuntos'])->first()->toArray();
            // dd( $compra );
            if (isset($compra['id']) && $compra['id']) {
                $this->proveedor_id = $compra['proveedor_id'];
                $this->cuenta_id    = $compra['cuenta_id'];
                $this->url      = $compra['adjuntos'][0]['ruta'];
                // dd($this->url);

                foreach ($compra['detalles'] as $det) {

                    $this->id_dets_old[] = $det['id'];

                    $this->detalles[] = [
                        'det_id'            => $det['id'], // para saber si estamos editando
                        'id'                => $det['producto_id'],
                        'nombre'            => $det['producto']['nombre'],
                        'imagenes'          => $det['producto']['imagenes'],
                        'cantidad'          => $det['stock_compra'],
                        'vendidos'          => $det['stock_compra'] - $det['stock'],
                        'precio_compra'     => '$ ' . number_format($det['precio_compra'], 2),
                        'subtotal'          => $det['stock_compra'] * $det['precio_compra'],
                    ];
                }
                // dd( $this->detalles );
            }
        }

        $this->proveedores = Proveedor::where('status', 1)->get();
        $this->cuentas     = Cuenta::where('status', 1)->get();
        $this->categorias  = Categoria::where('status', 1)->get();
    }

    public function render()
    {
        return view('livewire.compras.form-compra')->title('Formulario Compras');
    }

    public function getProductos()
    {
        $productos = Producto::with('categoria')->get()->toArray();
        return $productos;
    }

    public function guardar()
    {
        $this->validate([
            'proveedor_id'  => 'required',
            'cuenta_id'     => 'required',
            'adjunto'       => 'nullable|file|max:2048'
        ]);

        $cuenta = Cuenta::find($this->cuenta_id); //Cargamos la cuenta de primero por si vamos a editar


        if (!$this->compra_id) { // crear

            $compra = Compra::create([
                'fecha'         => date('Y-m-d'),
                'proveedor_id'  => $this->proveedor_id,
                'cuenta_id'     => $this->cuenta_id,
                'user_id'       => Auth::id(),
                'total'         => 0,
            ]);
        } else { // editar

            $compra = Compra::find($this->compra_id);
            $cuenta->saldo += $compra->total; // Devolver el saldo previo a la cuenta
            $compra->proveedor_id   = $this->proveedor_id;
            $compra->cuenta_id      = $this->cuenta_id;
        }

        if (isset($compra->id)) {

            $compra->total = 0;
            $id_dets_new = []; // id de los det agregados o editados

            // agregamos los detalles
            foreach ($this->detalles as $det) {

                $precio_compra = $this->__limpiarNumDecimales($det['precio_compra']);
                $compra->total += $precio_compra * $det['cantidad'];

                if (isset($det['det_id']) && $det['det_id']) { // editar un detalle

                    $detCompra = DetCompra::find($det['det_id']);

                    if (isset($detCompra->id)) {
                        // el stock de venta se modifica en proporción a la nueva cantidad
                        $detCompra->stock         = $det['cantidad'] - $det['vendidos'];
                        $detCompra->stock_compra  = $det['cantidad'];
                        $detCompra->precio_compra = $precio_compra;
                        $detCompra->save();
                    }
                } else { // agregar un detalle

                    $detCompra = DetCompra::create([
                        'compra_id'         => $compra->id,
                        'producto_id'       => $det['id'],
                        'stock'             => $det['cantidad'],
                        'stock_compra'      => $det['cantidad'],
                        'precio_compra'     => $precio_compra,
                    ]);
                }

                // indicamos que el item se agrego o no se removió de los detalles
                $id_dets_new[] = $detCompra->id;
            }

            //revisamos si hay que eliminar algún detalle
            if ($this->id_dets_old) {
                foreach ($this->id_dets_old as $det_id) {

                    // si estaba en los viejos, pero no en los nuevos, fue que se eliminó
                    if (!in_array($det_id, $id_dets_new)) {
                        DetCompra::find($det_id)->delete();
                    }
                }
            }

            // para guardar el total
            $compra->save();

            // Descontar el total de la compra del saldo de la cuenta
            $cuenta->saldo -= $compra->total;
            $cuenta->save();

            // Crear o actualizar el movimiento
            $movimiento = Movimiento::updateOrCreate(
                ['compra_id' => $compra->id], // Identificar el movimiento existente basado en la compra
                [
                    'cuenta_id'  => $this->cuenta_id,
                    'tipo'       => 'egreso',
                    'monto'      => $compra->total,
                    'fecha'      => now(),
                    'usuario_id' => Auth::id(),
                ]
            );
            $this->dispatch('formOK');
        }

        if ($this->adjunto) {
            $this->adjuntar($compra->id);
        }
    }

    public function adjuntar($compra_id)
    {
        $compra = Compra::find($compra_id);

        if ($this->adjunto && is_object($this->adjunto)) {
            // Eliminar adjuntos previos si existen
            if ($compra->adjuntos->isNotEmpty()) {
                foreach ($compra->adjuntos as $adjunto) {
                    if (Storage::exists($adjunto->ruta)) {
                        Storage::delete($adjunto->ruta);
                    }
                    $adjunto->delete();
                }
            }

            // Guardar el nuevo archivo adjunto
            $nombre_original = $this->adjunto->getClientOriginalName();
            $nombre_sin_extension = pathinfo($nombre_original, PATHINFO_FILENAME);
            $extension = $this->adjunto->getClientOriginalExtension();
            $nombre_db = Str::slug($nombre_sin_extension);
            $nombre_a_guardar = $nombre_db . '.' . $extension;

            $ruta = $this->adjunto->storeAs('public/adjuntos', $nombre_a_guardar);

            Adjunto::create([
                'ruta' => $ruta,
                'compra_id' => $compra_id,
            ]);

            // Limpiar la propiedad del adjunto después de guardarlo
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
}
