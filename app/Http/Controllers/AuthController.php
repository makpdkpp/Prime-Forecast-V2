<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\TwoFactorCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'])->onlyInput('email');
        }

        // Check if user is active
        if (!$user->is_active) {
            return back()->withErrors(['email' => 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ'])->onlyInput('email');
        }

        $rawPassword = $data['password'];
        $stored = (string) ($user->password ?? '');

        $valid = false;

        if ($stored !== '' && str_starts_with($stored, '$2y$')) {
            $valid = Hash::check($rawPassword, $stored);
        } elseif ($stored !== '') {
            $valid = hash_equals(md5($rawPassword), $stored);
        }

        if (!$valid) {
            return back()->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'])->onlyInput('email');
        }

        // Check if 2FA is enabled
        if ($user->hasTwoFactorEnabled()) {
            // Check if OTP already exists and not expired
            if ($user->two_factor_code && $user->two_factor_expires_at && now()->isBefore($user->two_factor_expires_at)) {
                // OTP still valid, don't send new one
                $request->session()->put('2fa_user_id', $user->user_id);
                return redirect()->route('2fa.verify')->with('success', 'รหัส OTP ถูกส่งไปยัง email ของคุณแล้ว');
            }
            
            // Generate OTP and send email
            $code = $user->generateTwoFactorCode();
            Mail::to($user->email)->send(new TwoFactorCode($user, $code));
            
            // Store user_id in session (not logged in yet)
            $request->session()->put('2fa_user_id', $user->user_id);
            
            return redirect()->route('2fa.verify')->with('success', 'รหัส OTP ถูกส่งไปยัง email ของคุณแล้ว');
        }

        // If 2FA not enabled, login normally
        Auth::login($user);
        $request->session()->regenerate();

        return match ((int) $user->role_id) {
            1 => redirect()->route('admin.dashboard'),
            2 => redirect()->route('teamadmin.dashboard'),
            3 => redirect()->route('user.dashboard'),
            default => redirect()->route('login'),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showTwoFactorVerify(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }
        
        $userId = $request->session()->get('2fa_user_id');
        $user = User::find($userId);
        
        if (!$user) {
            $request->session()->forget('2fa_user_id');
            return redirect()->route('login')->with('error', 'Session หมดอายุ กรุณา login ใหม่');
        }
        
        return view('auth.two-factor-verify', compact('user'));
    }

    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);
        
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login')->with('error', 'Session หมดอายุ กรุณา login ใหม่');
        }
        
        $userId = $request->session()->get('2fa_user_id');
        $user = User::find($userId);
        
        if (!$user) {
            $request->session()->forget('2fa_user_id');
            return redirect()->route('login')->with('error', 'Session หมดอายุ กรุณา login ใหม่');
        }
        
        if (!$user->verifyTwoFactorCode($request->code)) {
            return back()->withErrors(['code' => 'รหัส OTP ไม่ถูกต้องหรือหมดอายุ']);
        }
        
        // OTP correct - clear code and login
        $user->resetTwoFactorCode();
        $request->session()->forget('2fa_user_id');
        
        Auth::login($user);
        $request->session()->regenerate();
        
        return match ((int) $user->role_id) {
            1 => redirect()->route('admin.dashboard'),
            2 => redirect()->route('teamadmin.dashboard'),
            3 => redirect()->route('user.dashboard'),
            default => redirect()->route('login'),
        };
    }

    public function resendTwoFactorCode(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }
        
        $userId = $request->session()->get('2fa_user_id');
        $user = User::find($userId);
        
        if (!$user) {
            $request->session()->forget('2fa_user_id');
            return redirect()->route('login')->with('error', 'Session หมดอายุ กรุณา login ใหม่');
        }
        
        // Check if OTP was recently sent (within last 60 seconds)
        if ($user->two_factor_expires_at && $user->two_factor_expires_at->diffInSeconds(now()) < 240) {
            // OTP was sent less than 60 seconds ago (5 min - 4 min = 1 min)
            return back()->with('error', 'กรุณารอสักครู่ก่อนขอรหัส OTP ใหม่');
        }
        
        // Generate new OTP and send email
        $code = $user->generateTwoFactorCode();
        Mail::to($user->email)->send(new TwoFactorCode($user, $code));
        
        return back()->with('success', 'ส่งรหัส OTP ใหม่ไปยัง email ของคุณแล้ว');
    }
}
