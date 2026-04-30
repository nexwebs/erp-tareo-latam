<?php

namespace App\Http\Controllers;

use App\Models\Centro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaquinaController extends Controller
{
    private function paisesDisponibles(): array
    {
        return ['chile', 'colombia', 'australia', 'peru', 'provincia'];
    }

    private function paisToCountryIdMap(): array
    {
        return [
            'chile' => 1,
            'colombia' => 2,
            'australia' => 7,
            'peru' => [3, 4, 5, 6, 9, 10, 11],
        ];
    }

    public function crear(Request $request)
    {
        $request->validate([
            'serie'     => 'required|string|max:30',
            'modelo'    => 'required|string|max:50',
            'idCentro'  => 'required|integer',
            'country'   => 'required|integer',
            'relay'     => 'required|integer',
        ]);

        try {
            $result = DB::select('CALL usp_crear_maquina_visible(?, ?, ?, ?, ?)', [
                $request->serie,
                $request->modelo,
                $request->idCentro,
                $request->country,
                $request->relay,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Máquina creada y visible',
                'data'    => $result[0] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => 'db_error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function activar(Request $request)
    {
        $request->validate([
            'idMaquina' => 'required|integer',
            'idCentro' => 'required|integer',
            'country' => 'required|integer',
        ]);

        $idMaquina = $request->input('idMaquina');
        $idCentro = $request->input('idCentro');
        $country = $request->input('country');

        $centro = Centro::where('IdCentro', $idCentro)
            ->where('EsActivo', 1)
            ->first();

        if (! $centro) {
            return response()->json([
                'success' => false,
                'error' => 'centro_invalido',
                'message' => 'Centro no existe o está inactivo',
            ], 422);
        }

        $paisCentro = match (true) {
            $centro->EsChile === 1 => 'chile',
            $centro->EsColombia === 1 => 'colombia',
            $centro->EsAustralia === 1 => 'australia',
            default => 'peru',
        };

        $validCountries = $this->paisToCountryIdMap();
        $allowedCountry = $validCountries[$paisCentro] ?? null;

        $countryValido = false;
        if (is_array($allowedCountry)) {
            $countryValido = in_array($country, $allowedCountry);
        } else {
            $countryValido = $country === $allowedCountry;
        }

        if (! $countryValido) {
            return response()->json([
                'success' => false,
                'error' => 'country_invalido',
                'message' => "El country {$country} no corresponde al país del centro ({$paisCentro})",
            ], 422);
        }

        try {
            $result = DB::select('CALL usp_activar_maquina(?, ?, ?)', [
                $idMaquina,
                $idCentro,
                $country,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Máquina activada correctamente',
                'data' => $result[0] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'db_error',
                'message' => 'Error al activar máquina: '.$e->getMessage(),
            ], 500);
        }
    }

    public function darBaja(Request $request)
    {
        $request->validate([
            'idMaquina' => 'required|integer',
        ]);

        $idMaquina = $request->input('idMaquina');

        $tieneTickets = DB::table('tickets')
            ->where('maquina_id', $idMaquina)
            ->whereNotIn('estado', ['Cerrado', 'Cancelado'])
            ->exists();

        if ($tieneTickets) {
            return response()->json([
                'success' => false,
                'error' => 'tickets_pendientes',
                'message' => 'La máquina tiene tickets abiertos',
            ], 422);
        }

        try {
            DB::select('CALL usp_dar_baja_maquina(?)', [$idMaquina]);

            return response()->json([
                'success' => true,
                'message' => 'Máquina dada de baja',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'db_error',
                'message' => 'Error al dar baja: '.$e->getMessage(),
            ], 500);
        }
    }

    public function eliminarStaging(Request $request)
    {
        $request->validate([
            'idMaquina' => 'required|integer',
        ]);

        $idMaquina = $request->input('idMaquina');

        $esStaging = DB::table('maquinas')
            ->where('IdMaquina', $idMaquina)
            ->where('EsVisible', 0)
            ->where('EsActivo', 1)
            ->where('idCentro', 1)
            ->whereNull('country')
            ->exists();

        if (! $esStaging) {
            return response()->json([
                'success' => false,
                'error' => 'no_staging',
                'message' => 'Solo se pueden eliminar máquinas en staging',
            ], 422);
        }

        $produccion = DB::table('produccionperu')
            ->where('idMaquina', $idMaquina)
            ->count()
            + DB::table('produccionchile')
                ->where('idMaquina', $idMaquina)
                ->count()
            + DB::table('produccioncolombia')
                ->where('idMaquina', $idMaquina)
                ->count()
            + DB::table('produccionaustralia')
                ->where('idMaquina', $idMaquina)
                ->count();

        if ($produccion > 0) {
            return response()->json([
                'success' => false,
                'error' => 'tiene_produccion',
                'message' => 'La máquina tiene producción, use baja lógica',
            ], 422);
        }

        try {
            DB::select('CALL usp_eliminar_maquina_staging(?)', [$idMaquina]);

            return response()->json([
                'success' => true,
                'message' => 'Máquina eliminada del staging',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'db_error',
                'message' => 'Error al eliminar: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getCentros(Request $request)
    {
        $pais = $request->get('pais', 'chile');

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

        $centros = $query->orderBy('NombreCentro')->get(['IdCentro', 'NombreCentro']);

        return response()->json([
            'success' => true,
            'data' => $centros,
        ]);
    }
}
