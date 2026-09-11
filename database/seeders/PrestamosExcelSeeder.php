<?php

namespace Database\Seeders;

use App\Models\Prestamo;
use App\Models\PrestamoCliente;
use App\Models\PrestamoInversionista;
use App\Models\PrestamoMovimiento;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Carga en la base de datos los casos actuales del Excel
 * "docs/Tablas requerimientos.xlsx" (hojas "Contratos Actuales" y
 * "Prestamos Personales"), tal como estaban al 2026-09-10.
 *
 * Ejecutar:  php artisan db:seed --class=PrestamosExcelSeeder
 *
 * Notas de mapeo:
 *  - La columna "MESES" del Excel era un dato manual; el sistema ahora
 *    calcula el interés automáticamente por fechas. Para las retroventas
 *    fecha_corte = fecha de inicio (el interés corre desde el inicio).
 *  - En préstamos personales el Excel mostraba el interés de un mes
 *    (SALDO x tasa), no acumulado; por eso al importar fecha_corte = hoy
 *    (interés causado = 0 al momento de la carga) y empieza a correr desde
 *    la importación.
 *  - "RETIRADO" (retroventa) => contrato pagado / prenda devuelta.
 *  - El "DUEÑO" del Excel es el inversionista. Las tasas de reparto salen
 *    del tablero resumen de la hoja 1 (cliente 7% ; SAMIR/YAMILE/KEVIN
 *    reciben 7% ; LAURA/SOL/GIOVAN reciben 5%).
 */
class PrestamosExcelSeeder extends Seeder
{
    private array $meses = [
        'ENE' => '01', 'FEB' => '02', 'MAR' => '03', 'ABR' => '04',
        'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AGO' => '08',
        'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DIC' => '12',
    ];

    private array $tasaInversionista = [
        'SAMIR' => 0.07, 'YAMILE' => 0.07, 'KEVIN' => 0.07,
        'LAURA' => 0.05, 'SOL' => 0.05, 'GIOVAN' => 0.05,
    ];

