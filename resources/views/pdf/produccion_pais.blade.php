<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Producción {{ ucfirst($pais) }} — {{ $fecha }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 8px;
            color: #1e293b;
            background: #fff;
        }

        .page {
            padding: 12mm 10mm;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #0e7490;
        }

        .header-left .label {
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #0e7490;
            margin-bottom: 3px;
        }

        .header-left h1 {
            font-size: 16px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .header-left h1 span {
            color: #0e7490;
        }

        .header-left .sub {
            font-size: 7px;
            color: #64748b;
            margin-top: 3px;
        }

        .header-right {
            text-align: right;
        }

        .header-right .generated {
            font-size: 7px;
            color: #94a3b8;
        }

        .kpis {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 10px;
        }

        .kpi {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 8px;
            background: #f8fafc;
        }

        .kpi .kpi-label {
            font-size: 6px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .kpi .kpi-value {
            font-size: 12px;
            font-weight: 900;
            color: #0f172a;
        }

        .kpi.green  { border-color: #059669; background: #ecfdf5; }
        .kpi.green  .kpi-label { color: #059669; }
        .kpi.green  .kpi-value { color: #065f46; }

        .kpi.amber  { border-color: #d97706; background: #fffbeb; }
        .kpi.amber  .kpi-label { color: #d97706; }
        .kpi.amber  .kpi-value { color: #92400e; }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        thead tr {
            background: #0e7490;
        }

        thead th {
            padding: 4px 3px;
            font-size: 6.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #fff;
            text-align: center;
            border: 1px solid #0c6478;
            white-space: nowrap;
        }

        thead th.left { text-align: left; }

        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:nth-child(odd)  { background: #fff; }

        tbody tr:hover { background: #f0f9ff; }

        tbody td {
            padding: 3px 3px;
            font-size: 7px;
            color: #334155;
            border: 1px solid #e2e8f0;
            text-align: center;
            white-space: nowrap;
        }

        tbody td.left   { text-align: left; }
        tbody td.mono   { font-family: 'Courier New', monospace; font-size: 6.5px; color: #0e7490; }
        tbody td.num    { text-align: right; font-variant-numeric: tabular-nums; }
        tbody td.total  { font-weight: 700; color: #065f46; text-align: right; }
        tbody td.prom   { font-weight: 700; color: #92400e; text-align: right; }
        tbody td.center-name { max-width: 120px; overflow: hidden; text-overflow: ellipsis; }

        .h-zero { color: #cbd5e1; }
        .h-low  { color: #6b7280; }
        .h-mid  { color: #15803d; }
        .h-high { color: #065f46; font-weight: 600; }

        tfoot tr {
            background: #1e293b;
        }

        tfoot td {
            padding: 4px 3px;
            font-size: 7.5px;
            font-weight: 800;
            color: #fff;
            border: 1px solid #334155;
            text-align: center;
        }

        tfoot td.num { text-align: right; color: #6ee7b7; }
        tfoot td.label { text-align: right; color: #94a3b8; letter-spacing: 0.08em; text-transform: uppercase; }

        .footer {
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 6.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div class="header-left">
            <p class="label">Reporte de Producción — {{ ucfirst($pais) }}</p>
            <h1>Producción <span>{{ $fecha }}</span></h1>
            <p class="sub">{{ count($datos) }} máquinas · Horas {{ $horaInicio }}:00 – {{ $horaFin }}:00</p>
        </div>
        <div class="header-right">
            <p class="generated">Generado: {{ now()->format('d/m/Y H:i') }}</p>
            @if($tipoCambio && $tipoCambio != 1)
            <p class="generated" style="margin-top:2px;">T/C: {{ number_format($tipoCambio, 4) }}</p>
            @endif
        </div>
    </div>

    @php
        $horasArr    = range($horaInicio, $horaFin);
        $totalGeneral = array_sum(array_map(fn($r) => (float)($r->total ?? 0), $datos));

        $totalesHora = [];
        foreach ($horasArr as $h) {
            $totalesHora[$h] = array_sum(array_map(fn($r) => (float)($r->{"h{$h}"} ?? 0), $datos));
        }

        $maxPorHora = [];
        foreach ($horasArr as $h) {
            $maxPorHora[$h] = max(array_map(fn($r) => (float)($r->{"h{$h}"} ?? 0), $datos) ?: [0]);
        }

        $promedioGeneral = count($datos)
            ? array_sum(array_map(fn($r) => (float)($r->promedio ?? 0), $datos)) / count($datos)
            : 0;

        $fmt = fn($v) => number_format((float)$v, 2, '.', ',');
    @endphp

    <div class="kpis">
        <div class="kpi">
            <p class="kpi-label">Máquinas</p>
            <p class="kpi-value">{{ count($datos) }}</p>
        </div>
        <div class="kpi green">
            <p class="kpi-label">Total producción</p>
            <p class="kpi-value">{{ $fmt($totalGeneral) }}</p>
        </div>
        <div class="kpi amber">
            <p class="kpi-label">Promedio / hora</p>
            <p class="kpi-value">{{ $fmt($promedioGeneral) }}</p>
        </div>
        <div class="kpi">
            <p class="kpi-label">Horas activas</p>
            <p class="kpi-value">{{ $horaFin - $horaInicio + 1 }}h</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:20px">#</th>
                <th class="left" style="width:70px">Modelo</th>
                <th class="left" style="width:100px">Centro</th>
                <th style="width:55px">Serie</th>
                @foreach($horasArr as $h)
                    <th>{{ $h }}h</th>
                @endforeach
                <th style="width:52px">Total</th>
                <th style="width:45px">Prom/h</th>
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
                $promFila = $horasTrab > 0 ? $totalFila / $horasTrab : 0;
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="left">{{ $row->modelo }}</td>
                <td class="left center-name">{{ $row->centro }}</td>
                <td class="mono">{{ $row->serie }}</td>
                @foreach($horasArr as $h)
                @php
                    $val = (float)($row->{"h{$h}"} ?? 0);
                    $max = $maxPorHora[$h] ?? 0;
                    $cls = 'h-zero';
                    if ($val > 0 && $max > 0) {
                        $pct = $val / $max;
                        $cls = $pct >= 0.8 ? 'h-high' : ($pct >= 0.5 ? 'h-mid' : 'h-low');
                    }
                @endphp
                <td class="num {{ $cls }}">{{ $val > 0 ? $fmt($val) : '—' }}</td>
                @endforeach
                <td class="total">{{ $fmt($row->total) }}</td>
                <td class="prom">{{ $fmt($promFila) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="label">Total</td>
                @foreach($horasArr as $h)
                    <td class="num">{{ $totalesHora[$h] > 0 ? $fmt($totalesHora[$h]) : '—' }}</td>
                @endforeach
                <td class="num" style="color:#6ee7b7">{{ $fmt($totalGeneral) }}</td>
                <td class="num" style="color:#fcd34d">{{ $fmt($promedioGeneral) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <span>Sistema de Producción LATAM</span>
        <span>{{ ucfirst($pais) }} · {{ $fecha }} · {{ count($datos) }} registros</span>
    </div>

</div>
</body>
</html>