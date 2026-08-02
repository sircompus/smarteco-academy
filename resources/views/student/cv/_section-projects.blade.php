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
