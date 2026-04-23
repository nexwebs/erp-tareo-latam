<?php

namespace App\Http\Controllers;

use App\Models\Centro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProduccionController extends Controller
{
    private const TABLA_MAP = [
        'chile' => 'produccionchile',
        'colombia' => 'produccioncolombia',
        'australia' => 'produccionaustralia',
        'peru' => 'produccionperu',
    ];

    private const HORA_INICIO = 8;

    private const HORA_FIN = 23;

    private const PAR_PAIS = [
        'chile' => 'CLP-PEN',
        'colombia' => 'COP-PEN',
        'australia' => 'AUD-PEN',
    ];

    private const TC_REFERENCIA = [
        'chile' => 0.0039,
        'colombia' => 0.0010,
        'australia' => 2.47,
    ];

    private function obtenerTipoCambio(string $pais): float
    {
        $par = self::PAR_PAIS[$pais] ?? null;
        if (! $par) {
            return self::TC_REFERENCIA[$pais] ?? 1;
        }

        try {
            $url = "https://dolar.pe/api/public/series?pairs={$par}";
            $response = file_get_contents($url);
            $data = json_decode($response, true);

            if (isset($data['series'][0]['data'])) {
                $items = $data['series'][0]['data'];
                if (! empty($items)) {
                    $ultimo = end($items);
                    $valor = (float) ($ultimo['value'] ?? 0);
                    if ($valor > 0) {
                        return $valor;
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::error("Error tipo cambio {$pais}: ".$e->getMessage());
        }

        return self::TC_REFERENCIA[$pais] ?? 1;
    }

    private function tablaProduccion(string $pais): string
    {
        return self::TABLA_MAP[$pais] ?? self::TABLA_MAP['peru'];
    }

    private function centrosQuery(string $pais)
    {
        return match ($pais) {
            'chile' => Centro::where('EsChile', 1)->where('EsActivo', 1),
            'colombia' => Centro::where('EsColombia', 1)->where('EsActivo', 1),
            'australia' => Centro::where('EsAustralia', 1)->where('EsActivo', 1),
            default => Centro::where('EsChile', 0)
                ->where('EsColombia', 0)
                ->where('EsAustralia', 0)
                ->where('EsActivo', 1)
                ->where('IdCentro', '!=', 1),
        };
    }

    private function buildHoraSelects(int $inicio = self::HORA_INICIO, int $fin = self::HORA_FIN): array
    {
        return array_map(
            fn ($h) => "SUM(CASE WHEN HOUR(p.FechaProduccion) = {$h} THEN p.ProduccionFinal ELSE 0 END) as h{$h}",
            range($inicio, $fin)
        );
    }

    /**
     * Se calcula en PHP después de obtener la fila.
     * Si trabajó solo 1 hora, dividir entre 8 para normalizar.
     */
    private function calcularPromedio(object $row, int $inicio, int $fin): float
    {
        $horasTrabajadas = 0;
        $total = 0;

        for ($h = $inicio; $h <= $fin; $h++) {
            $val = (float) ($row->{"h{$h}"} ?? 0);
            if ($val > 0) {
                $horasTrabajadas++;
                $total += $val;
            }
        }

        if ($horasTrabajadas === 0) {
            return 0;
        }

        // Si solo trabajó 1 hora, normalizar usando 8 horas
        if ($horasTrabajadas === 1) {
            return round($total / 8, 2);
        }

        return round($total / $horasTrabajadas, 2);
    }

    private function queryRangoBase(string $tabla, string $fechaInicio, string $fechaFin, int $horaInicio = self::HORA_INICIO, int $horaFin = self::HORA_FIN)
    {
        $query = DB::table("{$tabla} as p")
            ->join('centros as c', 'p.idCentro', '=', 'c.IdCentro')
            ->join('maquinas as m', 'p.idMaquina', '=', 'm.IdMaquina')
            ->whereBetween(DB::raw('DATE(p.FechaProduccion)'), [$fechaInicio, $fechaFin])
            ->whereRaw('HOUR(p.FechaProduccion) BETWEEN ? AND ?', [$horaInicio, $horaFin])
            ->where('m.EsVisible', 1)
            ->selectRaw('m.Modelo as modelo')
            ->selectRaw('c.NombreCentro as centro')
            ->selectRaw('m.CodigoMaquina as serie');

        foreach ($this->buildHoraSelects($horaInicio, $horaFin) as $select) {
            $query->selectRaw($select);
        }

        $query->selectRaw('SUM(p.ProduccionFinal) as total');

        return $query
            ->groupBy('m.Modelo', 'c.NombreCentro', 'm.CodigoMaquina')
            ->orderByDesc(DB::raw('SUM(p.ProduccionFinal)'));
    }

    private function queryHoraBase(string $tabla, string $fecha)
    {
        $query = DB::table("{$tabla} as p")
            ->join('centros as c', 'p.idCentro', '=', 'c.IdCentro')
            ->join('maquinas as m', 'p.idMaquina', '=', 'm.IdMaquina')
            ->whereDate('p.FechaProduccion', $fecha)
            ->whereRaw('HOUR(p.FechaProduccion) BETWEEN ? AND ?', [self::HORA_INICIO, self::HORA_FIN])
            ->where('m.EsVisible', 1)
            ->selectRaw('m.Modelo as modelo')
            ->selectRaw('c.NombreCentro as centro')
            ->selectRaw('m.CodigoMaquina as serie');

        foreach ($this->buildHoraSelects() as $select) {
            $query->selectRaw($select);
        }

        $query->selectRaw('SUM(p.ProduccionFinal) as total');

        return $query
            ->groupBy('m.Modelo', 'c.NombreCentro', 'm.CodigoMaquina')
            ->orderBy('c.NombreCentro')
            ->orderBy('m.CodigoMaquina');
    }

    private function enrichWithIndex(iterable $rows, int $horaInicio = self::HORA_INICIO, int $horaFin = self::HORA_FIN): array
    {
        $result = [];
        foreach ($rows as $i => $row) {
            $arr = (array) $row;
            $arr['item'] = $i + 1;
            $arr['promedio'] = $this->calcularPromedio((object) $arr, $horaInicio, $horaFin);
            $result[] = (object) $arr;
        }

        return $result;
    }

    public function peru(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(14)->toDateString());
        $fechaFin = $request->get('fecha_fin', now()->toDateString());
        $tipoCambio = $request->get('tipo_cambio');

        $tipoCambioReal = $tipoCambio
            ? (float) str_replace(',', '.', $tipoCambio)
            : $this->obtenerTipoCambio('peru');

        $rawRows = $this->queryRangoBase('produccionperu', $fechaInicio, $fechaFin)->get();
        $datos = $this->enrichWithIndex($rawRows);

        $centros = $this->centrosQuery('peru')->orderBy('NombreCentro')->get();

        return inertia('ProduccionPeru', [
            'datos' => $datos,
            'centros' => $centros,
            'filtros' => ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin],
            'horaInicio' => self::HORA_INICIO,
            'horaFin' => self::HORA_FIN,
            'tipoCambio' => $tipoCambioReal,
        ]);
    }

    public function chile(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(14)->toDateString());
        $fechaFin = $request->get('fecha_fin', now()->toDateString());
        $tipoCambio = $request->get('tipo_cambio');

        $tipoCambioReal = $tipoCambio
            ? (float) str_replace(',', '.', $tipoCambio)
            : $this->obtenerTipoCambio('chile');

        $rawRows = $this->queryRangoBase('produccionchile', $fechaInicio, $fechaFin)->get();
        $datos = $this->enrichWithIndex($rawRows);

        $centros = $this->centrosQuery('chile')
            ->where('IdCentro', '!=', 46)
            ->orderBy('NombreCentro')
            ->get();

        return inertia('ProduccionChile', [
            'datos' => $datos,
            'centros' => $centros,
            'filtros' => ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin],
            'horaInicio' => self::HORA_INICIO,
            'horaFin' => self::HORA_FIN,
            'tipoCambio' => $tipoCambioReal,
        ]);
    }

    public function colombia(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(14)->toDateString());
        $fechaFin = $request->get('fecha_fin', now()->toDateString());
        $tipoCambio = $request->get('tipo_cambio');

        $tipoCambioReal = $tipoCambio
            ? (float) str_replace(',', '.', $tipoCambio)
            : $this->obtenerTipoCambio('colombia');

        $rawRows = $this->queryRangoBase('produccioncolombia', $fechaInicio, $fechaFin)->get();
        $datos = $this->enrichWithIndex($rawRows);

        $centros = $this->centrosQuery('colombia')->orderBy('NombreCentro')->get();

        return inertia('ProduccionColombia', [
            'datos' => $datos,
            'centros' => $centros,
            'filtros' => ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin],
            'horaInicio' => self::HORA_INICIO,
            'horaFin' => self::HORA_FIN,
            'tipoCambio' => $tipoCambioReal,
        ]);
    }

    public function australia(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(14)->toDateString());
        $fechaFin = $request->get('fecha_fin', now()->toDateString());
        $tipoCambio = $request->get('tipo_cambio');

        $tipoCambioReal = $tipoCambio
            ? (float) str_replace(',', '.', $tipoCambio)
            : $this->obtenerTipoCambio('australia');

        $rawRows = $this->queryRangoBase('produccionaustralia', $fechaInicio, $fechaFin)->get();
        $datos = $this->enrichWithIndex($rawRows);

        $centros = $this->centrosQuery('australia')->orderBy('NombreCentro')->get();

        return inertia('ProduccionAustralia', [
            'datos' => $datos,
            'centros' => $centros,
            'filtros' => ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin],
            'horaInicio' => self::HORA_INICIO,
            'horaFin' => self::HORA_FIN,
            'tipoCambio' => $tipoCambioReal,
        ]);
    }

    public function hora(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());
        $pais = $request->get('pais', 'peru');
        $tabla = $this->tablaProduccion($pais);

        $rawRows = $this->queryHoraBase($tabla, $fecha)->get();
        $datos = $this->enrichWithIndex($rawRows);

        return inertia('ProduccionHora', [
            'datos' => $datos,
            'filtros' => ['fecha' => $fecha, 'pais' => $pais],
            'horaInicio' => self::HORA_INICIO,
            'horaFin' => self::HORA_FIN,
        ]);
    }

    public function controlar(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());
        $pais = $request->get('pais', 'peru');
        $tabla = $this->tablaProduccion($pais);

        $baseQuery = DB::table("{$tabla} as p")
            ->join('centros as c', 'p.idCentro', '=', 'c.IdCentro')
            ->join('maquinas as m', 'p.idMaquina', '=', 'm.IdMaquina')
            ->whereDate('p.FechaProduccion', $fecha)
            ->whereRaw('HOUR(p.FechaProduccion) BETWEEN ? AND ?', [self::HORA_INICIO, self::HORA_FIN])
            ->where('m.EsVisible', 1);

        $rawRows = (clone $baseQuery)
            ->selectRaw('c.NombreCentro')
            ->selectRaw('m.Modelo')
            ->selectRaw('m.CodigoMaquina')
            ->selectRaw('SUM(p.ProduccionFinal) as total')
            ->selectRaw('COUNT(p.idProduccion) as conteo')
            ->selectRaw('MIN(HOUR(p.FechaProduccion)) as hora_inicio_real')
            ->selectRaw('MAX(HOUR(p.FechaProduccion)) as hora_fin_real')
            ->groupBy('c.NombreCentro', 'm.Modelo', 'm.CodigoMaquina')
            ->orderByDesc('total')
            ->get();

        $datos = $rawRows->map(function ($row, $i) {
            $arr = (array) $row;
            $arr['item'] = $i + 1;
            $horasTrabajadas = ($row->hora_fin_real - $row->hora_inicio_real) + 1;
            $divisor = $horasTrabajadas === 1 ? 8 : $horasTrabajadas;
            $arr['promedio'] = $horasTrabajadas > 0
                ? round($row->total / $divisor, 2)
                : 0;

            return (object) $arr;
        });

        $totales = (clone $baseQuery)
            ->selectRaw('SUM(p.ProduccionFinal) as total_general')
            ->selectRaw('COUNT(p.idProduccion) as conteo_general')
            ->first();

        $centros = $this->centrosQuery($pais)->orderBy('NombreCentro')->get();

        return inertia('ProduccionControlar', [
            'datos' => $datos,
            'centros' => $centros,
            'totales' => $totales,
            'filtros' => ['fecha' => $fecha, 'pais' => $pais],
            'horaInicio' => self::HORA_INICIO,
            'horaFin' => self::HORA_FIN,
        ]);
    }

    public function exportarCuadre(Request $request): StreamedResponse
    {
        $fecha = $request->get('fecha', now()->toDateString());
        $pais = $request->get('pais', 'todos');
        $paises = $pais === 'todos' ? array_keys(self::TABLA_MAP) : [$pais];

        $unionQuery = null;

        foreach ($paises as $p) {
            $sub = $this->queryHoraBase($this->tablaProduccion($p), $fecha)
                ->addSelect(DB::raw("'{$p}' as pais"));

            $unionQuery = $unionQuery ? $unionQuery->unionAll($sub) : $sub;
        }

        $rows = DB::query()->fromSub($unionQuery, 'u')->get()->values();

        $horaInicio = self::HORA_INICIO;
        $horaFin = self::HORA_FIN;

        return response()->streamDownload(function () use ($rows, $horaInicio, $horaFin) {
            $handle = fopen('php://output', 'w');

            $horasHeader = array_map(fn ($h) => "{$h}:00", range($horaInicio, $horaFin));

            fputcsv($handle, array_merge(
                ['ITEM', 'PAIS', 'MODELO', 'CENTRO COMERCIAL', 'SERIE'],
                $horasHeader,
                ['TOTAL', 'PROMEDIO']
            ));

            foreach ($rows as $i => $row) {
                $line = [$i + 1, strtoupper($row->pais ?? ''), $row->modelo, $row->centro, $row->serie];

                $horasTrabajadas = 0;
                $totalFila = 0;

                for ($h = $horaInicio; $h <= $horaFin; $h++) {
                    $val = (float) ($row->{"h{$h}"} ?? 0);
                    $line[] = $val;
                    if ($val > 0) {
                        $horasTrabajadas++;
                        $totalFila += $val;
                    }
                }

                $promedio = $horasTrabajadas > 0 ? round($totalFila / $horasTrabajadas, 2) : 0;
                $line[] = $row->total;
                $line[] = $promedio;

                fputcsv($handle, $line);
            }

            fclose($handle);
        }, "cuadre_{$fecha}_{$pais}.csv", ['Content-Type' => 'text/csv']);
    }

    public function exportarExcel(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());
        $pais = $request->get('pais', 'todos');
        $paises = $pais === 'todos' ? array_keys(self::TABLA_MAP) : [$pais];

        $unionQuery = null;

        foreach ($paises as $p) {
            $sub = $this->queryHoraBase($this->tablaProduccion($p), $fecha)
                ->addSelect(DB::raw("'{$p}' as pais"));

            $unionQuery = $unionQuery ? $unionQuery->unionAll($sub) : $sub;
        }

        $rows = DB::query()->fromSub($unionQuery, 'u')->get()->values();

        $horaInicio = self::HORA_INICIO;
        $horaFin = self::HORA_FIN;

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Producción');

        $horasHeader = array_map(fn ($h) => "{$h}:00", range($horaInicio, $horaFin));
        $headers = ['#', 'PAÍS', 'MODELO', 'CENTRO COMERCIAL', 'SERIE', ...$horasHeader, 'TOTAL', 'PROM/H'];
        $col = 'A';

        foreach ($headers as $h) {
            $sheet->setCellValue("{$col}1", strtoupper($h));
            $col++;
        }

        $lastCol = chr(64 + count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        foreach (range('A', $lastCol) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $sheet->getRowDimension(1)->setRowHeight(25);

        foreach ($rows as $i => $row) {
            $numRow = $i + 2;
            $sheet->setCellValue("A{$numRow}", $i + 1);
            $sheet->setCellValue("B{$numRow}", strtoupper($row->pais ?? ''));
            $sheet->setCellValue("C{$numRow}", $row->modelo);
            $sheet->setCellValue("D{$numRow}", $row->centro);
            $sheet->setCellValue("E{$numRow}", $row->serie);

            $trabajadas = 0;
            $totalF = 0;
            $cIdx = 5;

            for ($h = $horaInicio; $h <= $horaFin; $h++) {
                $cIdx++;
                $val = (float) ($row->{"h{$h}"} ?? 0);
                $cell = chr(64 + $cIdx).$numRow;
                $sheet->setCellValue($cell, $val);
                $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00');

                if ($val > 0) {
                    $trabajadas++;
                    $totalF += $val;
                }
            }

            $cIdx++;
            $promedio = $trabajadas > 0 ? round($totalF / $trabajadas, 2) : 0;
            $sheet->setCellValue(chr(64 + $cIdx).$numRow, $row->total);
            $sheet->getStyle(chr(64 + $cIdx).$numRow)->getNumberFormat()->setFormatCode('#,##0.00');

            $cIdx++;
            $sheet->setCellValue(chr(64 + $cIdx).$numRow, $promedio);
            $sheet->getStyle(chr(64 + $cIdx).$numRow)->getNumberFormat()->setFormatCode('#,##0.00');

            $sheet->getStyle("A{$numRow}:{$lastCol}{$numRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
            ]);
        }

        $totalRow = count($rows) + 2;
        $sheet->setCellValue("A{$totalRow}", 'TOTAL');
        $sheet->mergeCells("A{$totalRow}:E{$totalRow}");
        $sheet->getStyle("A{$totalRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$totalRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E7EB']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '9CA3AF']]],
        ]);

        for ($h = $horaInicio; $h <= $horaFin; $h++) {
            $cIdx = 5 + ($h - $horaInicio + 1);
            $cell = chr(64 + $cIdx).$totalRow;
            $sheet->setCellValue($cell, "=SUM({$cell}2:{$cell}".($totalRow - 1).')');
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '9CA3AF']]],
            ]);
        }

        $cIdx = 5 + ($horaFin - $horaInicio + 1);
        $sheet->setCellValue(chr(64 + $cIdx).$totalRow, "=SUM(F{$totalRow}:Z{$totalRow})");
        $sheet->getStyle(chr(64 + $cIdx).$totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle(chr(64 + $cIdx).$totalRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '9CA3AF']]],
        ]);

        $cIdx++;
        $sheet->setCellValue(chr(64 + $cIdx).$totalRow, '');
        $sheet->getStyle(chr(64 + $cIdx).$totalRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '9CA3AF']]],
        ]);

        $sheet->setShowGridlines(false);

        $paisLabel = $pais === 'todos' ? 'todos' : $pais;
        $filename = "reporte_produccion_{$fecha}_{$paisLabel}.xlsx";

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
