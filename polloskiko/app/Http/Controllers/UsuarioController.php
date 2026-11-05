<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    private function reglas(?User $usuario = null): array
    {
        return [
            'name'      => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'domicilio' => ['required', 'string', 'max:150'],
            'telefono'  => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'role_id'   => ['required', 'exists:roles,id'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario)],
            'password'  => [$usuario ? 'nullable' : 'required', Password::min(8)->letters()->numbers()],
        ];
    }

    private function mensajes(): array
    {
        return [
            'email.unique'   => 'Ese correo ya está registrado en otro usuario.',
            'telefono.regex' => 'El teléfono solo puede llevar números, espacios, +, - y paréntesis.',
        ];
    }

    /** Cierra de inmediato las sesiones abiertas de un usuario (sesiones guardadas en la base de datos). */
    private function cerrarSesiones(User $usuario): void
    {
        DB::table('sessions')->where('user_id', $usuario->id)->delete();
    }

    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('q', ''));

        $usuarios = User::with('role')
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$buscar}%")
                ->orWhere('apellidos', 'like', "%{$buscar}%")
                ->orWhere('email', 'like', "%{$buscar}%")))
            ->orderByDesc('activo')
            ->orderBy('name')
            ->get();

        $ventas = Venta::selectRaw('user_id, COUNT(*) as notas, SUM(total) as total')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $resumen = [
            'total'     => User::count(),
            'activos'   => User::where('activo', true)->count(),
            'admins'    => User::where('role_id', 1)->where('activo', true)->count(),
            'inactivos' => User::where('activo', false)->count(),
        ];

        return view('usuarios.index', compact('usuarios', 'ventas', 'resumen', 'buscar'));
    }

    public function create()
    {
        $roles = Role::orderBy('id')->get();

        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());
        $usuario = User::create($datos + ['activo' => true]);

        return redirect()->route('usuarios.index')
            ->with('exito', "¡Usuario '{$usuario->name}' registrado con éxito!");
    }

    public function edit(User $usuario)
    {
        $roles = Role::orderBy('id')->get();

        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, User $usuario)
    {
        $datos = $request->validate($this->reglas($usuario), $this->mensajes());

        if (blank($datos['password'] ?? null)) {
            unset($datos['password']); // si lo deja vacío, no se cambia
        }

        $datos['activo'] = $request->boolean('activo');

        // El administrador no puede desactivarse ni quitarse su propio rol
        if ($usuario->is($request->user())) {
            $datos['activo']  = true;
            $datos['role_id'] = $usuario->role_id;
        }

        $usuario->update($datos);

        if (! $usuario->activo) {
            $this->cerrarSesiones($usuario);
        }

        return redirect()->route('usuarios.index')
            ->with('exito', "Usuario '{$usuario->name}' actualizado.");
    }

    public function estado(Request $request, User $usuario)
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $activar = ! $usuario->activo;
        $usuario->update(['activo' => $activar]);

        if (! $activar) {
            $this->cerrarSesiones($usuario);
        }

        return back()->with('exito', $activar
            ? "Usuario '{$usuario->name}' activado."
            : "Usuario '{$usuario->name}' desactivado. Ya no puede iniciar sesión.");
    }
}