    /**
     * Contratos de la hoja "Contratos Actuales".
     * [dueño, fecha inicio, #contrato, nombre, cédula, peso, prenda, monto, estado, MESES pagados]
     * MESES = columna del Excel: nº de meses de interés que el cliente ya pagó
     * (cada mes pagado corre el vencimiento un mes).
     */
    private array $contratos = [
        ['GIOVAN', '29/JUL/2026', 1212, 'JOHANA RAMIREZ', '42800435', 30, '1 CADENA LAZO,DIJE CORONA', 5000000, 'ACTIVO', 1],
        ['GIOVAN', '11/AGO/2026', 1222, 'MORALES GREY', '15436921', 5.3, 'ANILLO HOMBRE PIEDRAS', 1000000, 'ACTIVO', 0],
        ['GIOVAN', '10/JUN/2026', 1175, 'DIXON CABRERA', '1005370391', 4.76, '2 PULSERAS', 1400000, 'ACTIVO', 0],
        ['GIOVAN', '5/SEP/2026', 1241, 'ANDRES PATIÑO', '15389265', 5, '1 PULSERA BALINES', 1200000, 'ACTIVO', 0],
        ['GIOVAN', '12/JUN/2026', 1176, 'JULIAN LAVERDE', '103270697', 4.4, 'PULSERA CUBANA', 1320000, 'ACTIVO', 0],
        ['GIOVAN', '12/JUN/2026', 1176, 'JULIAN LAVERDE', '103270697', 6.5, 'BALINES COMPRA', 1900000, 'VENCIDO', 0],
        ['GIOVAN', '19/AGO/2026', 1230, 'ALBA GARCIA', '32501547', 6, 'ANILLO ALBA', 700000, 'ACTIVO', 0],
        ['KEVIN', '26/ENE/2026', 1084, 'LUIS MONROY', '79453861', 5.9, '1 ARGOLLA MARCADA', 1900000, 'ACTIVO', 3],
        ['KEVIN', '29/AGO/2026', 1230, 'DANIEL RUINQUE', '1007717576', 13.2, 'UNA CADENA ESPEJO 3 OROS', 3300000, 'ACTIVO', 0],
        ['KEVIN', '31/ENE/2026', 1095, 'JUAN ARBELODA', '8032137', 4.8, '1 ANILLO GRADOS', 1000000, 'RETIRADO', 7],
        ['LAURA', '30/JUL/2026', 1213, 'FELIPE ESPAÑA', '1152186835', 3.7, 'CADENA TJ PLANA 3 OROS Y DIJE CRISTO', 1000000, 'ACTIVO', 0],
        ['LAURA', '27/JUL/2026', 1230, 'PIEDAD GARCIA', '43048207', 13.09, '1 PAR DE ARETAS, 1 PAR TOPOD', 3100000, 'ACTIVO', 0],
        ['LAURA', '13/AGO/2026', 1224, 'KAREN MONTOYA', '1036928102', 4.3, 'CADENA PLANA,DIJE Z', 1100000, 'ACTIVO', 0],
        ['LAURA', '15/AGO/2026', 1250, 'LUZ QUINTERO', '39441582', 2.7, 'UNA ARGOLLA LISA', 670000, 'ACTIVO', 0],
        ['SAMIR', '12/AGO/2026', 1225, 'FELIPE ESPAÑA', '1152186835', 42.2, 'CADENA TJ 3X1', 13500000, 'ACTIVO', 0],
        ['SAMIR', '3/DIC/2025', 1037, 'SEBASTIAN CASTAÑO', '1017211660', 6.8, '1 ANILLO, 2 CADENAS, 1 TOPO,2 DIJES', 1850000, 'ACTIVO', 5],
        ['SAMIR', '24/NOV/2025', 1032, 'FRANCY SINISTERRA', '1147953214', 8.5, '4 ANILLOS, 3 BLANCOS,1 AMARILLO', 2500000, 'ACTIVO', 6],
        ['SAMIR', '3/JUN/2026', 1169, 'ALEXANDER MONTOYA', '71376667', 12, 'ANILLO BIMBO 10KTS', 800000, 'ACTIVO', 0],
        ['SAMIR', '25/AGO/2026', 1230, 'MARGARITA HOYOS', '39450474', 1.9, 'PULSERA REVENTADA Y PAR DE TOPOS', 450000, 'ACTIVO', 0],
        ['SAMIR', '25/AGO/2026', 1217, 'SEBASTIAN JARAMILLO', '1026135266', 0, 'RELOJ CARTIER REF:931609FY', 10000000, 'ACTIVO', 0],
        ['SAMIR', '29/JUL/2026', 1212, 'JOHANA RAMIREZ', '42800435', 38, '1 CADENA LAZO,DIJE CORONA ANILLO', 2000000, 'RETIRADO', 1],
        ['SOL', '29/JUL/2026', 1212, 'JOHANA RAMIREZ', '42800435', 30, '1 CADENA LAZO,DIJE CORONA', 2300000, 'ACTIVO', 1],
        ['YAMILE', '12/JUN/2026', 1176, 'TULIO RENDON', '15320203', 36.67, 'CADENA MILITAR Y DIJE VIRGEN', 10500000, 'ACTIVO', 0],
        ['YAMILE', '5/SEP/2026', 1240, 'TULIO RENDON', '15320203', 36, 'CADENA MILITAR,DIJE V,CADENA LAZO', 7500000, 'ACTIVO', 0],
        ['YAMILE', '4/FEB/2026', 1100, 'JORGE ARBELAEZ', '15442547', 5.78, 'UNA ARGOLLA MARCADA', 1730000, 'VENCIDO', 0],
    ];

