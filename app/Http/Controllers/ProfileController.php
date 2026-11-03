<?php
namespace App\Http\Controllers; use Illuminate\Http\Request; use Illuminate\Support\Facades\Redirect;
class ProfileController extends Controller {
 public function edit(Request $r){ return view('settings.profile',['user'=>$r->user()]); }
 public function update(Request $r){ $v=$r->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email,'.$r->user()->id,'city'=>'nullable|string','country'=>'nullable|string','timezone'=>'nullable|string','date_of_birth'=>'nullable|date','gender'=>'nullable|string|max:20','preferred_language'=>'nullable|string']); $r->user()->fill($v); if($r->user()->isDirty('email')) $r->user()->email_verified_at=null; $r->user()->save(); return back()->with('status','profile-updated'); }
 public function destroy(Request $r){ $r->validateWithBag('userDeletion',['password'=>['required','current_password']]); $u=$r->user(); auth()->logout(); $u->delete(); $r->session()->invalidate(); $r->session()->regenerateToken(); return Redirect::to('/'); }
}