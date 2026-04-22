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

    private function buildHoraSelects(): array
    {
        return array_map(
            fn($h) => "SUM(CASE WHEN HOUR(p.FechaProduccion) = {$h} THEN p.ProduccionFinal ELSE 0 END) as h{$h}",
            range(8, 23)
        );
    }

    private function queryHoraBase(string $tabla, string $fecha)
    {
        $query = DB::table("{$tabla} as p")
            ->join('centros as c', 'p.idCentro', '=', 'c.IdCentro')
            ->join('maquinas as m', 'p.idMaquina', '=', 'm.IdMaquina')
            ->whereDate('p.FechaProduccion', $fecha)
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

    public function peru(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(14)->toDateString());
        $fechaFin    = $request->get('fecha_fin', now()->toDateString());

        $datos = DB::table('produccionperu as p')
            ->join('centros as c', 'p.idCentro', '=', 'c.IdCentro')
            ->join('maquinas as m', 'p.idMaquina', '=', 'm.IdMaquina')
            ->whereBetween(DB::raw('DATE(p.FechaProduccion)'), [$fechaInicio, $fechaFin])
            ->where('m.EsVisible', 1)
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY SUM(p.ProduccionFinal) DESC) as item')
            ->selectRaw('m.Modelo as modelo')
            ->selectRaw('c.NombreCentro as centro')
            ->selectRaw('m.CodigoMaquina as serie')
            ->selectRaw('SUM(p.ProduccionFinal) as total')
            ->selectRaw('ROUND(AVG(p.ProduccionFinal), 2) as promedio')
            ->selectRaw('SUM(CASE WHEN HOUR(p.FechaProduccion) = 8  THEN p.ProduccionFinal ELSE 0 END) as h8')
            ->selectRaw('SUM(CASE WHEN HOUR(p.FechaProduccion) = 9  THEN p.ProduccionFinal ELSE 0 END) as h9')
            ->selectRaw('SUM(CASE WHEN HOUR(p.FechaProduccion) = 10 THEN p.ProduccionFinal ELSE 0 END) as h10')
            ->selectRaw('SUM(CASE WHEN HOUR(p.FechaProduccion) = 11 THEN p.ProduccionFinal ELSE 0 END) as h11')
            ->selectRaw('SUM(CASE WHEN HOUR(p.FechaProduccion) = 12 THEN p.ProduccionFinal ELSE 0 END) as h12')
            ->selectRaw('SUM(CASE WHEN HOUR(p.FechaProduccion) = 13 THEN p.ProduccionFinal ELSE 0 END) as h13')
            ->selectRaw('SUM(CASE WHEN HOUR(p.FechaProduccion) = 14 THEN p.ProduccionFinal ELSE 0 END) as h14')
            ->selectRaw('SUM(CASE WHEN HOUR(p.FechaProduccion) = 15 THEN p.ProduccionFinal ELSE 0 END) as h15')
            ->groupBy('m.Modelo', 'c.NombreCentro', 'm.CodigoMaquina')
            ->orderByDesc('total')
            ->get();

        $centros = $this->centrosQuery('peru')->orderBy('NombreCentro')->get();

        return inertia('ProduccionPeru', [
            'datos'   => $datos,
            'centros' => $centros,
            'filtros' => ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin],
        ]);
    }

    public function chile(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(14)->toDateString());
        $fechaFin    = $request->get('fecha_fin', now()->toDateString());

        $datos = DB::table('produccionchile as p')
            ->join('centros as c', 'p.idCentro', '=', 'c.IdCentro')
            ->join('maquinas as m', 'p.idMaquina', '=', 'm.IdMaquina')
            ->whereBetween(DB::raw('DATE(p.FechaProduccion)'), [$fechaInicio, $fechaFin])
            ->where('m.EsVisible', 1)
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY SUM(p.ProduccionFinal) DESC) as item')
            ->selectRaw('m.Modelo as modelo')
            ->selectRaw('c.NombreCentro as centro')
            ->selectRaw('m.CodigoMaquina as serie')
            ->selectRaw('SUM(p.ProduccionFinal) as total')
            ->selectRaw('ROUND(AVG(p.ProduccionFinal), 2) as promedio')
            ->groupBy('m.Modelo', 'c.NombreCentro', 'm.CodigoMaquina')
            ->orderByDesc('total')
            ->get();

        $centros = $this->centrosQuery('chile')
            ->where('IdCentro', '!=', 46)
            ->orderBy('NombreCentro')
            ->get();

        return inertia('ProduccionChile', [
            'datos'   => $datos,
            'centros' => $centros,
            'filtros' => ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin],
        ]);
    }

    public function colombia(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(14)->toDateString());
        $fechaFin    = $request->get('fecha_fin', now()->toDateString());

        $datos = DB::table('produccioncolombia as p')
            ->join('centros as c', 'p.idCentro', '=', 'c.IdCentro')
            ->join('maquinas as m', 'p.idMaquina', '=', 'm.IdMaquina')
            ->whereBetween(DB::raw('DATE(p.FechaProduccion)'), [$fechaInicio, $fechaFin])
            ->where('m.EsVisible', 1)
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY SUM(p.ProduccionFinal) DESC) as item')
            ->selectRaw('m.Modelo as modelo')
            ->selectRaw('c.NombreCentro as centro')
            ->selectRaw('m.CodigoMaquina as serie')
            ->selectRaw('SUM(p.ProduccionFinal) as total')
            ->selectRaw('ROUND(AVG(p.ProduccionFinal), 2) as promedio')
            ->groupBy('m.Modelo', 'c.NombreCentro', 'm.CodigoMaquina')
            ->orderByDesc('total')
            ->get();

        $centros = $this->centrosQuery('colombia')->orderBy('NombreCentro')->get();

        return inertia('ProduccionColombia', [
            'datos'   => $datos,
            'centros' => $centros,
            'filtros' => ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin],
        ]);
    }

    public function australia(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(14)->toDateString());
        $fechaFin    = $request->get('fecha_fin', now()->toDateString());

        $datos = DB::table('produccionaustralia as p')
            ->join('centros as c', 'p.idCentro', '=', 'c.IdCentro')
            ->join('maquinas as m', 'p.idMaquina', '=', 'm.IdMaquina')
            ->whereBetween(DB::raw('DATE(p.FechaProduccion)'), [$fechaInicio, $fechaFin])
            ->where('m.EsVisible', 1)
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY SUM(p.ProduccionFinal) DESC) as item')
            ->selectRaw('m.Modelo as modelo')
            ->selectRaw('c.NombreCentro as centro')
            ->selectRaw('m.CodigoMaquina as serie')
            ->selectRaw('SUM(p.ProduccionFinal) as total')
            ->selectRaw('ROUND(AVG(p.ProduccionFinal), 2) as promedio')
            ->groupBy('m.Modelo', 'c.NombreCentro', 'm.CodigoMaquina')
            ->orderByDesc('total')
            ->get();

        $centros = $this->centrosQuery('australia')->orderBy('NombreCentro')->get();

        return inertia('ProduccionAustralia', [
            'datos'   => $datos,
            'centros' => $centros,
            'filtros' => ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin],
        ]);
    }

    public function hora(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());
        $pais  = $request->get('pais', 'peru');
        $tabla = $this->tablaProduccion($pais);

        $datos = $this->queryHoraBase($tabla, $fecha)
            ->get()
            ->values()
            ->map(fn($item, $i) => (object) array_merge(['item' => $i + 1], (array) $item));

        return inertia('ProduccionHora', [
            'datos'   => $datos,
            'filtros' => ['fecha' => $fecha, 'pais' => $pais],
        ]);
    }

    public function controlar(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());
        $pais  = $request->get('pais', 'peru');
        $tabla = $this->tablaProduccion($pais);

        $query = DB::table("{$tabla} as p")
            ->join('centros as c', 'p.idCentro', '=', 'c.IdCentro')
            ->join('maquinas as m', 'p.idMaquina', '=', 'm.IdMaquina')
            ->whereDate('p.FechaProduccion', $fecha)
            ->where('m.EsVisible', 1);

        $datos = (clone $query)
            ->selectRaw('c.NombreCentro')
            ->selectRaw('m.Modelo')
            ->selectRaw('m.CodigoMaquina')
            ->selectRaw('SUM(p.ProduccionFinal) as total')
            ->selectRaw('COUNT(p.idProduccion) as conteo')
            ->groupBy('c.NombreCentro', 'm.Modelo', 'm.CodigoMaquina')
            ->orderByDesc('total')
            ->get();

        $totales = (clone $query)
            ->selectRaw('SUM(p.ProduccionFinal) as total_general')
            ->selectRaw('COUNT(p.idProduccion) as conteo_general')
            ->first();

        $centros = $this->centrosQuery($pais)->orderBy('NombreCentro')->get();

        return inertia('ProduccionControlar', [
            'datos'   => $datos,
            'centros' => $centros,
            'totales' => $totales,
            'filtros' => ['fecha' => $fecha, 'pais' => $pais],
        ]);
    }

    public function exportarCuadre(Request $request): StreamedResponse
    {
        $fecha  = $request->get('fecha', now()->toDateString());
        $paises = ['peru', 'chile', 'colombia', 'australia'];

        $unionQuery = null;

        foreach ($paises as $pais) {
            $sub = $this->queryHoraBase($this->tablaProduccion($pais), $fecha)
                        ->addSelect(DB::raw("'{$pais}' as pais"));

            $unionQuery = $unionQuery ? $unionQuery->unionAll($sub) : $sub;
        }

        $rows = DB::query()->fromSub($unionQuery, 'u')->get()->values();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, array_merge(
                ['ITEM', 'MODELO', 'CENTRO COMERCIAL', 'SERIE'],
                array_map(fn($h) => "{$h}:00", range(8, 23)),
                ['TOTAL']
            ));

            foreach ($rows as $i => $row) {
                $line = [$i + 1, $row->modelo, $row->centro, $row->serie];
                foreach (range(8, 23) as $h) {
                    $line[] = $row->{"h{$h}"} ?? 0;
                }
                $line[] = $row->total;
                fputcsv($handle, $line);
            }

            fclose($handle);
        }, "cuadre_{$fecha}.csv", ['Content-Type' => 'text/csv']);
    }
}