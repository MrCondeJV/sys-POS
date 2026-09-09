<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Muestra la vista de inicio de sesión.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Procesa la autenticación del usuario con protección contra ataques de fuerza bruta.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Demasiados intentos de inicio de sesión. Por favor intenta de nuevo en {$seconds} segundos.",
            ]);
        }

        // Validar si el usuario existe y está activo antes de iniciar sesión
        $user = User::where('email', $credentials['email'])->first();

        if ($user && ! $user->isActivo()) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'email' => 'Esta cuenta de usuario se encuentra inactiva. Comunícate con el administrador.',
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $authenticatedUser = Auth::user();
            if ($authenticatedUser->empresa_id) {
                CompanyContext::setCompanyId($authenticatedUser->empresa_id);
                setPermissionsTeamId($authenticatedUser->empresa_id);
            }

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey);

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    /**
     * Cierra la sesión activa del usuario y limpia el contexto multiempresa.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        CompanyContext::clear();
        setPermissionsTeamId(null);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
