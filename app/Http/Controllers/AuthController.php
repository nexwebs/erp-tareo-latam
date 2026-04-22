<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return inertia('Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'Usu' => 'required|string',
            'Pass' => 'required|string',
        ]);

        $_usuario = Usuario::where('Usu', $request->Usu)->first();

        if (! $_usuario) {
            return back()->withErrors(['Usu' => 'Usuario no encontrado']);
        }

        if (! $_usuario->Activo) {
            return back()->withErrors(['Usu' => 'Usuario inactivo']);
        }

        $passwordDB = $_usuario->Pass;
        $passwordInput = $request->Pass;

        if ($passwordDB !== $passwordInput) {
            return back()->withErrors(['Pass' => 'Contraseña incorrecta']);
        }

        Auth::login($_usuario);

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function authenticated(Request $request)
    {
        $user = Auth::user();

        return [
            'id' => $user->IdUsuario,
            'nombres' => $user->Nombres,
            'apellidos' => $user->Apellidos,
            'tipo' => $user->Tipo,
            'rol_id' => $user->rol_id,
            'rol' => $user->rol ? $user->rol->nombre : null,
        ];
    }
}
