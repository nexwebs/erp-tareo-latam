<?php

namespace App\Http\Controllers;

use App\Models\Centro;
use Illuminate\Database\Query\Builder;
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

    private const PROMED_CAMPO = [
        'peru' => 'promV',
        'chile' => 'promT',
        'colombia' => 'promT',
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
                $ultimo = end($items);
                $valor = (float) ($ultimo['value'] ?? 0);
                if ($valor > 0) {
                    return $valor;
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
        $selects = [];
        foreach (range($inicio, $fin) as $h) {
            $selects[] = $h === 8
                ? "SUM(CASE WHEN HOUR(p.FechaProduccion) <= {$h} THEN p.ProduccionFinal ELSE 0 END) as h{$h}"
                : "SUM(CASE WHEN HOUR(p.FechaProduccion) = {$h} THEN p.ProduccionFinal ELSE 0 END) as h{$h}";
        }

        return $selects;
    }

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
        if ($horasTrabajadas === 1) {
            return round($total / 8, 2);
        }

        return round($total / $horasTrabajadas, 2);
    }

    /**
     * Subquery que devuelve el último promedioCentro por idCentro
     * desde la tabla promedios (Perú, Chile, Colombia).
     */
    private function subqueryPromediosCentro(string $campoProm): Builder
    {
        return DB::table('promedios as pr')
            ->join(
                DB::raw('(SELECT idCentro, MAX(timestamp) AS ts FROM promedios GROUP BY idCentro) latest'),
                function ($join) {
                    $join->on('pr.idCentro', '=', 'latest.idCentro')
                        ->on('pr.timestamp', '=', 'latest.ts');
                }
            )
            ->select('pr.idCentro', "pr.{$campoProm} as promedioCentro");
    }

    /**
     * CTE equivalente para Australia: promedio histórico de las últimas 8 semanas
     * del mismo día de semana que fechaRef, agrupado por idMaquina.
     * Se construye como subquery raw porque MySQL no soporta CTEs en el query builder
     * de forma nativa fluida con joins posteriores.
     */
    private function subqueryPromedioAustralia(string $fechaRef): string
    {
        return "
            SELECT
                p.idMaquina,
                SUM(p.ProduccionFinal) / COUNT(DISTINCT DATE(p.FechaProduccion)) AS promedioCentro
            FROM produccionaustralia p
            INNER JOIN maquinas m ON m.IdMaquina = p.idMaquina
            WHERE DATE(p.FechaProduccion) >= DATE_SUB('{$fechaRef}', INTERVAL 8 WEEK)
              AND DATE(p.FechaProduccion) <  '{$fechaRef}'
              AND DAYOFWEEK(p.FechaProduccion) = DAYOFWEEK('{$fechaRef}')
              AND p.ProduccionFinal > 0
              AND m.EsVisible = 1
            GROUP BY p.idMaquina
        ";
    }

    /**
     * Query principal para rango de fechas con PromedioCentro.
     *
     * Para Perú/Chile/Colombia: join con tabla promedios (último timestamp).
     * Para Australia: join con subquery histórico 8 semanas por idMaquina.
     *
     * El promedioCentro es un valor de referencia fijo del centro/máquina,
     * no cambia al agregar filas: se toma con MAX(COALESCE(..., 0)).
     */
    private function queryRangoBase(
        string $tabla,
        string $pais,
        string $fechaInicio,
        string $fechaFin,
        int $horaInicio = self::HORA_INICIO,
        int $horaFin = self::HORA_FIN
    ) {
        $query = DB::table("{$tabla} as p")
            ->join('centros as c', 'p.idCentro', '=', 'c.IdCentro')
            ->join('maquinas as m', 'p.idMaquina', '=', 'm.IdMaquina')
            ->whereBetween(DB::raw('DATE(p.FechaProduccion)'), [$fechaInicio, $fechaFin])
            ->whereRaw('HOUR(p.FechaProduccion) BETWEEN 0 AND ?', [$horaFin])
            ->where('m.EsVisible', 1)
            ->selectRaw('p.idMaquina')
            ->selectRaw('m.Modelo as modelo')
            ->selectRaw('c.NombreCentro as centro')
            ->selectRaw('m.CodigoMaquina as serie');

        foreach ($this->buildHoraSelects($horaInicio, $horaFin) as $select) {
            $query->selectRaw($select);
        }

        $query->selectRaw('SUM(p.ProduccionFinal) as total');

        if ($pais === 'australia') {
            $fechaRef = $fechaFin;
            $subSql = $this->subqueryPromedioAustralia($fechaRef);

            $query->leftJoin(
                DB::raw("({$subSql}) as hist"),
                'hist.idMaquina',
                '=',
                'p.idMaquina'
            )->selectRaw('ROUND(MAX(COALESCE(hist.promedioCentro, 0)), 2) as promedioCentro');

            $query->groupBy('p.idMaquina', 'm.Modelo', 'c.NombreCentro', 'm.CodigoMaquina');
        } else {
            $campoProm = self::PROMED_CAMPO[$pais] ?? 'promV';
            $subProm = $this->subqueryPromediosCentro($campoProm);

            $query->leftJoinSub($subProm, 'prom', 'prom.idCentro', '=', 'p.idCentro')
                ->selectRaw('ROUND(MAX(COALESCE(prom.promedioCentro, 0)), 2) as promedioCentro');

            $query->groupBy('p.idMaquina', 'm.Modelo', 'c.NombreCentro', 'm.CodigoMaquina');
        }

        return $query->orderByDesc(DB::raw('SUM(p.ProduccionFinal)'));
    }

    private function queryHoraBase(string $tabla, string $fecha)
    {
        $query = DB::table("{$tabla} as p")
            ->join('centros as c', 'p.idCentro', '=', 'c.IdCentro')
            ->join('maquinas as m', 'p.idMaquina', '=', 'm.IdMaquina')
            ->whereDate('p.FechaProduccion', $fecha)
            ->whereRaw('HOUR(p.FechaProduccion) BETWEEN 0 AND ?', [self::HORA_FIN])
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
        $fecha = $request->get('fecha', now()->toDateString());

        $rawRows = DB::select('CALL rpt_centro_produccion_peru(?)', [$fecha]);

        $datos = array_map(function ($row, $i) {
            $row = (array) $row;
            $row['item'] = $i + 1;
            $row['promedio'] = $row['promedioCentro'] ?? 0;

            return (object) $row;
        }, $rawRows, array_keys($rawRows));

        $centros = $this->centrosQuery('peru')->orderBy('NombreCentro')->get();

        return inertia('ProduccionPeru', [
            'datos' => $datos,
            'centros' => $centros,
            'filtros' => ['fecha' => $fecha],
            'horaInicio' => self::HORA_INICIO,
            'horaFin' => self::HORA_FIN,
            'tipoCambio' => 1,
        ]);
    }

    public function chile(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());

        $rawRows = DB::select('CALL rpt_centro_produccion_chile(?)', [$fecha]);

        $datos = array_map(function ($row, $i) {
            $row = (array) $row;
            $row['item'] = $i + 1;
            $row['promedio'] = $row['promedioCentro'] ?? 0;

            return (object) $row;
        }, $rawRows, array_keys($rawRows));

        $centros = $this->centrosQuery('chile')
            ->where('IdCentro', '!=', 46)
            ->orderBy('NombreCentro')
            ->get();

        return inertia('ProduccionChile', [
            'datos' => $datos,
            'centros' => $centros,
            'filtros' => ['fecha' => $fecha],
            'horaInicio' => self::HORA_INICIO,
            'horaFin' => self::HORA_FIN,
            'tipoCambio' => $this->obtenerTipoCambio('chile'),
        ]);
    }

    public function colombia(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());

        $rawRows = DB::select('CALL rpt_centro_produccion_colombia(?)', [$fecha]);

        $datos = array_map(function ($row, $i) {
            $row = (array) $row;
            $row['item'] = $i + 1;
            $row['promedio'] = $row['promedioCentro'] ?? 0;

            return (object) $row;
        }, $rawRows, array_keys($rawRows));

        $centros = $this->centrosQuery('colombia')->orderBy('NombreCentro')->get();

        return inertia('ProduccionColombia', [
            'datos' => $datos,
            'centros' => $centros,
            'filtros' => ['fecha' => $fecha],
            'horaInicio' => self::HORA_INICIO,
            'horaFin' => self::HORA_FIN,
            'tipoCambio' => $this->obtenerTipoCambio('colombia'),
        ]);
    }

    public function australia(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());

        $rawRows = DB::select('CALL rpt_centro_produccion_australia(?)', [$fecha]);

        $datos = array_map(function ($row, $i) {
            $row = (array) $row;
            $row['item'] = $i + 1;
            $row['promedio'] = $row['promedioCentro'] ?? 0;

            return (object) $row;
        }, $rawRows, array_keys($rawRows));

        $centros = $this->centrosQuery('australia')->orderBy('NombreCentro')->get();

        return inertia('ProduccionAustralia', [
            'datos' => $datos,
            'centros' => $centros,
            'filtros' => ['fecha' => $fecha],
            'horaInicio' => self::HORA_INICIO,
            'horaFin' => self::HORA_FIN,
            'tipoCambio' => $this->obtenerTipoCambio('australia'),
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

    // ProduccionController.php — método controlar

    public function controlar(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());
        $fechaFin = $request->get('fecha_fin', $fecha);
        $pais = $request->get('pais', 'peru');

        $paisesValidos = array_keys(self::TABLA_MAP);
        if (! in_array($pais, $paisesValidos)) {
            $pais = 'peru';
        }

        $rawRows = DB::select('CALL rpt_tareo_centros_latam(?, ?, ?)', [$fecha, $fechaFin, $pais]);

        $datos = array_map(function ($row, $i) {
            $arr = (array) $row;

            $total = (float) ($arr['total'] ?? 0);
            $diasConDatos = (int) ($arr['dias_con_datos'] ?? 1);
            $promDiario = (float) ($arr['total_promedio_diario'] ?? 0);
            $promHistorico = (float) ($arr['promedioCentro'] ?? 0);
            $horasActivas = (int) ($arr['horas_con_produccion'] ?? 0);
            $horasCero = (int) ($arr['horas_con_produccion_cero'] ?? 0);
            $horasMuertas = (int) ($arr['horas_sin_transmitir'] ?? 0);

            $horasTurno = $horasActivas + $horasCero + $horasMuertas;
            $eficiencia = $horasTurno > 0
                ? round(($horasActivas / $horasTurno) * 100, 1)
                : 0;
            $eficienciaCero = $horasTurno > 0
                ? round((($horasActivas + $horasCero) / $horasTurno) * 100, 1)
                : 0;

            $vsHistorico = $promHistorico > 0
                ? round((($promDiario - $promHistorico) / $promHistorico) * 100, 1)
                : null;

            $disponibilidad = round((($horasActivas + $horasCero) / 16) * 100, 1);

            $rendimiento = $promHistorico > 0
                ? round(min($promDiario / $promHistorico, 1.0) * 100, 1)
                : ($promDiario > 0 ? 100.0 : 0.0);

            $eficienciaReal = round(($disponibilidad / 100) * ($rendimiento / 100) * 100, 1);

            $vsHistorico = $promHistorico > 0
                ? round((($promDiario - $promHistorico) / $promHistorico) * 100, 1)
                : null;

            return (object) [
                'item'                      => $i + 1,
                'IdMaquina'                 => $arr['IdMaquina'] ?? null,
                'Modelo'                    => $arr['Modelo'] ?? '',
                'NombreCentro'              => $arr['NombreCentro'] ?? '',
                'Serie'                     => $arr['Serie'] ?? '',
                'total'                     => $total,
                'dias_con_datos'            => $diasConDatos,
                'total_promedio_diario'     => $promDiario,
                'promedioCentro'            => $promHistorico,
                'horas_con_produccion'      => $horasActivas,
                'horas_con_produccion_cero' => $horasCero,
                'horas_sin_transmitir'      => $horasMuertas,
                'disponibilidad'            => $disponibilidad,
                'rendimiento'               => $rendimiento,
                'eficiencia_real'           => $eficienciaReal,
                'vs_historico'              => $vsHistorico,
            ];
        }, $rawRows, array_keys($rawRows));

        $totalGeneral = array_sum(array_column(array_map(fn ($r) => (array) $r, $datos), 'total'));
        $horasActivasTotal = array_sum(array_column(array_map(fn ($r) => (array) $r, $datos), 'horas_con_produccion'));
        $horasCeroTotal = array_sum(array_column(array_map(fn ($r) => (array) $r, $datos), 'horas_con_produccion_cero'));
        $horasMuertasTotal = array_sum(array_column(array_map(fn ($r) => (array) $r, $datos), 'horas_sin_transmitir'));
        $horasTurnoTotal = $horasActivasTotal + $horasCeroTotal + $horasMuertasTotal;


        $totales = [
            'total_general'         => $totalGeneral,
            'conteo_general'        => count($datos),
            'horas_activas_total'   => $horasActivasTotal,
            'horas_cero_total'      => $horasCeroTotal,
            'horas_muertas_total'   => $horasMuertasTotal,
            'disponibilidad_global' => count($datos) > 0
                ? round((($horasActivasTotal + $horasCeroTotal) / (count($datos) * 16)) * 100, 1)
                : 0,
        ];

        $centros = $this->centrosQuery($pais)->orderBy('NombreCentro')->get();

        return inertia('ProduccionControlar', [
            'datos' => $datos,
            'centros' => $centros,
            'totales' => $totales,
            'filtros' => ['fecha' => $fecha, 'fecha_fin' => $fechaFin, 'pais' => $pais],
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

    public function exportarPdf(string $pais, Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());

        $metodo = match ($pais) {
            'chile' => 'chile',
            'colombia' => 'colombia',
            'australia' => 'australia',
            default => 'peru',
        };

        $rawRows = DB::select("CALL rpt_centro_produccion_{$metodo}(?)", [$fecha]);

        $datos = array_map(function ($row, $i) {
            $row = (array) $row;
            $row['item'] = $i + 1;
            $row['promedio'] = $row['promedioCentro'] ?? 0;

            return (object) $row;
        }, $rawRows, array_keys($rawRows));

        $tipoCambio = in_array($pais, ['chile', 'colombia', 'australia'])
            ? $this->obtenerTipoCambio($pais)
            : 1;

        return view('pdf.produccion_pais', [
            'pais' => $pais,
            'fecha' => $fecha,
            'datos' => $datos,
            'horaInicio' => self::HORA_INICIO,
            'horaFin' => self::HORA_FIN,
            'tipoCambio' => $tipoCambio,
        ]);
    }

    public function controlarPdf(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());
        $pais = $request->get('pais', 'peru');

        $rawRows = DB::select('CALL rpt_tareo_centros_latam(?, ?, ?)', [$fecha, $fecha, $pais]);

        $datos = array_map(function ($row, $i) {
            $arr = (array) $row;
            $arr['item'] = $i + 1;
            $arr['NombreCentro'] = $arr['NombreCentro'] ?? $arr['centro'] ?? '';
            $arr['Serie'] = $arr['CodigoMaquina'] ?? '';
            $arr['total'] = $arr['total'] ?? 0;
            $arr['total_promedio_diario'] = $arr['total_promedio_diario'] ?? $arr['dias_con_datos'] ?? 0;
            $arr['horas_con_produccion'] = $arr['horas_con_produccion'] ?? 0;
            $arr['horas_con_produccion_cero'] = $arr['horas_con_produccion_cero'] ?? 0;
            $arr['horas_sin_transmitir'] = $arr['horas_sin_transmitir'] ?? 0;
            $totalH = $arr['horas_con_produccion'] + $arr['horas_con_produccion_cero'] + $arr['horas_sin_transmitir'];
            $arr['eficiencia'] = $totalH > 0 ? round(($arr['horas_con_produccion'] / $totalH) * 100, 1) : 0;
            $arr['eficiencia_cero'] = $totalH > 0 ? round(($arr['horas_con_produccion'] + $arr['horas_con_produccion_cero']) / $totalH * 100, 1) : 0;

            return (object) $arr;
        }, $rawRows, array_keys($rawRows));

        return view('pdf.controlar_pais', [
            'pais' => $pais,
            'fecha' => $fecha,
            'datos' => $datos,
        ]);
    }
}
