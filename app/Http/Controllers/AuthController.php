<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\RegisterAdminRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Contraseña maestra para registrar administradores
     */
    private const ADMIN_MASTER_PASSWORD = 'admin2024';

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('username', $request->username)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user, $request->filled('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))->with('success', '¡Bienvenido!');
        }

        return back()->withErrors([
            'username' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('username');
    }

    /**
     * Show the registration form for vendedor.
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.register');
    }

    /**
     * Handle a registration request for vendedor.
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'vendedor',
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', '¡Cuenta creada exitosamente!');
    }

    /**
     * Show the registration form for admin.
     */
    public function showRegisterAdminForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.register-admin');
    }

    /**
     * Handle a registration request for admin.
     */
    public function registerAdmin(RegisterAdminRequest $request)
    {
        // Validar contraseña maestra
        if ($request->admin_password !== self::ADMIN_MASTER_PASSWORD) {
            return back()->withErrors([
                'admin_password' => 'La contraseña de administrador es incorrecta.',
            ])->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'admin',
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', '¡Cuenta de administrador creada exitosamente!');
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }

    /**
     * Show the dashboard based on user role.
     */
    public function dashboard()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return view('dashboard.admin');
        }

        return view('dashboard.vendedor');
    }
}

