<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controlar {{ ucfirst($pais) }} — Planet Game</title>
    <style>
        @php $esLandscape = in_array($pais, ['chile', 'colombia', 'australia']); @endphp

        @page {
            size: A4 {{ $esLandscape ? 'landscape' : 'portrait' }};
            margin: {{ $esLandscape ? '5mm' : '12mm' }};
        }
        @media print {
            @page { margin: {{ $esLandscape ? '4mm' : '10mm' }}; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 7px; color: #1e293b; background: #fff; }
        .page { padding: 10mm 8mm; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 2px solid #7c3aed; }
        .label { font-size: 6px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: #7c3aed; margin-bottom: 2px; }
        h1 { font-size: 13px; font-weight: 900; color: #0f172a; }
        h1 span { color: #7c3aed; }
        .sub { font-size: 6px; color: #64748b; margin-top: 2px; }
        .generated { font-size: 6px; color: #94a3b8; }

        .kpis { display: grid; grid-template-columns: repeat(5, 1fr); gap: 4px; margin-bottom: 8px; }
        .kpi { border: 1px solid #e2e8f0; border-radius: 4px; padding: 4px 6px; background: #f8fafc; }
        .kpi .kpi-label { font-size: 5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 1px; }
        .kpi .kpi-value { font-size: 10px; font-weight: 900; color: #0f172a; }
        .kpi.violet { border-color: #7c3aed; background: #f5f3ff; }
        .kpi.violet .kpi-label { color: #7c3aed; }
        .kpi.violet .kpi-value { color: #7c3aed; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead tr { background: #7c3aed; }
        thead th { padding: 3px 2px; font-size: 5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #fff; text-align: center; border: 1px solid #6d28d9; overflow: hidden; }
        thead th.left { text-align: left; }

        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td {
            padding: 2px;
            font-size: 6px;
            color: #334155;
            border: 1px solid #e2e8f0;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        tbody td.left { text-align: left; }
        tbody td.right { text-align: right; font-variant-numeric: tabular-nums; }
        tbody td.mono  { font-family: 'Courier New', monospace; color: #0891b2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .cell-above { background-color: #dbeafe !important; color: #1d4ed8 !important; font-weight: 700; }
        .cell-below { background-color: #fee2e2 !important; color: #b91c1c !important; font-weight: 700; }

        tfoot tr { background: #1e293b; }
        tfoot td { padding: 3px 2px; font-size: 6px; font-weight: 700; color: #fff; border: 1px solid #334155; text-align: center; }
        tfoot td.right { text-align: right; }
        tfoot td.label { text-align: right; color: #94a3b8; text-transform: uppercase; }

        th.c-num,  td.c-num   { width: 18px; }
        th.c-cen,  td.c-cen   { width: 80px; max-width: 80px; }
        th.c-mod,  td.c-mod   { width: 60px; max-width: 60px; }
        th.c-ser,  td.c-ser   { width: 50px; max-width: 50px; }
        th.c-tot,  td.c-tot   { width: 55px; }
        th.c-prom, td.c-prom  { width: 50px; }
        th.c-hist, td.c-hist  { width: 50px; }
        th.c-vs,   td.c-vs    { width: 32px; }
        th.c-h,    td.c-h     { width: 30px; }
        th.c-efic, td.c-efic  { width: 32px; }

        .footer { margin-top: 6px; display: flex; justify-content: space-between; font-size: 5.5px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 3px; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div>
            <p class="label">Controlar Producción — {{ ucfirst($pais) }}</p>
            <h1>Revisión <span>por Máquina</span></h1>
            <p class="sub">{{ $fecha }} · {{ count($datos) }} máquinas</p>
        </div>
        <div>
            <p class="generated">Generado: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    @php
        $esPeru = ($pais === 'peru');

        $fmtVal = function(float $v) use ($esPeru): string {
            if ($v == 0) return number_format(0, 2, '.', ',');
            if ($esPeru) return number_format($v, 2, '.', ',');
            return fmod(round($v, 2), 1) == 0
                ? number_format($v, 0, '.', ',')
                : number_format($v, 2, '.', ',');
        };

        $total        = array_sum(array_map(fn($r) => (float)($r->total ?? 0), $datos));
        $promDiario   = count($datos)
            ? array_sum(array_map(fn($r) => (float)($r->total_promedio_diario ?? 0), $datos)) / count($datos)
            : 0;
        $horasActivas = array_sum(array_map(fn($r) => (int)($r->horas_con_produccion ?? 0), $datos));
        $horasCero    = array_sum(array_map(fn($r) => (int)($r->horas_con_produccion_cero ?? 0), $datos));
        $horasMuertas = array_sum(array_map(fn($r) => (int)($r->horas_sin_transmitir ?? 0), $datos));
        $totalHoras   = $horasActivas + $horasCero + $horasMuertas;

        $eficienciaTotal = $totalHoras > 0 ? round(($horasActivas / $totalHoras) * 100, 1) : 0;
        $eficienciaCero  = $totalHoras > 0 ? round((($horasActivas + $horasCero) / $totalHoras) * 100, 1) : 0;

        $grupos = [];
        foreach ($datos as $r) {
            $c = $r->NombreCentro ?? '';
            $grupos[$c][] = (float)($r->total ?? 0);
        }
        $promPorCentro = [];
        foreach ($grupos as $c => $vals) {
            $promPorCentro[$c] = array_sum($vals) / count($vals);
        }

        $promsValidos = array_filter(
            array_map(fn($r) => (float)($r->total_promedio_diario ?? 0), $datos),
            fn($v) => $v > 0
        );
        $mediaPromDiario = count($promsValidos) ? array_sum($promsValidos) / count($promsValidos) : 0;
        $promMin = $promsValidos ? min($promsValidos) : 0;
        $promMax = $promsValidos ? max($promsValidos) : 0;

        // Handle outliers: if range is too large, use median-based range for better visualization
        if ($promMin > 0 && $promMax / $promMin > 5 && count($promsValidos) > 5) {
            $sortedVals = array_values($promsValidos);
            sort($sortedVals);
            $mid = count($sortedVals) / 2;
            $median = $sortedVals[(int)$mid] ?? $promMin;
            // Use median ± 50% for colored range instead of full range
            $promMin = max(0, $median * 0.5);
            $promMax = $median * 1.5;
        }

        $bgProm = function(float $v) use ($mediaPromDiario): string {
            if ($v <= 0 || $mediaPromDiario <= 0) return '';
            $pct = $v / $mediaPromDiario;
            if ($pct > 0.25) {
                return 'background-color: rgba(134, 239, 122, 0.5) !important; color: #1e293b !important; font-weight: 700;';
            }
            if ($pct >= 0.20) {
                return 'background-color: rgba(252, 211, 6, 0.45) !important; color: #1e293b !important; font-weight: 700;';
            }
            return 'background-color: rgba(252, 165, 165, 0.6) !important; color: #1e293b !important; font-weight: 700;';
        };
    @endphp

    <div class="kpis">
        <div class="kpi">
            <p class="kpi-label">Máquinas</p>
            <p class="kpi-value">{{ count($datos) }}</p>
        </div>
        <div class="kpi violet">
            <p class="kpi-label">Total</p>
            <p class="kpi-value">{{ $fmtVal($total) }}</p>
        </div>
        <div class="kpi">
            <p class="kpi-label">Prom/día</p>
            <p class="kpi-value">{{ $fmtVal($promDiario) }}</p>
        </div>
        <div class="kpi">
            <p class="kpi-label">H. activas</p>
            <p class="kpi-value">{{ $horasActivas }}h</p>
        </div>
        <div class="kpi">
            <p class="kpi-label">H. cero</p>
            <p class="kpi-value">{{ $horasCero }}h</p>
        </div>
        <div class="kpi">
            <p class="kpi-label">H. muertas</p>
            <p class="kpi-value">{{ $horasMuertas }}h</p>
        </div>
        <div class="kpi">
            <p class="kpi-label">Efic.</p>
            <p class="kpi-value">{{ $eficienciaTotal }}%</p>
        </div>
        <div class="kpi">
            <p class="kpi-label">Efic. cero</p>
            <p class="kpi-value">{{ $eficienciaCero }}%</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="c-num">#</th>
                <th class="c-cen left">Centro</th>
                <th class="c-mod left">Modelo</th>
                <th class="c-ser">Serie</th>
                <th class="c-tot">Total</th>
                <th class="c-prom">Prom/día</th>
                <th class="c-hist">Prom.hist</th>
                <th class="c-vs">vsHist</th>
                <th class="c-h">H.Act</th>
                <th class="c-h">H.Cero</th>
                <th class="c-h">H.Muertas</th>
                <th class="c-efic">Efic.</th>
                <th class="c-efic">Efic.Cero</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $row)
            @php
                $totalRow   = (float)($row->total ?? 0);
                $promCentro = $promPorCentro[$row->NombreCentro ?? ''] ?? 0;
                $claseTotal = $totalRow >= $promCentro ? 'cell-above' : 'cell-below';

                $promRow    = (float)($row->total_promedio_diario ?? 0);
                $bgPromStr  = $bgProm($promRow);
            @endphp
            <tr>
                <td class="c-num">{{ $row->item }}</td>
                <td class="c-cen left">{{ $row->NombreCentro }}</td>
                <td class="c-mod left">{{ $row->Modelo }}</td>
                <td class="c-ser mono">{{ $row->Serie }}</td>
                <td class="c-tot right {{ $claseTotal }}">{{ $fmtVal($totalRow) }}</td>
                <td class="c-prom right" style="{{ $bgPromStr }}">{{ $fmtVal($promRow) }}</td>
                <td class="c-hist right">{{ $fmtVal((float)($row->promedioCentro ?? 0)) }}</td>
                <td class="c-vs right">{{ $row->vs_historico !== null ? ($row->vs_historico > 0 ? '+' : '') . $row->vs_historico . '%' : '—' }}</td>
                <td class="c-h right" style="color:#059669">{{ $row->horas_con_produccion }}h</td>
                <td class="c-h right" style="color:#2563eb">{{ $row->horas_con_produccion_cero ?? 0 }}h</td>
                <td class="c-h right" style="color:#dc2626">{{ $row->horas_sin_transmitir }}h</td>
                <td class="c-efic right">{{ $row->eficiencia }}%</td>
                <td class="c-efic right">{{ $row->eficiencia_cero ?? 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="label">Total</td>
                <td class="c-tot right">{{ $fmtVal($total) }}</td>
                <td class="c-prom right" style="color:#fcd34d !important">{{ $fmtVal($promDiario) }}</td>
                <td class="c-hist right">—</td>
                <td class="c-vs right">—</td>
                <td class="c-h right">{{ $horasActivas }}h</td>
                <td class="c-h right">{{ $horasCero }}h</td>
                <td class="c-h right">{{ $horasMuertas }}h</td>
                <td class="c-efic right">{{ $eficienciaTotal }}%</td>
                <td class="c-efic right">{{ $eficienciaCero }}%</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <span>Planet Game</span>
        <span>{{ ucfirst($pais) }} · {{ $fecha }} · {{ count($datos) }} máquinas</span>
    </div>
</div>
<script>document.title = 'Controlar {{ ucfirst($pais) }} - Planet Game';</script>
</body>
</html>