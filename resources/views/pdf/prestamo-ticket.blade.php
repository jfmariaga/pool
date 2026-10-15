<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 6px 8px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5px;
            color: #000;
            line-height: 1.35;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .title {
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            margin: 4px 0 6px 0;
        }

        .hr {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .row {
            width: 100%;
            display: table;
            margin-bottom: 2px;
        }

        .row .col {
            display: table-cell;
        }

        .header-box {
            border: 1px solid #000;
            padding: 4px;
            margin-bottom: 6px;
        }

        .contrato-num {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
        }

        .plazo {
            text-align: right;
            font-size: 8px;
        }

        .prorroga-stamp {
            border: 2px solid #000;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            padding: 3px;
            margin-bottom: 6px;
            letter-spacing: 1px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
        }

        table.items th, table.items td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 8px;
        }

        .legal {
            text-align: justify;
            font-size: 7.3px;
            margin: 5px 0;
        }

        .valor-destacado {
            display: inline-block;
            border: 1px solid #000;
            padding: 1px 4px;
            font-weight: bold;
        }

        .footer-note {
            font-size: 7px;
            text-align: center;
            margin: 2px 0;
        }

        .fechas {
            margin-top: 6px;
        }

        .firma {
            margin-top: 22px;
            border-top: 1px solid #000;
            width: 90%;
            text-align: center;
            font-size: 8px;
            padding-top: 2px;
        }
    </style>
</head>
<body>

    <div class="header-box">
        <div class="row">
            <div class="col" style="width: 60%;">
                <span class="bold">No. Contrato</span>
            </div>
            <div class="col contrato-num" style="width: 40%;">
                {{ $prestamo->num_contrato ?: $prestamo->id }}
            </div>
        </div>
        <div class="plazo">{{ $prestamo->plazo_meses ?: 4 }} MES(ES)</div>
    </div>

    @if ($ultimaProrroga)
        <div class="prorroga-stamp">
            PRÓRROGA — {{ \Carbon\Carbon::parse($ultimaProrroga->fecha)->format('d/m/Y') }}
        </div>
    @endif

    <div class="title">
        Contrato de Compra-Venta<br>con pacto de Retroventa
    </div>

    <div>
        <span class="bold">Yo,</span> {{ mb_strtoupper($prestamo->cliente->nombre ?? '', 'UTF-8') }}
    </div>
    <div class="row">
        <div class="col" style="width: 60%;">
            C.C. {{ $prestamo->cliente->cedula ?? '' }}
        </div>
        <div class="col right" style="width: 40%;">
            Tel. {{ $prestamo->cliente->telefono ?? '' }}
        </div>
    </div>

    <div class="hr"></div>

    <div style="font-size: 7.3px; text-align: justify;">
        HAGO CONSTAR QUE HE VENDIDO LOS OBJETOS QUE SE RELACIONAN A CONTINUACIÓN.
    </div>

    <div class="row" style="margin-top: 4px;">
        <div class="col" style="width: 50%;">
            <span class="bold">Fecha de realización:</span>
        </div>
        <div class="col right" style="width: 50%;">
            {{ optional($prestamo->fecha_inicio)->format('d/m/Y') }}
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 65%;">Descripción del artículo</th>
                <th style="width: 35%;">Peso (grs)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $prestamo->descripcion_prenda ?: '-' }}</td>
                <td class="center">{{ $prestamo->peso ?: '-' }}</td>
            </tr>
        </tbody>
    </table>

    @if ($imagenPrendaPath)
        <div class="center" style="margin: 4px 0;">
            <img src="{{ $imagenPrendaPath }}" style="max-width: 100%; max-height: 150px; border: 1px solid #000;">
        </div>
    @endif

    <div class="row" style="margin-top: 4px;">
        <div class="col bold" style="width: 50%;">POR LA SUMA DE</div>
        <div class="col right bold" style="width: 50%;">$ {{ number_format($prestamo->monto, 0, ',', '.') }}</div>
    </div>

    <div class="hr"></div>

    <div class="legal">
        DECLARO QUE LOS BIENES VENDIDOS SON DE MI EXCLUSIVA PROPIEDAD Y SOBRE ELLOS NO EXISTE
        NINGÚN GRAVAMEN, PRENDA O LIMITACIÓN DE DOMINIO, NO ENCONTRÁNDOSE EMBARGADOS NI
        SECUESTRADOS. ESTE CONTRATO DE COMPRAVENTA QUEDA SOMETIDO AL PACTO DE RETROVENTA
        DE QUE TRATA EL ART. 1937 DEL CÓDIGO CIVIL, EN VIRTUD DEL CUAL EL VENDEDOR SE
        RESERVA LA FACULTAD DE RECOBRAR LOS BIENES VENDIDOS, REEMBOLSANDO AL COMPRADOR
        LA CANTIDAD DE:
    </div>

    <div class="center" style="margin: 4px 0;">
        <span class="valor-destacado">$ {{ number_format($valorReembolso, 0, ',', '.') }}</span>
    </div>

    <div class="legal">
        LA COMPRA QUEDARÁ EXONERADA DE CUALQUIER RECLAMO POR EL DETERIORO DE EL (LOS)
        OBJETO(S) DEL PRESENTE CONTRATO, SI PÉRDIDA POR ROBO O HURTO, ATRACO, INCENDIO,
        CASO FORTUITO O FUERZA MAYOR, POR FALTA DE FUNCIONAMIENTO Y/O POR ÓXIDO, ETC. SI
        EL ARTÍCULO NO SE RECLAMA EN EL PRESENTE CONTRATO, SE AUTORIZA CEDER EL DERECHO
        QUE NACE DEL PACTO DE RETROVENTA.
    </div>

    <div class="hr"></div>

    <div class="footer-note">
        SERVICIO LAS 24 HORAS - Entrega de alhajas de LUNES a SÁBADO de 8 am a 5:30 pm.<br>
        NO SE ENTREGAN DOMINGOS Y FESTIVOS.
    </div>
    <div class="footer-note">
        NO SE HACE RESPONSABLE POR ARTÍCULOS QUE NO SE PRESENTEN CON SU FACTURA ORIGINAL.<br>
        NO SE ADMITEN RECLAMOS.
    </div>

    <div class="hr"></div>

    <div class="fechas">
        <div class="row">
            <div class="col bold" style="width: 55%;">CONTRATO INICIAL</div>
            <div class="col right" style="width: 45%;">{{ optional($prestamo->fecha_inicio)->format('d/m/Y') }}</div>
        </div>
        <div class="row">
            <div class="col bold" style="width: 55%;">TERMINACIÓN CONTRATO</div>
            <div class="col right" style="width: 45%;">{{ $prestamo->fecha_vencimiento ? \Carbon\Carbon::parse($prestamo->fecha_vencimiento)->format('d/m/Y') : '-' }}</div>
        </div>
    </div>

    <div class="firma center">
        Responsable: {{ $responsableNombre ?: '-' }}
    </div>

</body>
</html>
