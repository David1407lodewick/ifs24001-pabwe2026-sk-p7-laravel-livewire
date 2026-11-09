<?php

namespace App\Http\Controllers;

use App\Features\Auth\Actions\LoginUserAction;
use App\Features\Auth\Actions\RegisterUserAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('livewire.auth.login');
    }

    public function login(Request $request, LoginUserAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        if (! $action->execute($validated['login'], $validated['password'], $request->boolean('remember'))) {
            return back()
                ->withErrors(['login' => 'Username/email atau password yang Anda masukkan tidak sesuai.'])
                ->withInput($request->only('login'));
        }

        return redirect()->route(Auth::user()->isAdmin() ? 'admin.users' : 'stocks.index');
    }

    public function showRegister(): View
    {
        return view('livewire.auth.register');
    }

    public function register(Request $request, RegisterUserAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $action->execute([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'member',
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->flash('success', 'Registrasi berhasil! Selamat datang di Stock Barang Pro.');

        return redirect()->route('stocks.index');
    }
}