    /**
     * Préstamos personales. [cartera, fecha, nombre, monto, abono, interes_snapshot].
     * (Se omiten filas de agregación del Excel que no son préstamos.)
     */
    private array $personales = [
        ['General', '2025-10-01', 'JOSE BARBERO', 600000, 0, 0],
        ['General', '2025-09-28', 'RANDY BARBERO', 500000, 500000, 0],
        ['General', '2025-02-16', 'JHOAN CACERES', 4400000, 4400000, 0],
        ['General', '2025-08-05', 'GIOVAN', 3000000, 3000000, 0],
        ['General', '2025-08-11', 'LAURA ACOSTA', 600000, 300000, 0],
        ['General', '2025-09-05', 'ANDERSON', 4100000, 4100000, 0],
        ['General', '2025-08-21', 'LINA', 1000000, 1000000, 50000],
        ['General', '2025-04-30', 'WALTER', 1000000, 0, 50000],
        ['General', '2025-05-10', 'ERICA', 3000000, 500000, 125000],
        ['General', '2025-05-10', 'FRANCHESCO', 2000000, 500000, 75000],
        ['General', '2025-01-01', 'TEACHER', 1043500, 1043500, 0],
        ['General', '2025-01-01', 'MAURICIO', 900000, 900000, 0],
        ['General', '2025-01-01', 'SEBASTIAN POLO', 3000000, 0, 150000],
        ['General', '2025-01-01', 'YOFREI', 1000000, 400000, 0],
        ['General', '2026-09-04', 'EDDER', 2500000, 0, 125000],
        ['General', '2025-01-01', 'MARIA FERNANDA', 2405000, 2405000, 0],
        ['General', '2025-01-01', 'MONICA', 500000, 500000, 0],
        ['General', '2025-01-01', 'YELITZA', 800000, 200000, 0],
        ['General', '2025-06-15', 'DOÑA AMPARO', 350000, 350000, 60000],
        ['General', '2025-10-20', 'MAURICIO', 4000000, 0, 200000],
        ['General', '2025-05-20', 'WICHO', 11500000, 0, 575000],
        ['General', '2025-11-28', 'MONICA', 2500000, 2500000, 0],
        ['General', '2026-01-31', 'MUJER BARBERO', 800000, 800000, 40000],
        ['General', '2026-02-11', 'ANDREA RAMOS', 1500000, 950000, 75000],
        ['General', '2026-01-20', 'FEDERICO', 600000, 0, 0],
        ['General', '2026-02-23', 'DANIELA DURANGO', 2500000, 0, 125000],

        ['Laura', '2025-05-05', 'WICHO', 8500000, 1500000, 210000],
        ['Laura', '2025-06-15', 'AMPARO', 700000, 700000, 21000],
        ['Laura', '2025-07-20', 'SAMIR', 1400000, 1400000, 42000],
        ['Laura', '2025-10-29', 'EMPEÑO', 12500000, 12500000, 875000],
        ['Laura', '2025-11-21', 'COMPRA ORO', 1150000, 1400000, 0],
        ['Laura', '2025-12-16', 'EMPEÑO', 9000000, 9000000, 630000],
        ['Laura', '2025-12-16', 'COMPRA ORO', 3530000, 0, 0],

        ['Mamá', '2025-05-05', 'YAJAIRA', 2000000, 0, 100000],
    ];

