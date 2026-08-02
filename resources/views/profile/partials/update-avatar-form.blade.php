<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Photo de profil
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Cette photo apparaît dans le menu, en haut de ton espace.
        </p>
    </header>

    @if (session('status') === 'avatar-updated')
        <p class="mt-2 text-sm text-green-600">Photo mise à jour.</p>
    @endif

    <div class="mt-4 flex items-center gap-6">
        @if ($profile?->avatar_path)
            <img
                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($profile->avatar_path) }}"
                class="h-20 w-20 rounded-full object-cover"
                alt="Photo de profil"
            >
        @else
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-100 text-xl font-bold text-indigo-700">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="flex items-center gap-3">
            @csrf
            @method('PATCH')

            <input type="file" name="avatar" accept="image/*" required class="text-sm">

            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                Mettre à jour
            </button>
        </form>
    </div>

    @error('avatar')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</section>
