@extends('layouts.app')@section('content')<h3>Settings</h3>
<div class="card intro-card"><p class="mb-1"><strong>Your personal settings for the whole app.</strong></p><p class="mb-0 text-muted-x">Everything here saves to your account and is used across Dashboard, Salah, Health and Office. Prayer calculation details (method, Hanafi Asr, minute adjustments) live under <a href="{{ route('salah.settings') }}">Salah Settings</a>.</p></div>

<form method="POST" action="{{ route('settings.update') }}">@csrf @method('PUT')
@php
  $wdStored = collect($settings->working_days ?? [])->map(function($d){
      if (is_numeric($d)) { $n = (int)$d; return $n === 0 ? 7 : $n; }
      $map = ['mon'=>1,'tue'=>2,'wed'=>3,'thu'=>4,'fri'=>5,'sat'=>6,'sun'=>7];
      return $map[strtolower(substr((string)$d,0,3))] ?? null;
  })->filter()->values();
  $accents = ['#8BCB9B'=>'Soft green','#7FB3D5'=>'Sky blue','#E7B765'=>'Warm gold','#D87979'=>'Soft rose','#B49CE0'=>'Lavender','#5EC8C0'=>'Teal'];
@endphp

<div class="card"><h5>About you</h5><p class="text-muted-x">Basic preferences used to display your information.</p>
<div class="row"><div class="col-12 col-md-3 mb-3"><label class="form-label" for="currency">Currency</label><input id="currency" name="currency" class="form-control" placeholder="e.g. PKR" value="{{ $settings->currency }}"><div class="form-text">Shown next to money amounts, e.g. PKR.</div></div>
<div class="col-12 col-md-3 mb-3"><label class="form-label" for="weight_unit">Weight unit</label><input id="weight_unit" name="weight_unit" class="form-control" placeholder="e.g. kg" value="{{ $settings->weight_unit }}"></div>
<div class="col-12 col-md-3 mb-3"><label class="form-label" for="distance_unit">Distance unit</label><input id="distance_unit" name="distance_unit" class="form-control" placeholder="e.g. km" value="{{ $settings->distance_unit }}"></div>
<div class="col-12 col-md-3 mb-3"><label class="form-label" for="time_format">Time format</label><select id="time_format" name="time_format" class="form-select"><option value="12" @selected(($settings->time_format ?? '12')=='12')>12-hour (2:30 PM)</option><option value="24" @selected(($settings->time_format ?? '')=='24')>24-hour (14:30)</option></select></div></div>
<div class="row"><div class="col-12 col-md-4 mb-3"><label class="form-label" for="date_format">Date format</label><select id="date_format" name="date_format" class="form-select"><option value="Y-m-d" @selected(($settings->date_format ?? 'Y-m-d')=='Y-m-d')>2026-11-03</option><option value="d M Y" @selected(($settings->date_format ?? '')=='d M Y')>03 Nov 2026</option><option value="d/m/Y" @selected(($settings->date_format ?? '')=='d/m/Y')>03/11/2026</option></select></div></div>
</div>

<div class="card"><h5>Salah &amp; location</h5><p class="text-muted-x">Your city and timezone decide your prayer times and all clock times in the app. Faisalabad, Pakistan and Asia/Karachi are the starting defaults.</p>
<div class="row"><div class="col-12 col-md-4 mb-3"><label class="form-label" for="city">City</label><input id="city" name="city" class="form-control" placeholder="e.g. Faisalabad" value="{{ $user->city }}"></div>
<div class="col-12 col-md-4 mb-3"><label class="form-label" for="country">Country</label><input id="country" name="country" class="form-control" placeholder="e.g. Pakistan" value="{{ $user->country }}"></div>
<div class="col-12 col-md-4 mb-3"><label class="form-label" for="timezone">Timezone</label><input id="timezone" name="timezone" class="form-control" placeholder="e.g. Asia/Karachi" value="{{ $user->timezone }}"><div class="form-text">Use an IANA timezone name, e.g. Asia/Karachi. All times in the app use this.</div></div></div>
<p class="mb-0"><a href="{{ route('salah.settings') }}">Open Salah Settings</a> <span class="text-muted-x">— calculation method, Hanafi Asr, coordinates and reminder times are set there.</span></p>
</div>

<div class="card"><h5>Health targets</h5><p class="text-muted-x">Your personal targets. The Dashboard and Health pages show your progress against these — they are guidance, not pressure.</p>
<div class="row"><div class="col-12 col-md-3 mb-3"><label class="form-label" for="target_weight">Target weight (kg)</label><input type="number" step="0.1" id="target_weight" name="target_weight" class="form-control" placeholder="e.g. 70" value="{{ $settings->target_weight }}"><div class="form-text">Your goal weight — change it any time.</div></div>
<div class="col-12 col-md-3 mb-3"><label class="form-label" for="water_target_ml">Daily water target (ml)</label><input type="number" id="water_target_ml" name="water_target_ml" class="form-control" placeholder="e.g. 2500" value="{{ $settings->water_target_ml }}"><div class="form-text">2500 ml is about 10 glasses.</div></div>
<div class="col-12 col-md-3 mb-3"><label class="form-label" for="walking_target_km">Daily walking target (km)</label><input type="number" step="0.1" id="walking_target_km" name="walking_target_km" class="form-control" placeholder="e.g. 3" value="{{ $settings->walking_target_km }}"></div>
<div class="col-12 col-md-3 mb-3"><label class="form-label" for="weekly_exercise_sessions">Exercise target (sessions per week)</label><input type="number" id="weekly_exercise_sessions" name="weekly_exercise_sessions" class="form-control" placeholder="e.g. 3" value="{{ $settings->weekly_exercise_sessions }}"><div class="form-text">How many exercise sessions you aim for each week.</div></div></div>
</div>

