<section id="certifications" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Certifications</h2>

    <div class="mt-4 space-y-2">
        @foreach ($profile->certifications as $certification)
            <div class="flex items-center justify-between rounded-xl border border-gray-100 p-3">
                <div>
                    <p class="text-sm font-medium">{{ $certification->name }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $certification->issuer }}
                        @if ($certification->date_obtained)
                            · {{ $certification->date_obtained->format('m/Y') }}
                        @endif
                    </p>
                </div>
                <form method="POST" action="{{ route('student.cv.certifications.destroy', $certification) }}">
                    @csrf @method('DELETE')
                    <button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600">Supprimer</button>
                </form>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('student.cv.certifications.store') }}" class="mt-4 grid gap-3 rounded-xl border border-dashed border-gray-300 p-4 md:grid-cols-2">
        @csrf
        <input name="name" placeholder="Nom de la certification" class="rounded-lg border-gray-300" required>
        <input name="issuer" placeholder="Organisme" class="rounded-lg border-gray-300">
        <input type="date" name="date_obtained" class="rounded-lg border-gray-300">
        <input name="credential_url" placeholder="Lien (optionnel)" class="rounded-lg border-gray-300">
        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white md:col-span-2">+ Ajouter</button>
    </form>
</section>
