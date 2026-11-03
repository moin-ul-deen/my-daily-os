<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller; use Illuminate\Http\Request; use Illuminate\Support\Facades\Hash; use Illuminate\Support\Facades\Password; use Illuminate\Support\Str; use Illuminate\Validation\Rules;
class NewPasswordController extends Controller {
 public function create(Request $r){ return view('auth.reset-password',['request'=>$r]); }
 public function store(Request $r){ $r->validate(['token'=>'required','email'=>'required|email','password'=>['required','confirmed',Rules\Password::defaults()]]); $s=Password::reset($r->only('email','password','password_confirmation','token'),function($u,$pw){ $u->forceFill(['password'=>Hash::make($pw)])->setRememberToken(Str::random(60)); $u->save(); }); return $s===Password::PASSWORD_RESET ? redirect()->route('login')->with('status',__($s)) : back()->withErrors(['email'=>__($s)]); }
}