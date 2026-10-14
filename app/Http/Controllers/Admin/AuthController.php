<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Muestra el formulario de inicio de sesión administrativo.
     */
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = $request->user();

            return redirect()->route($user->rutaPanel());
        }

        return view('admin.auth.login');
    }

    /**
     * Procesa la solicitud de inicio de sesión administrativo con control de intentos fallidos.
     */
    public function login(AdminLoginRequest $request): RedirectResponse
    {
        $credenciales = $request->validated();
        $recordar = $request->boolean('remember');

        $throttleKey = Str::transliterate(Str::lower($credenciales['nombre_usuario']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $segundos = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'nombre_usuario' => ["Demasiados intentos fallidos. Por favor intenta de nuevo en {$segundos} segundos."],
            ]);
        }

        $user = User::where('nombre_usuario', $credenciales['nombre_usuario'])->first();

        if (! $user || ! Hash::check($credenciales['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'nombre_usuario' => ['Las credenciales proporcionadas no coinciden con nuestros registros.'],
            ]);
        }

        if ($user->estado !== 'activo') {
            throw ValidationException::withMessages([
                'nombre_usuario' => ['Tu cuenta se encuentra inactiva. Contacta al administrador del sistema.'],
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, $recordar);
        $request->session()->regenerate();

        // Actualizar último acceso
        $user->update(['ultimo_acceso' => now()]);

        return redirect()->intended(route($user->rutaPanel()))
            ->with('success', 'Bienvenido al panel, '.$user->nombre_completo);
    }

    /**
     * Cierra la sesión activa del usuario.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Has cerrado sesión correctamente.');
    }
}
