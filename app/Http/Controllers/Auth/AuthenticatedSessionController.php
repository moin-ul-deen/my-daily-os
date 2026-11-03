<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller; use Illuminate\Http\Request; use Illuminate\Support\Facades\Auth; use Illuminate\Validation\ValidationException;
class AuthenticatedSessionController extends Controller {
 public function create(){ return view('auth.login'); }
 public function store(Request $r){ $r->validate(['email'=>'required|email','password'=>'required']); if(!Auth::attempt($r->only('email','password'),$r->boolean('remember'))){ throw ValidationException::withMessages(['email'=>'These credentials do not match our records.']); } $r->session()->regenerate(); return redirect()->intended(route('dashboard')); }
 public function destroy(Request $r){ Auth::guard('web')->logout(); $r->session()->invalidate(); $r->session()->regenerateToken(); return redirect('/login'); }
}