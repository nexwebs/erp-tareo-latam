<?php

namespace App\Http\Controllers;

use App\Models\Centro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CentroController extends Controller
{
    public function listar(Request $request)
    {
        $pais = $request->get('pais', 'peru');

        $query = Centro::where('EsActivo', 1);

        $query = match ($pais) {
            'chile' => $query->where('EsChile', 1),
            'colombia' => $query->where('EsColombia', 1),
            'australia' => $query->where('EsAustralia', 1),
            'provincia' => $query->where('EsProvincia', 1),
            default => $query->where('EsChile', 0)
                ->where('EsColombia', 0)
                ->where('EsAustralia', 0)
                ->where('EsProvincia', 0),
        };

        $centros = $query->orderBy('NombreCentro')->get();

        $zonas = DB::table('zonas')->orderBy('descripcion')->get();

        return inertia('CentrosListado', [
            'datos' => $centros,
            'zonas' => $zonas,
            'paisActual' => $pais,
        ]);
    }

    public function crear(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'pais' => 'required|in:chile,colombia,australia,peru,provincia',
            'idZona' => 'required|integer',
            'tOn' => 'nullable',
            'tOff' => 'nullable',
            'idZonaProv' => 'nullable|integer',
        ]);

        try {
            $result = DB::select('CALL usp_crear_centro(?, ?, ?, ?, ?, ?, ?)', [
                $request->nombre,
                $request->pais,
                $request->idZona,
                null,
                $request->tOn ?? '08:00:00',
                $request->tOff ?? '23:00:00',
                $request->idZonaProv,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Centro creado correctamente',
                'data' => $result[0] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'db_error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'idCentro' => 'required|integer',
            'nombre' => 'required|string|max:50',
            'tOn' => 'nullable',
            'tOff' => 'nullable',
        ]);

        $idCentro = $request->input('idCentro');

        $existe = Centro::where('IdCentro', $idCentro)->exists();
        if (! $existe) {
            return response()->json([
                'success' => false,
                'error' => 'not_found',
                'message' => 'Centro no existe',
            ], 404);
        }

        try {
            DB::table('centros')
                ->where('IdCentro', $idCentro)
                ->update([
                    'NombreCentro' => $request->nombre,
                    'tOn' => $request->tOn ?? '08:00:00',
                    'tOff' => $request->tOff ?? '23:00:00',
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Centro actualizado correctamente',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'db_error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function darBaja(Request $request)
    {
        $request->validate([
            'idCentro' => 'required|integer',
        ]);

        try {
            $result = DB::select('CALL usp_dar_baja_centro(?)', [
                $request->idCentro,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Centro dado de baja',
                'data' => $result[0] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'db_error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function eliminarStaging(Request $request)
    {
        $request->validate([
            'idCentro' => 'required|integer',
        ]);

        try {
            $result = DB::select('CALL usp_eliminar_centro_staging(?)', [
                $request->idCentro,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Centro eliminado',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'db_error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getZonas()
    {
        $zonas = DB::table('zonas')->orderBy('descripcion')->get();

        return response()->json([
            'success' => true,
            'data' => $zonas,
        ]);
    }

    public function crearZona(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
        ]);

        $descripcion = $request->input('descripcion');

        $existe = DB::table('zonas')
            ->where('descripcion', $descripcion)
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'error' => 'zona_existe',
                'message' => 'Ya existe una zona con esa descripción',
            ], 422);
        }

        try {
            $id = DB::table('zonas')->insertGetId([
                'descripcion' => $descripcion,
                'idGroup' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Zona creada correctamente',
                'data' => ['id' => $id, 'descripcion' => $descripcion],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'db_error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function eliminarZona(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $id = $request->input('id');

        $enUso = DB::table('centros')
            ->where('zona', $id)
            ->exists();

        if ($enUso) {
            return response()->json([
                'success' => false,
                'error' => 'zona_en_uso',
                'message' => 'La zona está asignada a centros',
            ], 422);
        }

        try {
            DB::table('zonas')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Zona eliminada correctamente',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'db_error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function listarTodos(Request $request)
    {
        $pais = $request->get('pais', 'peru');

        $query = Centro::query();

        $query = match ($pais) {
            'chile' => $query->where('EsChile', 1),
            'colombia' => $query->where('EsColombia', 1),
            'australia' => $query->where('EsAustralia', 1),
            'provincia' => $query->where('EsProvincia', 1),
            default => $query->where('EsChile', 0)
                ->where('EsColombia', 0)
                ->where('EsAustralia', 0)
                ->where('EsProvincia', 0),
        };

        $centros = $query->orderBy('NombreCentro')->get(['IdCentro', 'NombreCentro']);

        return response()->json([
            'success' => true,
            'data' => $centros,
        ]);
    }
}
