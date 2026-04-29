@php
    $colorPais = match($pais) {
        'chile'     => ['header' => '#0369a1', 'border' => '#0284c7'],
        'colombia'  => ['header' => '#b91c1c', 'border' => '#dc2626'],
        'australia' => ['header' => '#b45309', 'border' => '#d97706'],
        default     => ['header' => '#0e7490', 'border' => '#0c6478'],
    };

    $layoutPais = match($pais) {
        'peru' => [
            'mmDisponible' => 150,
            'mmHeader'     => 12,
            'mmThead'      => 6,
            'mmTfoot'      => 6,
            'mmFooter'     => 4,
            'mmPageLabel'  => 3,
        ],
        'chile' => [
            'mmDisponible' => 135,
            'mmHeader'     => 15,
            'mmThead'      => 7,
            'mmTfoot'      => 7,
            'mmFooter'     => 5,
            'mmPageLabel'  => 4,
        ],
        'colombia' => [
            'mmDisponible' => 140,
            'mmHeader'     => 15,
            'mmThead'      => 7,
            'mmTfoot'      => 7,
            'mmFooter'     => 5,
            'mmPageLabel'  => 4,
        ],
        'australia' => [
            'mmDisponible' => 138,
            'mmHeader'     => 14,
            'mmThead'      => 7,
            'mmTfoot'      => 7,
            'mmFooter'     => 5,
            'mmPageLabel'  => 4,
        ],
        default => [
            'mmDisponible' => 135,
            'mmHeader'     => 15,
            'mmThead'      => 7,
            'mmTfoot'      => 7,
            'mmFooter'     => 5,
            'mmPageLabel'  => 4,
        ],
    };

    $mmDisponible = $layoutPais['mmDisponible'];
    $mmHeader     = $layoutPais['mmHeader'];
    $mmThead      = $layoutPais['mmThead'];
    $mmTfoot      = $layoutPais['mmTfoot'];
    $mmFooter     = $layoutPais['mmFooter'];
    $mmPageLabel  = $layoutPais['mmPageLabel'];

    $horasArr        = range($horaInicio, $horaFin);
    $numHoras        = count($horasArr);
    $totalGeneral    = array_sum(array_map(fn($r) => (float)($r->total ?? 0), $datos));
    $promedioGeneral = count($datos)
        ? array_sum(array_map(fn($r) => (float)($r->promedio ?? 0), $datos)) / count($datos)
        : 0;

    $totalesHora = [];
    $maxPorHora  = [];
    foreach ($horasArr as $h) {
        $vals            = array_map(fn($r) => (float)($r->{"h{$h}"} ?? 0), $datos);
        $totalesHora[$h] = array_sum($vals);
        $maxPorHora[$h]  = $vals ? max($vals) : 0;
    }

    $clsIntensidad = function(float $val, float $maxHora, int $transmitio): string {
        if ($val > 0) {
            $pct = $maxHora > 0 ? $val / $maxHora : 0;
            if ($pct >= 0.8) return 'h-max';
            if ($pct >= 0.5) return 'h-high';
            if ($pct >= 0.2) return 'h-mid';
            return 'h-low';
        }
        return $transmitio ? 'h-zero-trans' : 'h-zero-red';
    };

    $clsBgPromedio = function(int $colorCentro): string {
        return match($colorCentro) {
            1 => 'prom-verde',
            2 => 'prom-amarillo',
            3 => 'prom-rojo',
            default => '',
        };
    };

    $clsTotal = function(float $total, float $promedio): string {
        return ($total >= $promedio) ? 'total-green' : 'total-red';
    };

    $fmt = function(float $v): string {
        if ($v == 0) return number_format(0, 2, ',', '.');
        $rounded = round($v, 2);
        return (fmod($rounded, 1) == 0)
            ? number_format($rounded, 0, ',', '.')
            : number_format($rounded, 2, ',', '.');
    };

    $fmtHora = function(float $v) use ($fmt): string {
        if ($v >= 1_000_000_000) return rtrim(rtrim(number_format($v / 1_000_000_000, 2, ',', ''), '0'), ',') . 'MM';
        if ($v >= 1_000_000)     return rtrim(rtrim(number_format($v / 1_000_000,     2, ',', ''), '0'), ',') . 'M';
        if ($v >= 1_000)         return rtrim(rtrim(number_format($v / 1_000,         2, ',', ''), '0'), ',') . 'K';
        return $fmt($v);
    };

    $totalRegistros = count($datos);
    $anchoHora      = $numHoras > 0 ? max(24, min(36, (int)(504 / $numHoras))) : 31;

    $fsTd    = $numHoras > 16 ? '9px'  : '10px';
    $fsTh    = $fsTd;
    $fsLabel = '10px';
    $fsH1    = '16px';

    $fsTdPx               = $numHoras > 16 ? 9 : 10;
    $alturaFilaMm          = ($fsTdPx * 1.4 + 4 + 1) * 0.2646;

    $mmParaFilasPrimera    = $mmDisponible - $mmHeader - $mmThead - $mmTfoot - $mmFooter;
    $mmParaFilasSiguientes = $mmDisponible - $mmPageLabel - $mmThead - $mmTfoot - $mmFooter;

    $registrosPrimera    = max(1, (int) floor($mmParaFilasPrimera    / $alturaFilaMm));
    $registrosSiguientes = max(1, (int) floor($mmParaFilasSiguientes / $alturaFilaMm));

    $chunks = [];
    if ($totalRegistros <= $registrosPrimera) {
        $chunks[] = $datos;
    } else {
        $chunks[] = array_slice($datos, 0, $registrosPrimera);
        $offset   = $registrosPrimera;
        while ($offset < $totalRegistros) {
            $chunks[] = array_slice($datos, $offset, $registrosSiguientes);
            $offset  += $registrosSiguientes;
        }
    }

    $totalPaginas = count($chunks);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Producción {{ ucfirst($pais) }} — Planet Game</title>
