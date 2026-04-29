<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eficiencia {{ ucfirst($pais) }} — Planet Game</title>
    <style>
        @php 
            $esLandscape = in_array($pais, ['chile', 'colombia', 'australia']); 
            $esPeru = ($pais === 'peru');
            
            $colorPais = match($pais) {
                'chile'     => '#0369a1',
                'colombia'  => '#b91c1c',
                'australia' => '#b45309',
                default     => '#7c3aed',
            };
            
            $colorTexto = match($pais) {
                'chile'     => '#0c4a6e',
                'colombia'  => '#7f1d1d',
                'australia' => '#78350f',
                default     => '#5b21b6',
            };
        @endphp

        @page {
            size: A4 {{ $esLandscape ? 'landscape' : 'portrait' }};
            margin: {{ $esLandscape ? '4mm' : '8mm' }};
        }
        @media print {
            @page { margin: {{ $esLandscape ? '3mm' : '6mm' }}; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: {{ $esLandscape ? '8px' : '9px' }}; color: #1e293b; background: #fff; }
        .page { padding: 8mm 6mm; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 2px solid {{ $colorPais }}; }
        .label { font-size: {{ $esLandscape ? '5.5px' : '6px' }}; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: {{ $colorPais }}; margin-bottom: 2px; }
        h1 { font-size: {{ $esLandscape ? '13px' : '15px' }}; font-weight: 900; color: #0f172a; }
        h1 span { color: {{ $colorPais }}; }
        .sub { font-size: {{ $esLandscape ? '5.5px' : '6px' }}; color: #64748b; margin-top: 2px; }
        .generated { font-size: {{ $esLandscape ? '5.5px' : '6px' }}; color: #94a3b8; }

        .kpis { display: grid; grid-template-columns: repeat(6, 1fr); gap: 4px; margin-bottom: 8px; }
        .kpi { border: 1px solid #e2e8f0; border-radius: 3px; padding: 4px 5px; background: #f8fafc; }
        .kpi .kpi-label { font-size: {{ $esLandscape ? '4.5px' : '5px' }}; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 1px; }
        .kpi .kpi-value { font-size: {{ $esLandscape ? '9px' : '11px' }}; font-weight: 900; color: #0f172a; }
        .kpi.violet { border-color: {{ $colorPais }}; }
        .kpi.violet .kpi-label { color: {{ $colorPais }}; }
        .kpi.violet .kpi-value { color: {{ $colorTexto }}; }
        .kpi.amber { border-color: #f59e0b; background: #fffbeb; }
        .kpi.amber .kpi-label { color: #b45309; }
        .kpi.amber .kpi-value { color: #92400e; }
        .kpi.green { border-color: #10b981; background: #ecfdf5; }
        .kpi.green .kpi-label { color: #047857; }
        .kpi.green .kpi-value { color: #065f46; }
        .kpi.blue { border-color: #3b82f6; background: #eff6ff; }
        .kpi.blue .kpi-label { color: #1d4ed8; }
        .kpi.blue .kpi-value { color: #1e40af; }
        .kpi.red { border-color: #ef4444; background: #fef2f2; }
        .kpi.red .kpi-label { color: #b91c1c; }
        .kpi.red .kpi-value { color: #991b1b; }

        table { width: 100%; border-collapse: collapse; table-layout: auto; }
        thead tr { background: {{ $colorPais }}; }
        thead th { padding: 3px 2px; font-size: {{ $esLandscape ? '5px' : '5.5px' }}; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #fff; text-align: center; border: 1px solid {{ $colorPais }}; white-space: nowrap; }
        thead th.left { text-align: left; }
        thead th.text-left { text-align: left; }

        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td {
            padding: 2.5px 2px;
            font-size: {{ $esLandscape ? '6px' : '6.5px' }};
            color: #334155;
            border: 1px solid #e2e8f0;
            text-align: center;
            white-space: nowrap;
        }
        tbody td.left { text-align: left; }
        tbody td.right { text-align: right; font-variant-numeric: tabular-nums; }
        tbody td.mono  { font-family: 'Courier New', monospace; color: #0891b2; }

        .text-violet { color: #7c3aed !important; }
        .text-amber { color: #f59e0b !important; }
        .text-slate { color: #64748b !important; }
        .text-emerald { color: #059669 !important; }
        .text-blue { color: #2563eb !important; }
        .text-red { color: #dc2626 !important; }
        
        .cell-high { color: #22c55e !important; font-weight: 700; }
        .cell-mid { color: #eab308 !important; font-weight: 700; }
        .cell-low { color: #ef4444 !important; font-weight: 700; }

        tfoot tr { background: #1e293b; }
        tfoot td { padding: 3px 2px; font-size: {{ $esLandscape ? '5px' : '5.5px' }}; font-weight: 700; color: #fff; border: 1px solid #334155; text-align: center; }
        tfoot td.right { text-align: right; }
        tfoot td.label { text-align: right; color: #94a3b8; text-transform: uppercase; }

        .footer { margin-top: 6px; display: flex; justify-content: space-between; font-size: {{ $esLandscape ? '5px' : '5.5px' }}; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 3px; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div>
            <p class="label">Controlar — Producción</p>
            <h1>Revisión <span>por Máquina</span></h1>
            <p class="sub">{{ $fecha }} · {{ ucfirst($pais) }} · {{ count($datos) }} máquinas</p>
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

        $fmtInt = function(int $v): string {
            return number_format($v, 0, '.', ',');
        };

        $total        = array_sum(array_map(fn($r) => (float)($r->total ?? 0), $datos));
        $promDiario   = count($datos)
            ? array_sum(array_map(fn($r) => (float)($r->total_promedio_diario ?? 0), $datos)) / count($datos)
            : 0;
        $horasActivas = array_sum(array_map(fn($r) => (int)($r->horas_con_produccion ?? 0), $datos));
        $horasCero    = array_sum(array_map(fn($r) => (int)($r->horas_con_produccion_cero ?? 0), $datos));
        $horasMuertas = array_sum(array_map(fn($r) => (int)($r->horas_sin_transmitir ?? 0), $datos));

        $totalHoras = $horasActivas + $horasCero + $horasMuertas;
        $horasPorDia = 16;
        $horasTurno = count($datos) * $horasPorDia;
        
        $disponibilidadMedia = $horasTurno > 0 
            ? round((($horasActivas + $horasCero) / $horasTurno) * 100, 1) 
            : 0;
        
        $rendimientoArr = array_filter(array_map(fn($r) => (float)($r->rendimiento ?? 0), $datos));
        $rendimientoMedio = count($rendimientoArr) > 0 
            ? round(array_sum($rendimientoArr) / count($rendimientoArr), 1) 
            : 0;
            
        $eficienciaArr = array_filter(array_map(fn($r) => (float)($r->eficiencia_real ?? 0), $datos));
        $eficienciaMedia = count($eficienciaArr) > 0 
            ? round(array_sum($eficienciaArr) / count($eficienciaArr), 1) 
            : 0;

        $colorEfic = function(float $ef): string {
            if ($ef >= 70) return 'cell-high';
            if ($ef >= 40) return 'cell-mid';
            return 'cell-low';
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
        <div class="kpi amber">
            <p class="kpi-label">Prom/día</p>
            <p class="kpi-value">{{ $fmtVal($promDiario) }}</p>
        </div>
        <div class="kpi green">
            <p class="kpi-label">H. Activas</p>
            <p class="kpi-value">{{ $fmtInt($horasActivas) }}h</p>
        </div>
        <div class="kpi blue">
            <p class="kpi-label">H. Cero</p>
            <p class="kpi-value">{{ $fmtInt($horasCero) }}h</p>
        </div>
        <div class="kpi red">
            <p class="kpi-label">H. Sin Trans.</p>
            <p class="kpi-value">{{ $fmtInt($horasMuertas) }}h</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th class="text-left">Centro</th>
                <th class="text-left">Modelo</th>
                <th>Serie</th>
                <th>Total</th>
                <th>Prom/día</th>
                <th>Prom hist.</th>
                <th>vs Hist.</th>
                <th>H. Activas</th>
                <th>H. Cero</th>
                <th>H. Sin Trans.</th>
                <th>Disp.</th>
                <th>Rend.</th>
                <th>Efic. Real</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $row)
            @php
                $disp = (float)($row->disponibilidad ?? 0);
                $rend = (float)($row->rendimiento ?? 0);
                $efic = (float)($row->eficiencia_real ?? 0);
            @endphp
            <tr>
                <td>{{ $row->item }}</td>
                <td class="left">{{ $row->NombreCentro }}</td>
                <td class="left">{{ $row->Modelo }}</td>
                <td class="mono">{{ $row->Serie }}</td>
                <td class="right font-bold">{{ $fmtVal((float)($row->total ?? 0)) }}</td>
                <td class="right font-bold">{{ $fmtVal((float)($row->total_promedio_diario ?? 0)) }}</td>
                <td class="right text-slate">{{ $fmtVal((float)($row->promedioCentro ?? 0)) }}</td>
                <td class="right font-bold {{ $colorEfic((float)($row->vs_historico ?? 0)) }}">
                    @if($row->vs_historico !== null)
                        {{ ($row->vs_historico > 0 ? '+' : '') . $row->vs_historico }}%
                    @else
                        —
                    @endif
                </td>
                <td class="right font-semibold">{{ $row->horas_con_produccion }}h</td>
                <td class="right font-semibold">{{ $row->horas_con_produccion_cero ?? 0 }}h</td>
                <td class="right font-semibold">{{ $row->horas_sin_transmitir }}h</td>
                <td class="right font-bold {{ $colorEfic($disp) }}">{{ $disp }}%</td>
                <td class="right font-bold {{ $colorEfic($rend) }}">{{ $rend }}%</td>
                <td class="right font-bold {{ $colorEfic($efic) }}">{{ $efic }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="label">Total</td>
                <td class="right font-bold">{{ $fmtVal($total) }}</td>
                <td class="right font-bold">{{ $fmtVal($promDiario) }}</td>
                <td class="right">—</td>
                <td class="right">—</td>
                <td class="right font-bold">{{ $fmtInt($horasActivas) }}h</td>
                <td class="right font-bold">{{ $fmtInt($horasCero) }}h</td>
                <td class="right font-bold">{{ $fmtInt($horasMuertas) }}h</td>
                <td class="right font-bold {{ $colorEfic($disponibilidadMedia) }}">{{ $disponibilidadMedia }}%</td>
                <td class="right font-bold {{ $colorEfic($rendimientoMedio) }}">{{ $rendimientoMedio }}%</td>
                <td class="right font-bold {{ $colorEfic($eficienciaMedia) }}">{{ $eficienciaMedia }}%</td>
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