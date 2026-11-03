@extends('layouts.guest')
@section('content')
<p>Thanks for signing up. Please verify your email if a verification link was sent — open the email and tap the link, then come back here. </p><p class="text-muted-x">Did not get it? Check your spam folder, or resend it below.</p>@if(session('status')=='verification-link-sent')<div class="alert alert-success">A new verification link has been sent.</div>@endif<form method="POST" action="{{ route('verification.send') }}">@csrf<button class="btn btn-accent">Resend verification email</button></form><form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf<button class="btn btn-outline-light btn-sm">Logout</button></form>
@endsection
