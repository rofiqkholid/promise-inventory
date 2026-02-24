<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Tampilkan form login
     */
    public function showLoginForm()
    {
        return view('authentication.login_page');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'nik' => 'required|string',
            'password' => 'required|string|min:6',
        ], [
            'nik.required' => 'Employee ID (NIK) harus diisi',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        // Cari user berdasarkan NIK
        $user = User::where('nik', $validated['nik'])->first();

        // Verifikasi password
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return back()
                ->withInput($request->only('nik'))
                ->withErrors([
                    'authentication' => 'NIK atau password salah.',
                ]);
        }

        // Login user
        Auth::login($user, $request->boolean('remember'));

        // Redirect ke halaman yang diminta atau dashboard
        return redirect()->intended(route('dashboard'))
            ->with('success', 'Selamat datang ' . $user->name);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Anda telah logout');
    }

    public function forgetPassword()
    {
        return view('forget_password');
    }
}
