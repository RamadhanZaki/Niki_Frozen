<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthWebController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $user = Auth::user();

        if ($user->status !== 'aktif') {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Akun Anda tidak aktif. Hubungi administrator.'],
            ]);
        }

        // Simpan info ke session
        session([
            'name'      => $user->name,
            'role'      => $user->role,
            'branch_id' => $user->branch_id,
        ]);

        $request->session()->regenerate();

        return $this->redirectByRole($user->role);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        // Kasir dengan shift masih terbuka DILARANG logout manual — supaya
        // shift tidak "nyangkut" terbuka tanpa pernah ditutup (bikin rekonsiliasi
        // kas Owner berantakan & jam kerja shift jadi tidak akurat).
        //
        // Ini validasi SERVER-SIDE, bukan cuma pop-up di browser — supaya
        // tidak bisa dilewati walau JS di-nonaktifkan atau logout dipicu lewat
        // cara lain. Popup peringatan di sisi client (lihat layouts/app.blade.php)
        // cuma untuk kasih feedback instan, keputusan final tetap di sini.
        //
        // Pengecualian: logout PAKSA setelah kasir mengganti email/password
        // sendiri (lihat kasir/settings.blade.php) tetap harus selalu berhasil
        // walau shift sedang terbuka — itu logout demi keamanan (supaya sesi
        // lama dengan kredensial lama tidak nyangkut), bukan logout santai yang
        // bisa ditunda.
        $isForcedForSecurity = $request->input('reason') === 'security';

        if ($user && $user->role === 'kasir' && !$isForcedForSecurity) {
            $hasOpenShift = Shift::where('user_id', $user->id)->whereNull('closed_at')->exists();

            if ($hasOpenShift) {
                return back()->with('error', 'Shift kamu masih terbuka. Tutup shift terlebih dahulu sebelum logout.');
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function redirectByRole(string $role)
    {
        return match ($role) {
            'owner'  => redirect()->route('owner.dashboard'),
            'kasir'  => redirect()->route('kasir.pos'),
            default  => redirect()->route('login'),
        };
    }
}
