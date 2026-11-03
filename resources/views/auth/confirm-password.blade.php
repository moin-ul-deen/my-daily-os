@extends('layouts.guest')
@section('content')
<p class="text-muted-x">For your security, please confirm your password before continuing to this protected page.</p>
<form method="POST" action="{{ route('password.confirm') }}">@csrf<div class="mb-3"><label class="form-label" for="password">Password</label><input type="password" id="password" name="password" class="form-control" placeholder="Your password" required></div><button class="btn btn-accent">Confirm</button></form>
@endsection
