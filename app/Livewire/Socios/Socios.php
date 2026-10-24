<?php

namespace App\Livewire\Socios;

use App\Models\DetCompra;
use App\Models\DetCompraSocio;
use App\Models\Socio as ModelsSocio;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Socios extends Component
{
    public $socios = [];
    public $socio_id;
    public $documento, $nombre, $direccion, $telefono, $correo, $contacto, $status;

    public function getSocios()
    {
        $this->skipRender(); // evita que el componente se vuelva a renderizar
        $socios = ModelsSocio::where('status', 1)->get();
        return $socios;
    }

    public function save()
    {
        $this->validate([
            'nombre'     => 'required',
            'documento'  => 'nullable|unique:socios,documento,' . $this->socio_id,
        ]);

        if ($this->socio_id) { // update
            $socio = ModelsSocio::find($this->socio_id);
            if (isset($socio->id)) {

                $socio->documento  = $this->documento;
                $socio->nombre     = $this->nombre;
                $socio->direccion  = $this->direccion;
                $socio->telefono   = $this->telefono;
                $socio->email      = $this->correo;
                $socio->contacto   = $this->contacto;
                $socio->status     = $this->status;
                $socio->save();
            }
        } else { // insert
            $socio = ModelsSocio::create([
                'documento' => $this->documento,
                'nombre'    => $this->nombre,
                'direccion' => $this->direccion,
                'telefono'  => $this->telefono,
                'email'     => $this->correo,
                'contacto'  => $this->contacto,
                'status'    => 1,
            ]);
        }

        if (isset($socio) && $socio) {
            $this->reset();
            return $socio->toArray();
        } else {
            return false;
        }
    }

    public function deleteSocio($item_id)
    {
        $socio = ModelsSocio::find($item_id);

        if ($socio) {
            $socio->update(['status' => 0]);
            return $socio->toArray();
        } else {
            return false;
        }
    }

    // KPIs de inventario/trazabilidad de un socio, acotado a las categorías marcadas como "aplica_socios"
    // las líneas compradas en sociedad se prorratean según el % aportado por este socio (dcs.porcentaje)
    public function getKpisSocio($socio_id)
    {
        $this->skipRender();

        $inventario = DB::table('det_compra_socios as dcs')
            ->join('det_compras as dc', 'dc.id', '=', 'dcs.det_compra_id')
            ->join('productos as p', 'p.id', '=', 'dc.producto_id')
            ->join('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->where('dcs.socio_id', $socio_id)
            ->where('c.aplica_socios', 1)
            ->selectRaw('
                COALESCE(SUM(dc.stock * dcs.porcentaje / 100), 0) as prendas,
                COALESCE(SUM(dc.stock * dc.precio_compra * dcs.porcentaje / 100), 0) as capital_invertido
            ')
            ->first();

        $ventas = DB::table('det_ventas as dv')
            ->join('det_compras as dc', 'dc.id', '=', 'dv.det_compra_id')
            ->join('det_compra_socios as dcs', 'dcs.det_compra_id', '=', 'dc.id')
            ->join('productos as p', 'p.id', '=', 'dv.producto_id')
            ->join('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->where('dcs.socio_id', $socio_id)
            ->where('c.aplica_socios', 1)
            ->selectRaw('
                COALESCE(SUM(dv.cant * dcs.porcentaje / 100), 0) as unidades_vendidas,
                COALESCE(SUM(dv.cant * dv.precio_venta * dcs.porcentaje / 100), 0) as total_vendido,
                COALESCE(SUM(
                    (CASE
                        WHEN dc.precio_compra IS NULL OR dc.precio_compra = 0 THEN dv.cant * dv.precio_venta
                        ELSE dv.cant * (dv.precio_venta - dc.precio_compra)
                    END) * dcs.porcentaje / 100
                ), 0) as margen_ganancia
            ')
            ->first();

        return [
            'prendas'           => (float) $inventario->prendas,
            'capital_invertido' => (float) $inventario->capital_invertido,
            'unidades_vendidas' => (float) $ventas->unidades_vendidas,
            'total_vendido'     => (float) $ventas->total_vendido,
            'margen_ganancia'   => (float) $ventas->margen_ganancia,
        ];
    }

    // prendas que este socio compró en sociedad con otros socios (más de un aportante en la línea)
    public function getPrendasSociedad($socio_id)
    {
        $this->skipRender();

        $det_compra_ids = DB::table('det_compra_socios as dcs')
            ->join('det_compras as dc', 'dc.id', '=', 'dcs.det_compra_id')
            ->join('productos as p', 'p.id', '=', 'dc.producto_id')
            ->join('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->where('dcs.socio_id', $socio_id)
            ->where('c.aplica_socios', 1)
            ->pluck('dcs.det_compra_id');

        $compartidas = DB::table('det_compra_socios')
            ->whereIn('det_compra_id', $det_compra_ids)
            ->groupBy('det_compra_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('det_compra_id');

        return DetCompra::with(['producto', 'socios.socio'])
            ->whereIn('id', $compartidas)
            ->get()
            ->map(function ($dc) {
                return [
                    'id'       => $dc->id,
                    'producto' => $dc->producto->nombre,
                    'total'    => $dc->precio_compra * $dc->stock_compra,
                    'socios'   => $dc->socios->map(fn($s) => [
                        'nombre'     => $s->socio->nombre,
                        'monto'      => (float) $s->monto_aportado,
                        'porcentaje' => (float) $s->porcentaje,
                    ]),
                ];
            })
            ->values();
    }

    // líneas de compra de categorías "aplica_socios" que quedaron sin ningún socio asignado (compras anteriores a este módulo)
    public function getDetComprasSinSocio()
    {
        $this->skipRender();

        return DB::table('det_compras as dc')
            ->join('productos as p', 'p.id', '=', 'dc.producto_id')
            ->join('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->join('compras as co', 'co.id', '=', 'dc.compra_id')
            ->where('c.aplica_socios', 1)
            ->whereNotExists(function ($q) {
                $q->selectRaw(1)
                    ->from('det_compra_socios as dcs')
                    ->whereColumn('dcs.det_compra_id', 'dc.id');
            })
            ->select(
                'dc.id',
                'p.nombre as producto',
                'co.fecha',
                'dc.stock_compra',
                'dc.stock',
                'dc.precio_compra'
            )
            ->orderBy('co.fecha', 'desc')
            ->get();
    }

    // asigna en bloque un socio (único, 100% del aporte) a varias líneas de compra existentes
    public function asignarSocio($det_compra_ids, $socio_id)
    {
        $this->skipRender();

        if (empty($det_compra_ids) || !$socio_id) {
            return false;
        }

        DB::transaction(function () use ($det_compra_ids, $socio_id) {
            $detalles = DetCompra::whereIn('id', $det_compra_ids)->get();

            foreach ($detalles as $detalle) {
                // por seguridad, no pisar líneas que ya tengan socios asignados
                if ($detalle->socios()->exists()) {
                    continue;
                }

                $detalle->update(['socio_id' => $socio_id]);

                DetCompraSocio::create([
                    'det_compra_id'  => $detalle->id,
                    'socio_id'       => $socio_id,
                    'monto_aportado' => $detalle->precio_compra * $detalle->stock_compra,
                    'porcentaje'     => 100,
                ]);
            }
        });

        return true;
    }

    public function render()
    {
        return view('livewire.socios.socios', [
            'socios_activos' => ModelsSocio::where('status', 1)->get(),
        ])->title('Socios');
    }
}
