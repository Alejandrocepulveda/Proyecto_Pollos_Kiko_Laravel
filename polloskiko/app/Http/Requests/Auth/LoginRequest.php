<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'acceso'   => ['nullable', 'in:admin,cajero'],
        ];
    }

    /**
     * Intenta iniciar sesión. Revisa tres cosas:
     * 1) que la cuenta esté activa, 2) que las credenciales sean correctas
     * y 3) que la cuenta coincida con el acceso elegido (Administrador o Cajero).
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $usuario = User::where('email', (string) $this->input('email'))->first();

        // Cuenta pendiente de aprobación o desactivada.
        // Solo se avisa si la contraseña es correcta, para no revelar qué correos existen.
        if ($usuario && ! $usuario->activo
            && Hash::check((string) $this->input('password'), $usuario->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está pendiente de aprobación o fue desactivada. Consulta con el administrador.',
            ]);
        }

        if (! Auth::attempt($this->only('email', 'password') + ['activo' => true], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Correo o contraseña incorrectos.',
            ]);
        }

        // La cuenta debe coincidir con el acceso elegido en la pantalla
        $esAdmin = Auth::user()->esAdmin();
        $acceso  = $this->input('acceso');

        if (($acceso === 'admin' && ! $esAdmin) || ($acceso === 'cajero' && $esAdmin)) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => $acceso === 'admin'
                    ? 'Esta cuenta no es de Administrador. Elige la opción Cajero.'
                    : 'Esta cuenta es de Administrador. Elige la opción Administrador.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $segundos = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Demasiados intentos. Vuelve a intentarlo en {$segundos} segundos.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower((string) $this->input('email')) . '|' . $this->ip());
    }
}
