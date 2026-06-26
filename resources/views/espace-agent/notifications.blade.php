@extends('layouts.agent')
@section('title', 'Notifications')
@section('header', 'Notifications')
@section('sous-titre', $notifications->total() . ' notification(s)')

@section('content')
@if ($notifications->total() > 0)
    <div class="mb-4 flex justify-end">
        <form method="POST" action="{{ route('espace-agent.notifications.lues') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Tout marquer comme lu
            </button>
        </form>
    </div>
@endif

@if ($notifications->isEmpty())
    <div class="rounded-2xl bg-white py-16 text-center shadow-sm ring-1 ring-slate-200/70">
        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
        <p class="mt-3 text-sm font-medium text-slate-600">Aucune notification</p>
        <p class="mt-1 text-sm text-slate-400">Vous serez alerté ici des actes vous concernant.</p>
    </div>
@else
    <div class="space-y-2.5">
        @foreach ($notifications as $notif)
            @php
                $style = match ($notif->niveau) {
                    'danger'  => ['ring' => 'ring-red-200',   'ico' => 'bg-red-50 text-red-600'],
                    'warning' => ['ring' => 'ring-amber-200', 'ico' => 'bg-amber-50 text-amber-600'],
                    default   => ['ring' => 'ring-institution-100', 'ico' => 'bg-institution-50 text-institution-700'],
                };
            @endphp
            <div class="relative flex items-start gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 {{ $notif->lu ? 'ring-slate-200/70' : $style['ring'] }}">
                @unless ($notif->lu)<span class="absolute right-4 top-4 h-2 w-2 rounded-full bg-institution-600"></span>@endunless
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $notif->lu ? 'bg-slate-100 text-slate-400' : $style['ico'] }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="pr-4 text-sm font-semibold {{ $notif->lu ? 'text-slate-600' : 'text-slate-800' }}">{{ $notif->titre }}</p>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $notif->message }}</p>
                    <div class="mt-2 flex items-center gap-3">
                        <span class="text-xs text-slate-400">{{ $notif->created_at?->translatedFormat('d M Y · H:i') }}</span>
                        @unless ($notif->lu)
                            <form method="POST" action="{{ route('espace-agent.notifications.lue', $notif) }}">
                                @csrf
                                <button type="submit" class="text-xs font-medium text-institution-600 hover:underline">Marquer comme lu</button>
                            </form>
                        @endunless
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-5">{{ $notifications->links() }}</div>
@endif
@endsection
