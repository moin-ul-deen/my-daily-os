@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Notifications @if($unread) <span class="badge bg-danger">{{ $unread }} unread</span>@endif</h3>
  @if($unread)
  <form method="POST" action="{{ route('notifications.readAll') }}">@csrf<button class="btn btn-sm btn-accent">Mark all read</button></form>
  @endif
</div>

@if($items->count())
  @foreach($items as $n)
  <div class="card mb-2 {{ $n->read_at ? '' : 'notif-unread' }}">
    <div class="d-flex justify-content-between">
      <div>
        <strong>{{ $n->title ?? ucfirst($n->type).' reminder' }}</strong>
        @unless($n->read_at) <span class="badge bg-warning text-dark">New</span>@endunless
        <div>{{ $n->body }}</div>
        <small class="text-muted-x">{{ $n->sent_at?->format('d M Y, h:i A') }} · {{ $n->type }}</small>
      </div>
      @unless($n->read_at)
      <form method="POST" action="{{ route('notifications.read', $n) }}">@csrf<button class="btn btn-sm btn-outline-light">Mark read</button></form>
      @endunless
    </div>
  </div>
  @endforeach
  {{ $items->links() }}
@else
<div class="card">
  <h5>No notifications yet</h5>
  <p class="mb-0">This is your notification inbox. When a reminder fires — for example a prayer reminder, a task reminder, or one of your custom reminders — it appears here, even if you were not looking at the app at that moment. You can set reminder times in <a href="{{ route('salah.settings') }}">Salah Settings</a> and <a href="{{ route('notifications.index') }}">Settings → Notifications</a>. Want to check right now? Open Settings → Notifications and tap <strong>Send test notification</strong>.</p>
</div>
@endif
@endsection