<div class="card"><h5>Work</h5><p class="text-muted-x">Your office schedule. It appears on your Dashboard timeline, and overnight shifts (ending after midnight) are handled correctly.</p>
<div class="row"><div class="col-12 col-md-4 mb-3"><label class="form-label" for="office_start">Office start</label><input type="time" id="office_start" name="office_start" class="form-control" value="{{ $settings->office_start }}"><div class="form-text">Your normal shift begins at this time (default 5:00 PM).</div></div>
<div class="col-12 col-md-4 mb-3"><label class="form-label" for="office_end">Office end</label><input type="time" id="office_end" name="office_end" class="form-control" value="{{ $settings->office_end }}"><div class="form-text">An end time after midnight, like 2:00 AM, is fine.</div></div>
<div class="col-12 col-md-4 mb-3"><label class="form-label" for="monthly_salary">Monthly salary (PKR)</label><input type="number" step="0.01" id="monthly_salary" name="monthly_salary" class="form-control" placeholder="e.g. 50000" value="{{ $settings->monthly_salary }}"></div></div>
<div class="mb-1"><span class="form-label">Working days</span><div class="d-flex flex-wrap gap-3 mt-1">@foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $iso=>$dname)<label class="form-check"><input type="checkbox" class="form-check-input" name="working_days[]" value="{{ $iso }}" @checked($wdStored->contains($iso))> {{ $dname }}</label>@endforeach</div><div class="form-text">The days you normally work. Your office block appears on your timeline only on these days, and your routine uses this to tell workdays from days off.</div></div>
</div>

<div class="card"><h5>Appearance</h5><p class="text-muted-x">How the app looks. Your choice is saved to your account and applies on every device, immediately after saving.</p>
<div class="row g-3 mb-3">
  <div class="col-12 col-md-4"><label class="theme-option d-block"><input type="radio" name="theme" value="dark" @checked(($settings->theme ?? 'dark')==='dark')> <strong>Dark</strong><div class="text-muted-x small">Easy on the eyes at night. The original My Daily OS look.</div></label></div>
  <div class="col-12 col-md-4"><label class="theme-option d-block"><input type="radio" name="theme" value="light" @checked(($settings->theme ?? '')==='light')> <strong>Light</strong><div class="text-muted-x small">Bright and clear, better in daylight or bright rooms.</div></label></div>
  <div class="col-12 col-md-4"><label class="theme-option d-block"><input type="radio" name="theme" value="system" @checked(($settings->theme ?? '')==='system')> <strong>System</strong><div class="text-muted-x small">Follows your phone or computer setting — dark when your device is dark, light when it is light.</div></label></div>
</div>
<div class="mb-3"><label class="form-label" for="accent_color">Accent color</label>
<div class="d-flex flex-wrap align-items-center gap-2 mb-2">@foreach($accents as $hex=>$name)<button type="button" class="swatch {{ ($settings->accent_color ?? '#8BCB9B')===$hex ? 'selected' : '' }}" data-accent-swatch="{{ $hex }}" style="background:{{ $hex }}" title="{{ $name }}" aria-label="{{ $name }}"></button>@endforeach
<input type="color" id="accent_color" name="accent_color" class="form-control" style="max-width:90px;padding:4px" value="{{ $settings->accent_color ?? '#8BCB9B' }}"></div>
<div class="form-text">Tap a circle to pick a ready color, or use the color box for any custom color. Buttons, links, graphs and progress bars use this color.</div></div>
</div>

<div class="card"><h5>Notifications</h5><p class="text-muted-x">The master switch for reminders. Reminder types, times and phone push are managed on the Notifications page.</p>
<div class="row"><div class="col-12 col-md-4 mb-3"><label class="form-label" for="notifications_enabled">Notifications</label><select id="notifications_enabled" name="notifications_enabled" class="form-select"><option value="1" @selected($settings->notifications_enabled ?? true)>Enabled</option><option value="0" @selected(!($settings->notifications_enabled ?? true))>Disabled</option></select></div>
<div class="col-12 col-md-4 mb-3"><label class="form-label" for="quiet_start">Quiet hours start</label><input type="time" id="quiet_start" name="quiet_start" class="form-control" value="{{ $settings->quiet_start }}"><div class="form-text">Optional — reminders stay silent from this time…</div></div>
<div class="col-12 col-md-4 mb-3"><label class="form-label" for="quiet_end">Quiet hours end</label><input type="time" id="quiet_end" name="quiet_end" class="form-control" value="{{ $settings->quiet_end }}"><div class="form-text">…until this time. Leave both empty for no quiet hours.</div></div></div>
<p class="mb-0"><a href="{{ route('notifications.index') }}">Open Notifications settings</a></p>
</div>

<button class="btn btn-accent btn-lg">Save Settings</button>
</form>

<div class="card mt-3"><a href="{{ route('profile.edit') }}">Profile &amp; Password</a> · <a href="{{ route('salah.settings') }}">Salah Settings</a> · <a href="{{ route('notifications.index') }}">Notifications</a> · <a href="{{ route('export.index') }}">Export &amp; Backup</a></div>
@endsection
