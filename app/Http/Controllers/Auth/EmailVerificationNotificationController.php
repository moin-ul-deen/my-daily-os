<?php
namespace App\Http\Controllers\Auth; use App\Http\Controllers\Controller; use Illuminate\Http\Request;
class EmailVerificationNotificationController extends Controller { public function store(Request $r){ if($r->user()->hasVerifiedEmail()) return redirect()->intended(route('dashboard')); $r->user()->sendEmailVerificationNotification(); return back()->with('status','verification-link-sent'); } }
