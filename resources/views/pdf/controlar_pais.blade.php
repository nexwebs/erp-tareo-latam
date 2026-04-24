<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controlar Producción {{ ucfirst($pais) }} — {{ $fecha }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 8px; color: #1e293b; background: #fff; }
        .page { padding: 12mm 10mm; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 2px solid #7c3aed; }
        .label { font-size: 7px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: #7c3aed; margin-bottom: 3px; }
        h1 { font-size: 16px; font-weight: 900; color: #0f172a; }
        h1 span { color: #7c3aed; }
        .sub { font-size: 7px; color: #64748b; margin-top: 3px; }
        .header-right { text-align: right; }
        .generated { font-size: 7px; color: #94a3b8; }
        
        .kpis { display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; margin-bottom: 10px; }
        .kpi { border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 8px; background: #f8fafc; }
        .kpi .kpi-label { font-size: 6px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 2px; }
        .kpi .kpi-value { font-size: 12px; font-weight: 900; color: #0f172a; }
        .kpi.violet { border-color: #7c3aed; background: #f5f3ff; }
        .kpi.violet .kpi-label { color: #7c3aed; }
        
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #7c3aed; }
        thead th { padding: 4px 3px; font-size: 6px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #fff; text-align: center; border: 1px solid #6d28d9; white-space: nowrap; }
        thead th.left { text-align: left; }
        
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:nth-child(odd) { background: #fff; }
        tbody td { padding: 3px; font-size: 6.5px; color: #334155; border: 1px solid #e2e8f0; text-align: center; }
        tbody td.left { text-align: left; }
        tbody td.num { text-align: right; font-variant-numeric: tabular-nums; }
        tbody td.mono { font-family: 'Courier New', monospace; color: #0891b2; }
        
        .bg-green { color: #059669; }
        .bg-red { color: #dc2626; }
        .bg-amber { color: #d97706; }
        
        tfoot tr { background: #1e293b; }
        tfoot td { padding: 4px 3px; font-size: 7px; font-weight: 700; color: #fff; border: 1px solid #334155; text-align: center; }
        tfoot td.num { text-align: right; }
        tfoot td.label { text-align: right; color: #94a3b8; text-transform: uppercase; }
        
        .footer { margin-top: 8px; display: flex; justify-content: space-between; font-size: 6px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 4px; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="header-left">
            <p class="label">Controlar Producción — {{ ucfirst($pais) }}</p>
            <h1>Revisión <span>por Máquina</span></h1>
            <p class="sub">{{ $fecha }} · {{ count($datos) }} máquinas</p>
        </div>
        <div class="header-right">
            <p class="generated">Generado: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    @php
        $total = array_sum(array_map(fn($r) => (float)($r->total ?? 0), $datos));
        $promDiario = count($datos) ? array_sum(array_map(fn($r) => (float)($r->total_promedio_diario ?? 0), $datos)) / count($datos) : 0;
        $horasActivas = array_sum(array_map(fn($r) => (int)($r->horas_con_produccion ?? 0), $datos));
        $horasMuertas = array_sum(array_map(fn($r) => (int)($r->horas_sin_transmitir ?? 0), $datos));
        $eficienciaTotal = ($horasActivas + $horasMuertas) > 0 ? round(($horasActivas / ($horasActivas + $horasMuertas)) * 100, 1) : 0;
        $fmt = fn($v) => number_format((float)$v, 2, '.', ',');
    @endphp

    <div class="kpis">
        <div class="kpi">
            <p class="kpi-label">Máquinas</p>
            <p class="kpi-value">{{ count($datos) }}</p>
        </div>
        <div class="kpi violet">
            <p class="kpi-label">Total</p>
            <p class="kpi-value">{{ $fmt($total) }}</p>
        </div>
        <div class="kpi">
            <p class="kpi-label">Prom/día</p>
            <p class="kpi-value">{{ $fmt($promDiario) }}</p>
        </div>
        <div class="kpi">
            <p class="kpi-label">H. activas</p>
            <p class="kpi-value">{{ $horasActivas }}h</p>
        </div>
        <div class="kpi">
            <p class="kpi-label">H. muertas</p>
            <p class="kpi-value">{{ $horasMuertas }}h</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:20px">#</th>
                <th class="left" style="width:80px">Centro</th>
                <th class="left" style="width:60px">Modelo</th>
                <th style="width:50px">Serie</th>
                <th>Total</th>
                <th>Prom/día</th>
                <th>H.Act</th>
                <th>H.Muertas</th>
                <th>Efic.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $row)
            <tr>
                <td>{{ $row->item }}</td>
                <td class="left">{{ $row->NombreCentro }}</td>
                <td class="left">{{ $row->Modelo }}</td>
                <td class="mono">{{ $row->Serie }}</td>
                <td class="num">{{ $fmt($row->total) }}</td>
                <td class="num">{{ $fmt($row->total_promedio_diario) }}</td>
                <td class="num bg-green">{{ $row->horas_con_produccion }}h</td>
                <td class="num bg-red">{{ $row->horas_sin_transmitir }}h</td>
                <td class="num">{{ $row->eficiencia }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="label">Total</td>
                <td class="num">{{ $fmt($total) }}</td>
                <td class="num">{{ $fmt($promDiario) }}</td>
                <td class="num">{{ $horasActivas }}h</td>
                <td class="num">{{ $horasMuertas }}h</td>
                <td class="num">{{ $eficienciaTotal }}%</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <span>Sistema de Producción LATAM</span>
        <span>{{ ucfirst($pais) }} · {{ $fecha }} · {{ count($datos) }} máquinas</span>
    </div>
</div>
</body>
</html>