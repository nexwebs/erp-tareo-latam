<?php

namespace App\Http\Controllers;

use App\Models\Centro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProduccionController extends Controller
{
    private const TABLA_MAP = [
        'chile'     => 'produccionchile',
        'colombia'  => 'produccioncolombia',
        'australia' => 'produccionaustralia',
        'peru'      => 'produccionperu',
    ];

    private const HORA_INICIO = 8;
    private const HORA_FIN    = 15;

    private function tablaProduccion(string $pais): string
    {
        return self::TABLA_MAP[$pais] ?? self::TABLA_MAP['peru'];
    }

    private function centrosQuery(string $pais)
    {
        return match ($pais) {
            'chile'     => Centro::where('EsChile', 1)->where('EsActivo', 1),
            'colombia'  => Centro::where('EsColombia', 1)->where('EsActivo', 1),
            'australia' => Centro::where('EsAustralia', 1)->where('EsActivo', 1),
            default     => Centro::where('EsChile', 0)
                                 ->where('EsColombia', 0)
                                 ->where('EsAustralia', 0)
                                 ->where('EsActivo', 1)
                                 ->where('IdCentro', '!=', 1),
        };
    }

    private function buildHoraSelects(int $inicio = self::HORA_INICIO, int $fin = self::HORA_FIN): array
    {
        return array_map(
            fn($h) => "SUM(CASE WHEN HOUR(p.FechaProduccion) = {$h} THEN p.ProduccionFinal ELSE 0 END) as h{$h}",
            range($inicio, $fin)
        );
    }

    /**
     * Promedio real: total / cantidad de horas distintas con produccion > 0.
     * Se calcula en PHP despues de obtener la fila para poder usar el rango real trabajado.
     */
    private function calcularPromedio(object $row, int $inicio, int $fin): float
    {
        $horasTrabajadas = 0;
        $total           = 0;

        for ($h = $inicio; $h <= $fin; $h++) {
            $val = (float) ($row->{"h{$h}"} ?? 0);
            if ($val > 0) {
                $horasTrabajadas++;
                $total += $val;
            }
        }

        return $horasTrabajadas > 0 ? round($total / $horasTrabajadas, 2) : 0;
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
            $arr             = (array) $row;
            $arr['item']     = $i + 1;
            $arr['promedio'] = $this->calcularPromedio((object) $arr, $horaInicio, $horaFin);
            $result[]        = (object) $arr;
        }
        return $result;
    }

    public function peru(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(14)->toDateString());
        $fechaFin    = $request->get('fecha_fin', now()->toDateString());

        $rawRows = $this->queryRangoBase('produccionperu', $fechaInicio, $fechaFin)->get();
        $datos   = $this->enrichWithIndex($rawRows);

        $centros = $this->centrosQuery('peru')->orderBy('NombreCentro')->get();

        return inertia('ProduccionPeru', [
            'datos'       => $datos,
            'centros'     => $centros,
            'filtros'     => ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin],
            'horaInicio'  => self::HORA_INICIO,
            'horaFin'     => self::HORA_FIN,
        ]);
    }

    public function chile(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(14)->toDateString());
        $fechaFin    = $request->get('fecha_fin', now()->toDateString());

        $rawRows = $this->queryRangoBase('produccionchile', $fechaInicio, $fechaFin)->get();
        $datos   = $this->enrichWithIndex($rawRows);

        $centros = $this->centrosQuery('chile')
            ->where('IdCentro', '!=', 46)
            ->orderBy('NombreCentro')
            ->get();

        return inertia('ProduccionChile', [
            'datos'      => $datos,
            'centros'    => $centros,
            'filtros'    => ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin],
            'horaInicio' => self::HORA_INICIO,
            'horaFin'    => self::HORA_FIN,
        ]);
    }

    public function colombia(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(14)->toDateString());
        $fechaFin    = $request->get('fecha_fin', now()->toDateString());

        $rawRows = $this->queryRangoBase('produccioncolombia', $fechaInicio, $fechaFin)->get();
        $datos   = $this->enrichWithIndex($rawRows);

        $centros = $this->centrosQuery('colombia')->orderBy('NombreCentro')->get();

        return inertia('ProduccionColombia', [
            'datos'      => $datos,
            'centros'    => $centros,
            'filtros'    => ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin],
            'horaInicio' => self::HORA_INICIO,
            'horaFin'    => self::HORA_FIN,
        ]);
    }

    public function australia(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(14)->toDateString());
        $fechaFin    = $request->get('fecha_fin', now()->toDateString());

        $rawRows = $this->queryRangoBase('produccionaustralia', $fechaInicio, $fechaFin)->get();
        $datos   = $this->enrichWithIndex($rawRows);

        $centros = $this->centrosQuery('australia')->orderBy('NombreCentro')->get();

        return inertia('ProduccionAustralia', [
            'datos'      => $datos,
            'centros'    => $centros,
            'filtros'    => ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin],
            'horaInicio' => self::HORA_INICIO,
            'horaFin'    => self::HORA_FIN,
        ]);
    }

    public function hora(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());
        $pais  = $request->get('pais', 'peru');
        $tabla = $this->tablaProduccion($pais);

        $rawRows = $this->queryHoraBase($tabla, $fecha)->get();
        $datos   = $this->enrichWithIndex($rawRows);

        return inertia('ProduccionHora', [
            'datos'      => $datos,
            'filtros'    => ['fecha' => $fecha, 'pais' => $pais],
            'horaInicio' => self::HORA_INICIO,
            'horaFin'    => self::HORA_FIN,
        ]);
    }

    public function controlar(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());
        $pais  = $request->get('pais', 'peru');
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
            $arr             = (array) $row;
            $arr['item']     = $i + 1;
            $horasTrabajadas = ($row->hora_fin_real - $row->hora_inicio_real) + 1;
            $arr['promedio'] = $horasTrabajadas > 0
                ? round($row->total / $horasTrabajadas, 2)
                : 0;
            return (object) $arr;
        });

        $totales = (clone $baseQuery)
            ->selectRaw('SUM(p.ProduccionFinal) as total_general')
            ->selectRaw('COUNT(p.idProduccion) as conteo_general')
            ->first();

        $centros = $this->centrosQuery($pais)->orderBy('NombreCentro')->get();

        return inertia('ProduccionControlar', [
            'datos'      => $datos,
            'centros'    => $centros,
            'totales'    => $totales,
            'filtros'    => ['fecha' => $fecha, 'pais' => $pais],
            'horaInicio' => self::HORA_INICIO,
            'horaFin'    => self::HORA_FIN,
        ]);
    }

    public function exportarCuadre(Request $request): StreamedResponse
    {
        $fecha  = $request->get('fecha', now()->toDateString());
        $pais   = $request->get('pais', 'todos');
        $paises = $pais === 'todos' ? array_keys(self::TABLA_MAP) : [$pais];

        $unionQuery = null;

        foreach ($paises as $p) {
            $sub = $this->queryHoraBase($this->tablaProduccion($p), $fecha)
                        ->addSelect(DB::raw("'{$p}' as pais"));

            $unionQuery = $unionQuery ? $unionQuery->unionAll($sub) : $sub;
        }

        $rows = DB::query()->fromSub($unionQuery, 'u')->get()->values();

        $horaInicio = self::HORA_INICIO;
        $horaFin    = self::HORA_FIN;

        return response()->streamDownload(function () use ($rows, $horaInicio, $horaFin) {
            $handle = fopen('php://output', 'w');

            $horasHeader = array_map(fn($h) => "{$h}:00", range($horaInicio, $horaFin));

            fputcsv($handle, array_merge(
                ['ITEM', 'PAIS', 'MODELO', 'CENTRO COMERCIAL', 'SERIE'],
                $horasHeader,
                ['TOTAL', 'PROMEDIO']
            ));

            foreach ($rows as $i => $row) {
                $line = [$i + 1, strtoupper($row->pais ?? ''), $row->modelo, $row->centro, $row->serie];

                $horasTrabajadas = 0;
                $totalFila       = 0;

                for ($h = $horaInicio; $h <= $horaFin; $h++) {
                    $val    = (float) ($row->{"h{$h}"} ?? 0);
                    $line[] = $val;
                    if ($val > 0) {
                        $horasTrabajadas++;
                        $totalFila += $val;
                    }
                }

                $promedio = $horasTrabajadas > 0 ? round($totalFila / $horasTrabajadas, 2) : 0;
                $line[]   = $row->total;
                $line[]   = $promedio;

                fputcsv($handle, $line);
            }

            fclose($handle);
        }, "cuadre_{$fecha}_{$pais}.csv", ['Content-Type' => 'text/csv']);
    }
}