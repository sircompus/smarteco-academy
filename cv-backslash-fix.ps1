$path0 = "C:\laragon\www\SEA\resources\views\student\cv\_section-educations.blade.php"
$content0 = @'
<section id="educations" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Formation</h2>

    <div class="mt-4 space-y-4">
        @foreach ($profile->educations as $education)
            <form
                method="POST"
                action="{{ route("{$routePrefix}.educations.update", $education) }}"
                class="grid gap-3 rounded-xl border border-gray-100 p-4 md:grid-cols-2"
            >
                @csrf
                @method('PATCH')

                <input name="institution" value="{{ $education->institution }}" placeholder="Établissement" class="rounded-lg border-gray-300" required>
                <input name="degree" value="{{ $education->degree }}" placeholder="Diplôme" class="rounded-lg border-gray-300">
                <input name="field_of_study" value="{{ $education->field_of_study }}" placeholder="Domaine" class="rounded-lg border-gray-300">

                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" value="{{ $education->start_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                    <input type="date" name="end_date" value="{{ $education->end_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                </div>

                <textarea name="description" rows="2" placeholder="Description (optionnel)" class="rounded-lg border-gray-300 md:col-span-2">{{ $education->description }}</textarea>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_current" value="1" @checked($education->is_current)>
                    En cours
                </label>

                <div class="flex items-center gap-2 md:col-span-2">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Enregistrer</button>

                    <button
                        type="button"
                        onclick="if(confirm('Supprimer cette formation ?')) document.getElementById('del-edu-{{ $education->id }}').submit();"
                        class="rounded-lg bg-red-50 px-4 py-2 text-xs font-semibold text-red-600"
                    >
                        Supprimer
                    </button>
                </div>
            </form>

            <form id="del-edu-{{ $education->id }}" method="POST" action="{{ route("{$routePrefix}.educations.destroy", $education) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endforeach
    </div>

    <form method="POST" action="{{ route("{$routePrefix}.educations.store", $storeParams) }}" class="mt-4 grid gap-3 rounded-xl border border-dashed border-gray-300 p-4 md:grid-cols-2">
        @csrf
        <input name="institution" placeholder="Établissement" class="rounded-lg border-gray-300" required>
        <input name="degree" placeholder="Diplôme" class="rounded-lg border-gray-300">
        <input name="field_of_study" placeholder="Domaine" class="rounded-lg border-gray-300">

        <div class="grid grid-cols-2 gap-2">
            <input type="date" name="start_date" class="rounded-lg border-gray-300">
            <input type="date" name="end_date" class="rounded-lg border-gray-300">
        </div>

        <textarea name="description" rows="2" placeholder="Description (optionnel)" class="rounded-lg border-gray-300 md:col-span-2"></textarea>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_current" value="1">
            En cours
        </label>

        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white md:col-span-2">
            + Ajouter une formation
        </button>
    </form>
</section>

'@
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-educations.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-educations.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\resources\views\student\cv\_section-experiences.blade.php"
$content1 = @'
<section id="experiences" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Expérience professionnelle</h2>

    <div class="mt-4 space-y-4">
        @foreach ($profile->experiences as $experience)
            <form
                method="POST"
                action="{{ route("{$routePrefix}.experiences.update", $experience) }}"
                class="grid gap-3 rounded-xl border border-gray-100 p-4 md:grid-cols-2"
            >
                @csrf
                @method('PATCH')

                <input name="company" value="{{ $experience->company }}" placeholder="Entreprise" class="rounded-lg border-gray-300" required>
                <input name="position" value="{{ $experience->position }}" placeholder="Poste" class="rounded-lg border-gray-300" required>
                <input name="location" value="{{ $experience->location }}" placeholder="Lieu" class="rounded-lg border-gray-300">

                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" value="{{ $experience->start_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                    <input type="date" name="end_date" value="{{ $experience->end_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                </div>

                <textarea name="description" rows="3" placeholder="Missions, réalisations..." class="rounded-lg border-gray-300 md:col-span-2">{{ $experience->description }}</textarea>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_current" value="1" @checked($experience->is_current)>
                    Poste actuel
                </label>

                <div class="flex items-center gap-2 md:col-span-2">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Enregistrer</button>

                    <button
                        type="button"
                        onclick="if(confirm('Supprimer cette expérience ?')) document.getElementById('del-exp-{{ $experience->id }}').submit();"
                        class="rounded-lg bg-red-50 px-4 py-2 text-xs font-semibold text-red-600"
                    >
                        Supprimer
                    </button>
                </div>
            </form>

            <form id="del-exp-{{ $experience->id }}" method="POST" action="{{ route("{$routePrefix}.experiences.destroy", $experience) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endforeach
    </div>

    <form method="POST" action="{{ route("{$routePrefix}.experiences.store", $storeParams) }}" class="mt-4 grid gap-3 rounded-xl border border-dashed border-gray-300 p-4 md:grid-cols-2">
        @csrf
        <input name="company" placeholder="Entreprise" class="rounded-lg border-gray-300" required>
        <input name="position" placeholder="Poste" class="rounded-lg border-gray-300" required>
        <input name="location" placeholder="Lieu" class="rounded-lg border-gray-300">

        <div class="grid grid-cols-2 gap-2">
            <input type="date" name="start_date" class="rounded-lg border-gray-300">
            <input type="date" name="end_date" class="rounded-lg border-gray-300">
        </div>

        <textarea name="description" rows="3" placeholder="Missions, réalisations..." class="rounded-lg border-gray-300 md:col-span-2"></textarea>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_current" value="1">
            Poste actuel
        </label>

        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white md:col-span-2">
            + Ajouter une expérience
        </button>
    </form>
</section>

'@
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-experiences.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-experiences.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\resources\views\student\cv\_section-certifications.blade.php"
$content2 = @'
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
                <form method="POST" action="{{ route("{$routePrefix}.certifications.destroy", $certification) }}">
                    @csrf @method('DELETE')
                    <button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600">Supprimer</button>
                </form>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route("{$routePrefix}.certifications.store", $storeParams) }}" class="mt-4 grid gap-3 rounded-xl border border-dashed border-gray-300 p-4 md:grid-cols-2">
        @csrf
        <input name="name" placeholder="Nom de la certification" class="rounded-lg border-gray-300" required>
        <input name="issuer" placeholder="Organisme" class="rounded-lg border-gray-300">
        <input type="date" name="date_obtained" class="rounded-lg border-gray-300">
        <input name="credential_url" placeholder="Lien (optionnel)" class="rounded-lg border-gray-300">
        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white md:col-span-2">+ Ajouter</button>
    </form>
</section>

'@
try {
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-certifications.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-certifications.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path3 = "C:\laragon\www\SEA\resources\views\student\cv\_section-projects.blade.php"
$content3 = @'
<section id="projects" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Projets (Portfolio)</h2>
    <p class="mt-1 text-sm text-gray-500">Ces projets apparaissent sur ton portfolio public.</p>

    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @foreach ($profile->projects as $project)
            <form
                method="POST"
                action="{{ route("{$routePrefix}.projects.update", $project) }}"
                enctype="multipart/form-data"
                class="space-y-2 rounded-xl border border-gray-100 p-4"
            >
                @csrf
                @method('PATCH')

                @if ($project->image_url)
                    <img src="{{ $project->image_url }}" class="h-32 w-full rounded-lg object-cover">
                @endif

                <input type="file" name="image" accept="image/*" class="w-full text-xs">
                <input name="title" value="{{ $project->title }}" placeholder="Titre du projet" class="w-full rounded-lg border-gray-300" required>
                <textarea name="description" rows="2" placeholder="Description" class="w-full rounded-lg border-gray-300">{{ $project->description }}</textarea>
                <input name="tags" value="{{ $project->tags }}" placeholder="Tags séparés par virgule" class="w-full rounded-lg border-gray-300">
                <input name="project_url" value="{{ $project->project_url }}" placeholder="Lien du projet" class="w-full rounded-lg border-gray-300">
                <input name="repo_url" value="{{ $project->repo_url }}" placeholder="Lien du code (optionnel)" class="w-full rounded-lg border-gray-300">

                <div class="flex items-center gap-2 pt-2">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Enregistrer</button>
                    <button
                        type="button"
                        onclick="if(confirm('Supprimer ce projet ?')) document.getElementById('del-proj-{{ $project->id }}').submit();"
                        class="rounded-lg bg-red-50 px-4 py-2 text-xs font-semibold text-red-600"
                    >
                        Supprimer
                    </button>
                </div>
            </form>

            <form id="del-proj-{{ $project->id }}" method="POST" action="{{ route("{$routePrefix}.projects.destroy", $project) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endforeach
    </div>

    <form method="POST" action="{{ route("{$routePrefix}.projects.store", $storeParams) }}" enctype="multipart/form-data" class="mt-4 space-y-2 rounded-xl border border-dashed border-gray-300 p-4">
        @csrf
        <input type="file" name="image" accept="image/*" class="w-full text-xs">
        <input name="title" placeholder="Titre du projet" class="w-full rounded-lg border-gray-300" required>
        <textarea name="description" rows="2" placeholder="Description" class="w-full rounded-lg border-gray-300"></textarea>
        <input name="tags" placeholder="Tags séparés par virgule (ex : Excel, Marketing)" class="w-full rounded-lg border-gray-300">
        <input name="project_url" placeholder="Lien du projet" class="w-full rounded-lg border-gray-300">
        <input name="repo_url" placeholder="Lien du code (optionnel)" class="w-full rounded-lg border-gray-300">
        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white">+ Ajouter un projet</button>
    </form>
</section>

'@
try {
    [System.IO.File]::WriteAllText($path3, $content3, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-projects.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-projects.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine. Verifie qu il n y a AUCUNE ligne ECHEC rouge ci-dessus." -ForegroundColor Cyan
