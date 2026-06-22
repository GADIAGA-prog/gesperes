<x-guest-layout>
    <h2 class="text-xl font-semibold text-gray-800 mb-1">Mot de passe oublié</h2>
    <p class="text-sm text-gray-500 mb-6">Indiquez votre e-mail pour recevoir un lien de réinitialisation.</p>
    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-700">{{ session('status') }}</div>
    @endif
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="label">Adresse e-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="input">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-full justify-center">Envoyer le lien</button>
        <a href="{{ route('login') }}" class="block text-center text-sm text-gray-500 hover:underline">Retour à la connexion</a>
    </form>
</x-guest-layout>
