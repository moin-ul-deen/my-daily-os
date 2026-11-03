<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller; use App\Models\User; use Illuminate\Http\Request; use Illuminate\Support\Facades\Auth; use Illuminate\Support\Facades\Hash; use Illuminate\Validation\Rules;
class RegisteredUserController extends Controller {
 public function create(){ return view('auth.register'); }
 public function store(Request $r){ $r->validate(['name'=>['required','string','max:255'],'email'=>['required','string','email','max:255','unique:users'],'password'=>['required','confirmed',Rules\Password::defaults()]]); $u=User::create(['name'=>$r->name,'email'=>$r->email,'password'=>Hash::make($r->password)]); Auth::login($u); return redirect()->route('dashboard'); }
}