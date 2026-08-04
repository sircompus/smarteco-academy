@extends('layouts.student')

@section('title', 'Modifier une publication')
@section('page-title', 'Modifier une publication')

@section('content')
    <div class="mx-auto max-w-3xl">
        <a
            href="{{ route('student.community.index') }}"
            class="text-sm font-medium text-indigo-700 hover:underline"
        >
            ← Retour à la communauté
        </a>

        <section class="mt-5 rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900">
                Modifier la publication
            </h2>

            <form
                method="POST"
                action="{{ route(
                    'student.community.posts.update',
                    $communityPost
                ) }}"
                class="mt-5"
            >
                @csrf
                @method('PATCH')

                <label
                    for="body"
                    class="block text-sm font-semibold text-gray-700"
                >
                    Contenu
                </label>

                <textarea
                    id="body"
                    name="body"
                    rows="8"
                    maxlength="5000"
                    required
                    class="mt-3 block w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500"
                >{{ old('body', $communityPost->body) }}</textarea>

                @error('body')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

                <div class="mt-5 flex justify-end gap-3">
                    <a
                        href="{{ route('student.community.index') }}"
                        class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Annuler
                    </a>

                    <button
                        type="submit"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"
                    >
                        Enregistrer
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
