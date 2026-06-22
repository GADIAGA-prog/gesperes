<x-guest-layout>
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Réinitialiser le mot de passe</h2>
    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <label for="email" class="label">Adresse e-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus class="input">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="label">Nouveau mot de passe</label>
            <input id="password" type="password" name="password" required class="input">
            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="label">Confirmation</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required class="input">
        </div>
        <button type="submit" class="btn btn-primary w-full justify-center">Réinitialiser</button>
    </form>
</x-guest-layout>