<style>
* { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; margin: 0; padding: 0; box-sizing: border-box; }

@page { size: A4 landscape; margin: 6mm; }

body { font-family: 'Segoe UI', Arial, sans-serif; font-size: {{ $fsTd }}; color: #1e293b; background: #fff; }

.page { page-break-after: always; }
.page:last-child { page-break-after: auto; }

.header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 5px;
    padding-bottom: 4px;
    border-bottom: 2px solid {{ $colorPais['header'] }};
}
.label-pais { font-size: {{ $fsLabel }}; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: {{ $colorPais['header'] }}; margin-bottom: 2px; }
h1 { font-size: {{ $fsH1 }}; font-weight: 900; color: #0f172a; }
h1 span { color: {{ $colorPais['header'] }}; }
.sub { font-size: {{ $fsLabel }}; color: #64748b; margin-top: 3px; }
.generated { font-size: {{ $fsLabel }}; color: #94a3b8; text-align: right; }

.page-label {
    font-size: {{ $fsLabel }};
    color: #94a3b8;
    text-align: right;
    margin-bottom: 3px;
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

thead { display: table-header-group; }
tfoot { display: table-footer-group; }

tbody tr { page-break-inside: avoid; break-inside: avoid; }

th.c-num,  td.c-num  { width: 18px;  min-width: 18px;  max-width: 18px; }
th.c-mod,  td.c-mod  { width: 52px;  min-width: 52px;  max-width: 52px; }
th.c-cen,  td.c-cen  { width: 78px;  min-width: 78px;  max-width: 78px; }
th.c-ser,  td.c-ser  { width: 50px;  min-width: 50px;  max-width: 50px; }
th.c-tot,  td.c-tot  { width: 42px;  min-width: 42px;  max-width: 42px; }
th.c-prom, td.c-prom { width: 42px;  min-width: 42px;  max-width: 42px; }
th.c-hora, td.c-hora { width: {{ $anchoHora }}px; min-width: {{ $anchoHora }}px; max-width: {{ $anchoHora }}px; }

thead tr.cols-row { background: {{ $colorPais['header'] }} !important; }
thead th {
    padding: 3px 1px;
    font-size: {{ $fsTh }};
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #fff !important;
    text-align: center;
    border: 1px solid {{ $colorPais['border'] }};
    white-space: nowrap;
    overflow: hidden;
}
thead th.left { text-align: left; }

tbody tr:nth-child(even) { background: #f8fafc !important; }
tbody tr:nth-child(odd)  { background: #ffffff !important; }
tbody td {
    padding: 2px 2px;
    font-size: {{ $fsTd }};
    color: #334155;
    border: 1px solid #e2e8f0;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
tbody td.left  { text-align: left; }
tbody td.right { text-align: right; font-variant-numeric: tabular-nums; }
tbody td.mono  { font-family: 'Courier New', monospace; font-size: calc({{ $fsTd }} - 0.5px); }
tbody td.wrap  { white-space: normal; word-break: break-word; line-height: 1.2; }

tbody td.total-green { background: #eff6ff !important; color: #1d4ed8 !important; font-weight: 700; }
tbody td.total-red   { background: #fff1f2 !important; color: #f43f5e !important; font-weight: 700; }

tbody td.h-max        { color: #22c55e !important; font-weight: 600; }
tbody td.h-high       { color: #16a34a !important; }
tbody td.h-mid        { color: #15803d !important; }
tbody td.h-low        { color: #64748b !important; }
tbody td.h-zero-trans { background: #eff6ff !important; color: #60a5fa !important; }
tbody td.h-zero-red   { background: #fff1f2 !important; color: #f87171 !important; }

tbody td.prom-verde    { background: rgba(134,239,122,0.5) !important; color: #1e293b !important; font-weight: 600; }
tbody td.prom-amarillo { background: rgba(252,211,6,0.45)  !important; color: #1e293b !important; font-weight: 600; }
tbody td.prom-rojo     { background: rgba(252,165,165,0.6) !important; color: #1e293b !important; font-weight: 600; }

tfoot tr { background: #1e293b !important; }
tfoot td {
    padding: 3px 2px;
    font-size: {{ $fsTh }};
    font-weight: 700;
    color: #fff !important;
    border: 1px solid #334155;
    text-align: center;
    font-variant-numeric: tabular-nums;
}
tfoot td.right     { text-align: right; }
tfoot td.label     { text-align: right; color: #94a3b8 !important; text-transform: uppercase; }
tfoot td.total-col { color: #6ee7b7 !important; text-align: right; }
tfoot td.prom-col  { color: #fcd34d !important; text-align: right; }

.footer {
    margin-top: 5px;
    display: flex;
    justify-content: space-between;
    font-size: {{ $fsLabel }};
    color: #94a3b8;
    border-top: 1px solid #e2e8f0;
    padding-top: 3px;
}
</style>
</head>
<body>

@php $offsetGlobal = 0; @endphp

@foreach($chunks as $pageIndex => $chunk)
@php
    $esPrimera    = $pageIndex === 0;
    $numeroPagina = $pageIndex + 1;
@endphp
<div class="page">

    @if($esPrimera)
    <div class="header">
        <div>
            <p class="label-pais">Reporte de Producción — {{ ucfirst($pais) }}</p>
            <h1>Producción <span>{{ $fecha }}</span></h1>
            <p class="sub">{{ $totalRegistros }} máquinas &middot; Horas {{ $horaInicio }}:00 – {{ $horaFin }}:00</p>
        </div>
        <p class="generated">Generado: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    @else
    <p class="page-label">Página {{ $numeroPagina }} de {{ $totalPaginas }} &middot; {{ ucfirst($pais) }} &middot; {{ $fecha }}</p>
    @endif

    <table>
        <thead>
            <tr class="cols-row">
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
            @foreach($chunk as $localIndex => $row)
                @php
                    $total       = (float)($row->total    ?? 0);
                    $promedio    = (float)($row->promedio  ?? 0);
                    $colorCentro = (int)($row->colorCentro ?? 0);
                    $clsTot      = $clsTotal($total, $promedioGeneral);
                    $clsProm     = $clsBgPromedio($colorCentro);
                    $numGlobal   = $offsetGlobal + $localIndex + 1;
                @endphp
                <tr>
                    <td class="c-num">{{ $numGlobal }}</td>
                    <td class="c-mod left wrap">{{ $row->modelo }}</td>
                    <td class="c-cen left wrap">{{ $row->centro }}</td>
                    <td class="c-ser mono wrap">{{ $row->serie }}</td>
                    @foreach($horasArr as $h)
                        @php
                            $val        = (float)($row->{"h{$h}"} ?? 0);
                            $transmitio = (int)($row->{"trans_h{$h}"} ?? 0);
                            $cls        = $clsIntensidad($val, $maxPorHora[$h] ?? 0, $transmitio);
                        @endphp
                        <td class="c-hora right {{ $cls }}">{{ $fmt($val) }}</td>
                    @endforeach
                    <td class="c-tot right {{ $clsTot }}">{{ $fmt($total) }}</td>
                    <td class="c-prom right {{ $clsProm }}">{{ $fmt($promedio) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="label">
                    @if($esPrimera && $totalPaginas === 1)
                        Total · {{ $totalRegistros }} registros
                    @elseif($esPrimera)
                        Continúa en página 2…
                    @elseif($pageIndex === $totalPaginas - 1)
                        Total general · {{ $totalRegistros }} registros
                    @else
                        Continúa en página {{ $numeroPagina + 1 }}…
                    @endif
                </td>
                @foreach($horasArr as $h)
                    <td class="c-hora right">{{ $fmtHora($totalesHora[$h]) }}</td>
                @endforeach
                <td class="c-tot total-col">{{ $fmt($totalGeneral) }}</td>
                <td class="c-prom prom-col">{{ $fmt($promedioGeneral) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <span>Planet Game</span>
        <span>{{ ucfirst($pais) }} &middot; {{ $fecha }} &middot; {{ $totalRegistros }} registros &middot; Pág. {{ $numeroPagina }}/{{ $totalPaginas }}</span>
    </div>

</div>
@php $offsetGlobal += count($chunk); @endphp
@endforeach

</body>
</html>