<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /** La pantalla de registro es la misma del login, con la pestaña "Solicitar acceso" abierta. */
    public function create(): View
    {
        return view('auth.login', ['tab' => 'registro']);
    }

    /**
     * Registra una SOLICITUD de acceso: la cuenta se crea siempre como Cajero
     * y queda inactiva hasta que un administrador la apruebe.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'domicilio' => ['required', 'string', 'max:150'],
            'telefono'  => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'email.unique'       => 'Ese correo ya está registrado.',
            'telefono.regex'     => 'El teléfono solo puede llevar números, espacios, +, - y paréntesis.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        User::create([
            'name'      => $datos['name'],
            'apellidos' => $datos['apellidos'],
            'domicilio' => $datos['domicilio'],
            'telefono'  => $datos['telefono'],
            'email'     => $datos['email'],
            'password'  => $datos['password'],
            'role_id'   => Role::where('nombre', 'Cajero')->value('id') ?? 2, // nunca administrador
            'activo'    => false,                                               // espera aprobación
        ]);

        return redirect()->route('login')
            ->with('status', 'Solicitud enviada. Un administrador debe aprobar tu cuenta antes de que puedas entrar.');
    }
}
