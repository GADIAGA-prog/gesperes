@if (auth()->check())
    <meta http-equiv="refresh" content="0;url={{ route('dashboard') }}">
@else
    <meta http-equiv="refresh" content="0;url={{ route('login') }}">
@endif
