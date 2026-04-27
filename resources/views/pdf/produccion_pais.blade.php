@php
    $esLandscape = in_array($pais, ['chile', 'colombia', 'australia']);
    $colorPais = match($pais) {
        'chile'     => ['header' => '#0369a1', 'thead' => '#0369a1', 'border' => '#0284c7'],
        'colombia'  => ['header' => '#b91c1c', 'thead' => '#b91c1c', 'border' => '#dc2626'],
        'australia' => ['header' => '#b45309', 'thead' => '#b45309', 'border' => '#d97706'],
        default     => ['header' => '#0e7490', 'thead' => '#0e7490', 'border' => '#0c6478'],
    };
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Producción {{ ucfirst($pais) }} — {{ $fecha }}</title>
    <style>
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: A4 {{ $esLandscape ? 'landscape' : 'portrait' }};
            margin: {{ $esLandscape ? '5mm 6mm' : '12mm' }};
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: {{ $esLandscape ? '6px' : '7px' }};
            color: #1e293b;
            background: #fff;
        }

        .page { padding: {{ $esLandscape ? '4mm 5mm' : '10mm 8mm' }}; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
            padding-bottom: 5px;
            border-bottom: 2px solid {{ $colorPais['header'] }};
        }
        .label {
            font-size: 5.5px; font-weight: 700;
            letter-spacing: 0.15em; text-transform: uppercase;
            color: {{ $colorPais['header'] }}; margin-bottom: 2px;
        }
        h1 { font-size: {{ $esLandscape ? '11px' : '13px' }}; font-weight: 900; color: #0f172a; }
        h1 span { color: {{ $colorPais['header'] }}; }
        .sub { font-size: 5.5px; color: #64748b; margin-top: 2px; }
        .generated { font-size: 5.5px; color: #94a3b8; }

        .kpis {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 3px;
            margin-bottom: 6px;
        }
        .kpi {
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 3px 5px;
            background: #f8fafc;
        }
        .kpi-label {
            font-size: 4.5px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.1em;
            color: #94a3b8; margin-bottom: 1px;
        }
        .kpi-value { font-size: {{ $esLandscape ? '9px' : '10px' }}; font-weight: 900; color: #0f172a; }
        .kpi.accent {
            border-color: {{ $colorPais['header'] }};
            background: #f0f9ff;
        }
        .kpi.accent .kpi-label { color: {{ $colorPais['header'] }}; }
        .kpi.accent .kpi-value { color: {{ $colorPais['header'] }}; }
        .kpi.amber { border-color: #d97706; background: #fffbeb; }
        .kpi.amber .kpi-label { color: #b45309; }
        .kpi.amber .kpi-value { color: #92400e; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }

        thead tr { background: {{ $colorPais['thead'] }} !important; }
        thead th {
            padding: {{ $esLandscape ? '2px 1px' : '3px 2px' }};
            font-size: {{ $esLandscape ? '4px' : '5px' }};
            font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.04em; color: #fff !important;
            text-align: center;
            border: 1px solid {{ $colorPais['border'] }};
            overflow: hidden;
        }
        thead th.left { text-align: left; }

        tbody tr:nth-child(even) { background: #f8fafc !important; }
        tbody tr:nth-child(odd)  { background: #ffffff !important; }
        tbody td {
            padding: {{ $esLandscape ? '1.5px 1px' : '2px' }};
            font-size: {{ $esLandscape ? '5px' : '6px' }};
            color: #334155;
            border: 1px solid #e2e8f0;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        tbody td.left  { text-align: left; }
        tbody td.right { text-align: right; font-variant-numeric: tabular-nums; }
        tbody td.mono  {
            font-family: 'Courier New', monospace;
            font-size: {{ $esLandscape ? '4.5px' : '5.5px' }};
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        tbody td.total { font-weight: 700; }
        tbody td.total-green { background-color: #dbeafe !important; color: #1d4ed8 !important; }
        tbody td.total-red   { background-color: #fee2e2 !important; color: #b91c1c !important; }

        .h-zero    { color: #dc2626 !important; font-weight: 700; }
        .h-low     { color: #64747b; }
        .h-mid     { color: #15803d; font-weight: 600; }
        .h-high    { color: #14532d; font-weight: 700; }
        .h-blue-bg { background-color: #dbeafe !important; color: #2563eb !important; }
        .h-red-bg  { background-color: #fee2e2 !important; color: #dc2626 !important; }

        tfoot tr { background: #1e293b !important; }
        tfoot td {
            padding: {{ $esLandscape ? '2px 1px' : '3px 2px' }};
            font-size: {{ $esLandscape ? '5px' : '6px' }};
            font-weight: 700; color: #fff !important;
            border: 1px solid #334155;
            text-align: center;
        }
        tfoot td.right { text-align: right; }
        tfoot td.label { text-align: right; color: #94a3b8 !important; text-transform: uppercase; }

        @if($esLandscape)
        th.c-num, td.c-num   { width: 14px; }
        th.c-mod, td.c-mod   { width: 42px; }
        th.c-cen, td.c-cen   { width: 62px; }
        th.c-ser, td.c-ser   { width: 38px; max-width: 38px; }
        th.c-hora, td.c-hora { width: 20px; }
        th.c-tot, td.c-tot   { width: 40px; }
        th.c-prom, td.c-prom { width: 36px; }
        @else
        th.c-num, td.c-num   { width: 16px; }
        th.c-mod, td.c-mod   { width: 50px; }
        th.c-cen, td.c-cen   { width: 70px; }
        th.c-ser, td.c-ser   { width: 42px; max-width: 42px; }
        th.c-hora, td.c-hora { width: 22px; }
        th.c-tot, td.c-tot   { width: 55px; }
        th.c-prom, td.c-prom { width: 50px; }
        @endif

        .footer {
            margin-top: 5px;
            display: flex;
            justify-content: space-between;
            font-size: 5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
        }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div>
            <p class="label">Reporte de Producción — {{ ucfirst($pais) }}</p>
            <h1>Producción <span>{{ $fecha }}</span></h1>
            <p class="sub">{{ count($datos) }} máquinas · Horas {{ $horaInicio }}:00 – {{ $horaFin }}:00</p>
        </div>
        <div style="text-align:right">
            <p class="generated">Generado: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    @php
        $horasArr     = range($horaInicio, $horaFin);
        $totalGeneral = array_sum(array_map(fn($r) => (float)($r->total ?? 0), $datos));

        $totalesHora = [];
        $maxPorHora  = [];
        foreach ($horasArr as $h) {
            $vals = array_map(fn($r) => (float)($r->{"h{$h}"} ?? 0), $datos);
            $totalesHora[$h] = array_sum($vals);
            $maxPorHora[$h]  = $vals ? max($vals) : 0;
        }

        $promedioGeneral = count($datos)
            ? array_sum(array_map(fn($r) => (float)($r->promedio ?? 0), $datos)) / count($datos)
            : 0;

        $promsValidos = array_filter(
            array_map(fn($r) => (float)($r->promedio ?? 0), $datos),
            fn($v) => $v > 0
        );
        $promMin = $promsValidos ? min($promsValidos) : 0;
        $promMax = $promsValidos ? max($promsValidos) : 0;

        $bgProm = function(float $v) use ($promMin, $promMax): string {
            if ($v <= 0 || $promMax <= $promMin) return '';
            $pct = ($v - $promMin) / ($promMax - $promMin);
            if ($pct < 0.5) {
                $t = $pct * 2;
                $r = 252; $g = (int)(165 + (211 - 165) * $t); $b = (int)(165 + (6 - 165) * $t);
            } else {
                $t = ($pct - 0.5) * 2;
                $r = (int)(252 - (252 - 134) * $t); $g = (int)(211 + (239 - 211) * $t); $b = (int)(6 + (122 - 6) * $t);
            }
            return "background-color: rgba({$r},{$g},{$b},0.35) !important; color: #1e293b !important; font-weight: 700;";
        };

        $fmtVal = function(float $v, bool $soloDecimalesCero = false): string {
            if ($v == 0) return number_format(0, 2, '.', ',');
            if ($soloDecimalesCero) {
                return fmod(round($v, 2), 1) == 0
                    ? number_format($v, 0, '.', ',')
                    : number_format($v, 2, '.', ',');
            }
            return number_format($v, 2, '.', ',');
        };

        $esPeru = ($pais === 'peru');

        $fmtHora = function(float $v): string {
            if ($v <= 0) return number_format(0, 2, '.', ',');
            if ($v >= 1_000_000_000) return rtrim(rtrim(number_format($v/1_000_000_000, 2, '.', ''), '0'), '.') . 'MM';
            if ($v >= 1_000_000)     return rtrim(rtrim(number_format($v/1_000_000,     2, '.', ''), '0'), '.') . 'M';
            if ($v >= 1_000)         return rtrim(rtrim(number_format($v/1_000,         2, '.', ''), '0'), '.') . 'K';
            return fmod(round($v, 2), 1) == 0
                ? number_format($v, 0, '.', ',')
                : number_format($v, 2, '.', ',');
        };

        $clsIntensidad = function(float $val, float $max, int $transmitio): string {
            if ($val > 0) {
                if ($max <= 0) return '';
                $pct = $val / $max;
                if ($pct >= 0.8) return 'h-high';
                if ($pct >= 0.5) return 'h-mid';
                if ($pct >= 0.2) return 'h-low';
                return '';
            }
            if ($transmitio) return 'h-blue-bg';
            return 'h-red-bg';
        };

        $colorTotal = function($total, $promedioCentro): string {
            if (!$total || !$promedioCentro) return '';
            return ((float)$total >= (float)$promedioCentro) ? 'total-green' : 'total-red';
        };
    @endphp

    <div class="kpis">
        <div class="kpi">
            <p class="kpi-label">Máquinas</p>
            <p class="kpi-value">{{ count($datos) }}</p>
        </div>
        <div class="kpi accent">
            <p class="kpi-label">Total</p>
            <p class="kpi-value">{{ $fmtVal($totalGeneral, !$esPeru) }}</p>
        </div>
        <div class="kpi amber">
            <p class="kpi-label">Prom/hora</p>
            <p class="kpi-value">{{ $fmtVal($promedioGeneral, !$esPeru) }}</p>
        </div>
        <div class="kpi">
            <p class="kpi-label">Horas turno</p>
            <p class="kpi-value">{{ $horaFin - $horaInicio + 1 }}h</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="c-num">#</th>
                <th class="c-mod left">Modelo</th>
                <th class="c-cen left">Centro</th>
                <th class="c-ser">Serie</th>
                @foreach($horasArr as $h)
                <th class="c-hora">{{ $h }}h</th>
                @endforeach
                <th class="c-tot">Total</th>
                <th class="c-prom">Prom/h</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $i => $row)
                @php
                    $horasTrab = 0;
                    $totalFila = 0;
                    foreach ($horasArr as $h) {
                        $v = (float)($row->{"h{$h}"} ?? 0);
                        if ($v > 0) { $horasTrab++; $totalFila += $v; }
                    }
                    $promFila  = $horasTrab > 0 ? $totalFila / $horasTrab : 0;
                    $totalCls  = $colorTotal($row->total ?? 0, $row->promedioCentro ?? 0);
                    $bgPromStr = $bgProm($promFila);
                @endphp
                <tr>
                    <td class="c-num">{{ $i + 1 }}</td>
                    <td class="c-mod left">{{ $row->modelo }}</td>
                    <td class="c-cen left">{{ $row->centro }}</td>
                    <td class="c-ser mono">{{ $row->serie }}</td>
                    @foreach($horasArr as $h)
                    @php
                        $val       = (float)($row->{"h{$h}"} ?? 0);
                        $transmitio = (int)($row->{"trans_h{$h}"} ?? 0);
                        $cls       = $clsIntensidad($val, $maxPorHora[$h] ?? 0, $transmitio);
                    @endphp
                    <td class="c-hora right {{ $cls }}">{{ $fmtHora($val) }}</td>
                    @endforeach
                    <td class="c-tot right total {{ $totalCls }}">{{ $fmtVal((float)($row->total ?? 0), !$esPeru) }}</td>
                    <td class="c-prom right" style="{{ $bgPromStr }}">{{ $fmtVal($promFila, !$esPeru) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="label">Total</td>
                @foreach($horasArr as $h)
                <td class="c-hora right">{{ $fmtHora($totalesHora[$h]) }}</td>
                @endforeach
                <td class="c-tot right" style="color:#6ee7b7 !important">{{ $fmtVal($totalGeneral, !$esPeru) }}</td>
                <td class="c-prom right" style="color:#fcd34d !important">{{ $fmtVal($promedioGeneral, !$esPeru) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <span>Sistema de Producción LATAM</span>
        <span>{{ ucfirst($pais) }} · {{ $fecha }} · {{ count($datos) }} registros</span>
    </div>
</div>
<script>document.title = 'Producción {{ ucfirst($pais) }} - {{ $fecha }}';</script>
</body>
</html>