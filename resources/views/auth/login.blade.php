<x-guest-layout>
    <h2 class="text-xl font-semibold text-gray-800 mb-1">Connexion</h2>
    <p class="text-sm text-gray-500 mb-6">Accédez à votre espace de gestion</p>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="label">Adresse e-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="input">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="label">Mot de passe</label>
            <input id="password" type="password" name="password" required class="input">
            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-institution-600">
                Se souvenir de moi
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-institution-600 hover:underline">Mot de passe oublié ?</a>
            @endif
        </div>
        <button type="submit" class="btn btn-primary w-full justify-center">Se connecter</button>
    </form>

    <div class="mt-6 border-t border-gray-100 pt-4 text-center text-sm text-gray-500">
        Vous êtes un agent du ministère ?
        <a href="{{ route('espace-agent.connexion') }}" class="text-institution-600 hover:underline">Accéder à l'espace agent</a>
    </div>
</x-guest-layout>
