<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller; use Illuminate\Http\Request; use Illuminate\Support\Facades\Hash; use Illuminate\Validation\Rules\Password as Pw;
class PasswordController extends Controller { public function update(Request $r){ $v=$r->validate(['current_password'=>['required','current_password'],'password'=>['required',Pw::defaults(),'confirmed']]); $r->user()->update(['password'=>Hash::make($v['password'])]); return back()->with('status','password-updated'); } }
