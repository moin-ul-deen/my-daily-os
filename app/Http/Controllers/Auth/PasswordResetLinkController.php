<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller; use Illuminate\Http\Request; use Illuminate\Support\Facades\Password;
class PasswordResetLinkController extends Controller {
 public function create(){ return view('auth.forgot-password'); }
 public function store(Request $r){ $r->validate(['email'=>'required|email']); $s=Password::sendResetLink($r->only('email')); return $s===Password::RESET_LINK_SENT ? back()->with('status',__($s)) : back()->withErrors(['email'=>__($s)]); }
}