    public function run(): void
    {
        $hoy = Carbon::today()->toDateString();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PrestamoMovimiento::truncate();
        Prestamo::truncate();
        PrestamoCliente::truncate();
        PrestamoInversionista::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ---- Inversionistas ----
        $inversionistas = [];
        foreach ($this->tasaInversionista as $nombre => $tasa) {
            $inversionistas[$nombre] = PrestamoInversionista::create([
                'nombre' => $nombre,
                'tasa' => $tasa,
                'activo' => 1,
            ]);
        }

        // ---- Retroventa ----
        $plazo = 4;
        foreach ($this->contratos as [$dueno, $fini, $num, $nombre, $cedula, $peso, $desc, $monto, $estadoXls, $mesesXls]) {
            $inicio = $this->parseFecha($fini);
            $mesesXls = (int) $mesesXls;
            $cliente = PrestamoCliente::firstOrCreate(['cedula' => $cedula], ['nombre' => $nombre]);

            $retirado = $estadoXls === 'RETIRADO';

            // El cliente pagó $mesesXls meses de interés → el corte avanza esos meses.
            // Si el Excel lo marca ACTIVO pero con esos meses ya estaría vencido, quiere decir
            // que la columna MESES está atrasada: se sube al mínimo que lo mantiene vigente.
            $mesesPagados = $mesesXls;
            if ($estadoXls === 'ACTIVO') {
                $transcurridos = Carbon::parse($inicio)->diffInMonths(Carbon::today());
                $mesesPagados = max($mesesXls, $transcurridos - $plazo + 1, 0);
            }

            $corte = $mesesPagados > 0
                ? Carbon::parse($inicio)->addMonths($mesesPagados)->toDateString()
                : $inicio;
            $interesMensual = round($monto * 0.07, 2);

            $prestamo = Prestamo::create([
                'modalidad' => 'retroventa',
                'cliente_id' => $cliente->id,
                'inversionista_id' => $inversionistas[$dueno]->id ?? null,
                'num_contrato' => (string) $num,
                'peso' => $peso ?: null,
                'descripcion_prenda' => $desc,
                'fecha_inicio' => $inicio,
                'fecha_corte' => $retirado ? $inicio : $corte,
                'fecha_cierre' => $retirado ? $hoy : null,
                'monto' => $monto,
                'saldo_capital' => $retirado ? 0 : $monto,
                'tasa_interes' => 0.07,
                'plazo_meses' => $plazo,
                'estado' => $retirado ? 'pagado' : 'activo',
            ]);

            PrestamoMovimiento::create([
                'prestamo_id' => $prestamo->id,
                'fecha' => $inicio,
                'tipo' => 'desembolso',
                'monto_capital' => $monto,
                'capital_antes' => 0,
                'capital_despues' => $monto,
                'created_at' => now(),
            ]);

            // Intereses ya pagados según la columna MESES del Excel
            if ($mesesPagados > 0 && ! $retirado) {
                $obs = $mesesPagados === $mesesXls
                    ? "Intereses pagados según columna MESES del Excel ({$mesesXls})."
                    : "Intereses pagados. Excel: {$mesesXls}; ajustado a {$mesesPagados} para reflejar el contrato vigente.";
                PrestamoMovimiento::create([
                    'prestamo_id' => $prestamo->id,
                    'fecha' => $corte,
                    'tipo' => 'pago_interes',
                    'monto_interes' => round($interesMensual * $mesesPagados, 2),
                    'monto_capital' => 0,
                    'capital_antes' => $monto,
                    'capital_despues' => $monto,
                    'fecha_corte_antes' => $inicio,
                    'fecha_corte_despues' => $corte,
                    'meses_cubiertos' => $mesesPagados,
                    'interes_causado' => round($interesMensual * $mesesPagados, 2),
                    'observacion' => $obs,
                    'created_at' => now(),
                ]);
            }

            if ($retirado) {
                PrestamoMovimiento::create([
                    'prestamo_id' => $prestamo->id,
                    'fecha' => $hoy,
                    'tipo' => 'cancelacion',
                    'monto_interes' => round($interesMensual * $mesesXls, 2),
                    'monto_capital' => $monto,
                    'capital_antes' => $monto,
                    'capital_despues' => 0,
                    'fecha_corte_antes' => $inicio,
                    'fecha_corte_despues' => $hoy,
                    'meses_cubiertos' => $mesesXls,
                    'observacion' => "Prenda retirada por el cliente (RETIRADO en el Excel, {$mesesXls} meses de interés pagados).",
                    'created_at' => now(),
                ]);
            }
        }

        // ---- Préstamos personales ----
        foreach ($this->personales as [$cartera, $fecha, $nombre, $monto, $abono, $interesXls]) {
            if ($monto <= 0) {
                continue;
            }

            $cliente = PrestamoCliente::firstOrCreate(['nombre' => $nombre], []);
            $capitalPagado = min($abono, $monto);
            $saldoCapital = round($monto - $capitalPagado, 2);
            $pagado = $saldoCapital <= 0;

            // Laura maneja 3% normal y 7% para operaciones de "EMPEÑO".
            $tasa = $cartera === 'Laura'
                ? (str_contains($nombre, 'EMPEÑO') ? 0.07 : 0.03)
                : 0.05;

            $prestamo = Prestamo::create([
                'modalidad' => 'personal',
                'cliente_id' => $cliente->id,
                'cartera' => $cartera,
                'fecha_inicio' => $fecha,
                'fecha_corte' => $pagado ? $fecha : $hoy,
                'fecha_cierre' => $pagado ? $hoy : null,
                'monto' => $monto,
                'saldo_capital' => max(0, $saldoCapital),
                'tasa_interes' => $tasa,
                'plazo_meses' => 0,
                'estado' => $pagado ? 'pagado' : 'activo',
                'observacion' => $interesXls ? "Interés de referencia del Excel: $" . number_format($interesXls) : null,
            ]);

            PrestamoMovimiento::create([
                'prestamo_id' => $prestamo->id,
                'fecha' => $fecha,
                'tipo' => 'desembolso',
                'monto_capital' => $monto,
                'capital_antes' => 0,
                'capital_despues' => $monto,
                'created_at' => now(),
            ]);

            if ($capitalPagado > 0) {
                PrestamoMovimiento::create([
                    'prestamo_id' => $prestamo->id,
                    'fecha' => $fecha,
                    'tipo' => 'abono_capital',
                    'monto_capital' => $capitalPagado,
                    'capital_antes' => $monto,
                    'capital_despues' => max(0, $saldoCapital),
                    'fecha_corte_antes' => $fecha,
                    'fecha_corte_despues' => $fecha,
                    'observacion' => 'Abono acumulado registrado en el Excel.',
                    'created_at' => now(),
                ]);
            }
        }

        $this->command->info('Préstamos importados: ' . Prestamo::count()
            . ' | Clientes: ' . PrestamoCliente::count()
            . ' | Inversionistas: ' . PrestamoInversionista::count()
            . ' | Movimientos: ' . PrestamoMovimiento::count());
    }

    private function parseFecha(string $s): string
    {
        [$d, $m, $y] = explode('/', strtoupper(trim($s)));

        return sprintf('%04d-%s-%02d', (int) $y, $this->meses[$m] ?? '01', (int) $d);
    }
}
