<?php

namespace App\Livewire\Productos;

use App\Models\Producto;
use App\Models\Categoria;
use App\Traits\General;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class Productos extends Component
{
    use General;

    use WithFileUploads;

    public $productos = [];
    public $categorias = []; // Para almacenar las categorías disponibles
    public $nombre, $descripcion, $precio_base, $precio_mayorista, $disponible = true,  $stock_infinito = false, $categoria_id, $imagen, $changeImagen, $producto_id;

    public function mount()
    {
        $this->categorias = Categoria::all();
    }

    public function render()
    {
        return view('livewire.productos.productos')->title('Productos');
    }

    public function getProductos()
    {
        $this->productos = Producto::with('categoria')->get(); // Traemos los productos con su relación de categoría
        return $this->productos;
    }

    public function save()
    {
        $this->validate([
            'nombre'         => 'required',
            'precio_base'    => 'required',
            'categoria_id'   => 'required|exists:categorias,id', // Validamos que la categoría exista
        ]);

        // Procesar la imagen si se cambió
        if ($this->imagen && $this->changeImagen) {
            $name_picture = $this->processImage($this->imagen); // Llama a la función para procesar la imagen
        } else {
            $name_picture = $this->producto_id ? Producto::find($this->producto_id)->imagenes : 'default.png';
        }

        if ($this->producto_id) { // Update
            $producto = Producto::with('categoria')->find($this->producto_id);

            if ($producto) {
                // Si se cambió la imagen, eliminamos la anterior
                if ($this->changeImagen && $producto->picture) {
                    Storage::disk('public')->delete('productos/' . $producto->picture);
                }
                $producto->update([
                    'nombre'              => $this->nombre,
                    'descripcion'         => $this->descripcion,
                    'precio_base'         => $this->limpiarNum($this->precio_base),
                    'precio_mayorista'    => $this->limpiarNum($this->precio_mayorista),
                    'disponible'          => $this->disponible,
                    'stock_infinito'      => $this->stock_infinito,
                    'categoria_id'        => $this->categoria_id, // Aseguramos que se actualice la categoría
                    'imagenes'            => $name_picture ?? $producto->picture,
                ]);
            }
        } else { // Insert
            $producto = Producto::create([
                'nombre'              => $this->nombre,
                'descripcion'         => $this->descripcion,
                'precio_base'         => $this->limpiarNum($this->precio_base),
                'precio_mayorista'    => $this->limpiarNum($this->precio_mayorista) ?? null,
                'disponible'          => $this->disponible,
                'stock_infinito'      => $this->stock_infinito,
                'categoria_id'        => $this->categoria_id, // Se asigna la categoría correctamente
                'imagenes'            => $name_picture,
            ]);
        }

        if ($producto) {
            $producto->load('categoria');
            $this->reset();
            return $producto->toArray();
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
        $name_picture = 'producto-' . $rand . '.' . $ext;

        // Redimensionamos y guardamos la imagen
        $img = Image::make($imagen)->widen(80)->encode($ext);
        Storage::disk('public')->put('productos/' . $name_picture, $img);

        return $name_picture;
    }


    public function deleteProducto($id)
    {
        $producto = Producto::find($id);

        if ($producto) {
            $producto->update(['disponible' => 0]);
            return $producto->toArray();
        } else {
            return false;
        }
    }

    public function resetForm()
    {
        $this->reset([
            'nombre',
            'descripcion',
            'precio_base',
            'precio_mayorista',
            'disponible',
            'stock_infinito',
            'categoria_id',
            'imagen',
            'producto_id',
            // $this->categorias=[]
        ]);
        $this->resetValidation();
    }
}
