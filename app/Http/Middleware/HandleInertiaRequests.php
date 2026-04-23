<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        // Cargar el rol si el usuario existe
        if ($user) {
            $user->load('rol');
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'IdUsuario' => $user->IdUsuario,
                    'Nombres' => $user->Nombres,
                    'Apellidos' => $user->Apellidos,
                    'Tipo' => $user->Tipo,
                    'Usu' => $user->Usu,
                    'Activo' => $user->Activo,
                    'rol_id' => $user->rol_id,
                    'rol' => $user->rol ? $user->rol->nombre : null,
                ] : null,
            ],
        ];
    }
}
