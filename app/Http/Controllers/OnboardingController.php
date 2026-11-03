<?php
namespace App\Http\Controllers;
use App\Services\OnboardingService;
use Illuminate\Http\Request;

class OnboardingController extends Controller {
 public function index(Request $r, OnboardingService $svc) {
   $u = $r->user();
   return view('onboarding.index', ['steps'=>$svc->stepsFor($u), 'dismissed'=>(bool) ($u->settings->onboarding_dismissed ?? false)]);
 }
 public function dismiss(Request $r) {
   $settings = $r->user()->settings ?? $r->user()->settings()->create([]);
   $settings->update(['onboarding_dismissed'=>true]);
   return redirect()->route('dashboard')->with('success','Welcome guide hidden. You can reopen it any time from Help.');
 }
}
