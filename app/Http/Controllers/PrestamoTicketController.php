<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PrestamoTicketController extends Controller
{
    public function show($id)
    {
        $prestamo = Prestamo::with('cliente', 'movimientos')->findOrFail($id);

        $responsable = User::find($prestamo->usuario_id);
        $responsableNombre = $responsable
            ? trim($responsable->name.' '.$responsable->last_name)
            : null;

        // saldo_capital + interés causado a hoy: se actualiza con cada abono, a diferencia
        // de usar "monto" (el desembolso original, que nunca cambia).
        $valorReembolso = (float) $prestamo->total_adeudado;

        // Si ya se pagó algún mes de interés, el corte se movió hacia adelante: el
        // ticket deja de ser el contrato inicial y pasa a ser una prórroga.
        $ultimaProrroga = $prestamo->movimientos
            ->whereIn('tipo', ['pago_interes', 'mixto'])
            ->where('meses_cubiertos', '>', 0)
            ->sortByDesc('fecha')
            ->first();

        $imagenPrendaPath = null;
        if ($prestamo->imagen_prenda) {
            $ruta = Storage::disk('public')->path('prestamos/'.$prestamo->imagen_prenda);
            if (file_exists($ruta)) {
                $imagenPrendaPath = $ruta;
            }
        }

        $pdf = Pdf::loadView('pdf.prestamo-ticket', [
            'prestamo'           => $prestamo,
            'responsableNombre'  => $responsableNombre,
            'valorReembolso'     => $valorReembolso,
            'imagenPrendaPath'   => $imagenPrendaPath,
            'ultimaProrroga'     => $ultimaProrroga,
        ])->setPaper([0, 0, 226.77, $imagenPrendaPath ? 560 : 420], 'portrait'); // ~80mm, más alto si lleva foto

        return $pdf->stream('retroventa-'.($prestamo->num_contrato ?: $prestamo->id).'.pdf');
    }
}
