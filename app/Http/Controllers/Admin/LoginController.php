<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //Manda al formulario de login
    public function index()
    {
        return view('admin.auth.login');
    }

    //Valida las credenciales y autentica al usuario
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return back()
                ->withErrors([
                    'email' => 'Credenciales incorrectas.'
                ])
                ->withInput($request->only('email'));
        }

        $user = Auth::user();

        if ($user->estado !== 'activo') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors([
                    'email' => 'Tu usuario está inactivo.'
                ])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        return redirect()->route('panel')
            ->with('success', 'Bienvenido ' . $user->name);
    }
}
