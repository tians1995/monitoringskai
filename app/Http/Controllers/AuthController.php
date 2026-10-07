<?php
namespace App\Http\Controllers;
use App\Models\User; use Illuminate\Http\Request; use Illuminate\Support\Facades\Hash; use Inertia\Inertia;
class AuthController extends Controller { public function show(){return Inertia::render('Auth/Login');} public function login(Request $r){$d=$r->validate(['email'=>'required|email','password'=>'required']);$u=User::where('email',$d['email'])->first();if(!$u||!Hash::check($d['password'],$u->password)) return back()->withErrors(['email'=>'Email atau password salah.']);auth()->login($u,$r->boolean('remember'));$r->session()->regenerate();return redirect()->route('dashboard');} public function logout(Request $r){auth()->logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('login');} }
