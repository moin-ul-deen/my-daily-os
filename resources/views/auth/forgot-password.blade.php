@extends('layouts.guest')
@section('content')
<p class="text-muted-x">Forgot your password? Enter the email you registered with and we will send you a link to set a new one.</p>@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif<form method="POST" action="{{ route('password.email') }}">@csrf<div class="mb-3"><label class="form-label" for="email">Email</label><input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="{{ old('email') }}" required></div><button class="btn btn-accent w-100">Send reset link</button><div class="mt-3 text-center"><a href="{{ route('login') }}">Back to login</a></div></form>
@endsection
