@extends('layouts.student')

@section('title', 'Community')
@section('page-title', 'Community')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">
                Fil de la communauté
            </h2>

            <p class="mt-1 text-gray-600">
                Partagez une information, une question ou une expérience.
            </p>

            @if ($isAdmin)
                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    Mode modération administrateur activé. Les contenus
                    masqués restent visibles pour permettre leur restauration.
                </div>
            @endif
        </div>

        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <form
                method="POST"
                action="{{ route('student.community.posts.store') }}"
            >
                @csrf

                <label
                    for="post-body"
                    class="block text-sm font-semibold text-gray-700"
                >
                    Nouvelle publication
                </label>

                <textarea
                    id="post-body"
                    name="body"
                    rows="4"
                    maxlength="5000"
                    required
                    placeholder="Écrivez votre publication…"
                    class="mt-3 block w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500"
                >{{ old('body') }}</textarea>

                @error('body')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

                <div class="mt-4 flex justify-end">
                    <button
                        type="submit"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
                    >
                        Publier
                    </button>
                </div>
            </form>
        </section>

        <section class="mt-6 space-y-4">
            @forelse ($posts as $post)
                <article
                    id="post-{{ $post->id }}"
                    class="{{ $post->status === 'hidden'
                        ? 'border-2 border-amber-300 bg-amber-50'
                        : 'bg-white' }}
                        rounded-2xl p-5 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-gray-900">
                                    {{ $post->author->name }}
                                </p>

                                @if ($post->status === 'hidden')
                                    <span class="rounded-full bg-amber-200 px-2.5 py-1 text-xs font-semibold text-amber-900">
                                        Masquée
                                    </span>
                                @endif
                            </div>

                            <p class="mt-1 text-xs text-gray-500">
                                {{ $post->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            @can('update', $post)
                                <a
                                    href="{{ route(
                                        'student.community.posts.edit',
                                        $post
                                    ) }}"
                                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Modifier
                                </a>
                            @endcan

                            @can('delete', $post)
                                <form
                                    method="POST"
                                    action="{{ route(
                                        'student.community.posts.destroy',
                                        $post
                                    ) }}"
                                    onsubmit="return confirm(
                                        'Supprimer cette publication ?'
                                    );"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50"
                                    >
                                        Supprimer
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    <div class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-gray-800">
                        {{ $post->body }}
                    </div>

                    @if ($isAdmin)
                        <form
                            method="POST"
                            action="{{ route(
                                'student.community.moderation.posts',
                                $post
                            ) }}"
                            class="mt-4 rounded-xl border border-amber-200 bg-white/70 p-3"
                        >
                            @csrf
                            @method('PATCH')

                            <input
                                type="hidden"
                                name="action"
                                value="{{ $post->status === 'hidden'
                                    ? 'restore'
                                    : 'hide' }}"
                            >

                            @if ($post->status !== 'hidden')
                                <input
                                    type="text"
                                    name="moderation_note"
                                    maxlength="1000"
                                    placeholder="Motif facultatif"
                                    class="mb-2 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                                >
                            @endif

                            <button
                                type="submit"
                                class="rounded-lg border border-amber-400 px-3 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-100"
                            >
                                {{ $post->status === 'hidden'
                                    ? 'Restaurer la publication'
                                    : 'Masquer la publication' }}
                            </button>
                        </form>
                    @endif

                    @if ($post->status === 'published')
                        <div class="mt-5 flex flex-wrap items-center gap-4 border-t border-gray-100 pt-4">
                            <form
                                method="POST"
                                action="{{ route(
                                    'student.community.likes.toggle',
                                    $post
                                ) }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="{{ $post->likes->isNotEmpty()
                                        ? 'border-indigo-300 bg-indigo-50 text-indigo-700'
                                        : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}
                                        rounded-lg border px-3 py-2 text-sm font-medium"
                                >
                                    {{ $post->likes->isNotEmpty()
                                        ? 'Je n’aime plus'
                                        : 'J’aime' }}
                                </button>
                            </form>

                            <span class="text-sm text-gray-500">
                                {{ $post->likes_count }} J’aime
                            </span>

                            <span class="text-sm text-gray-500">
                                {{ $post->comments_count }} commentaire(s)
                            </span>
                        </div>
                    @endif

                    <div class="mt-5 space-y-3">
                        @foreach ($post->comments as $comment)
                            <div class="{{ $comment->status === 'hidden'
                                ? 'border border-amber-300 bg-amber-50'
                                : 'bg-gray-50' }}
                                rounded-xl p-4"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ $comment->author->name }}
                                            </p>

                                            @if ($comment->status === 'hidden')
                                                <span class="rounded-full bg-amber-200 px-2 py-0.5 text-xs font-semibold text-amber-900">
                                                    Masqué
                                                </span>
                                            @endif
                                        </div>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $comment->created_at->diffForHumans() }}
                                        </p>
                                    </div>

                                    @can('delete', $comment)
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'student.community.comments.destroy',
                                                [$post, $comment]
                                            ) }}"
                                            onsubmit="return confirm(
                                                'Supprimer ce commentaire ?'
                                            );"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="text-xs font-medium text-red-600 hover:underline"
                                            >
                                                Supprimer
                                            </button>
                                        </form>
                                    @endcan
                                </div>

                                <p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-gray-700">
                                    {{ $comment->body }}
                                </p>

                                @if ($isAdmin)
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'student.community.moderation.comments',
                                            $comment
                                        ) }}"
                                        class="mt-3"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="{{ $comment->status === 'hidden'
                                                ? 'restore'
                                                : 'hide' }}"
                                        >

                                        <button
                                            type="submit"
                                            class="text-xs font-semibold text-amber-700 hover:underline"
                                        >
                                            {{ $comment->status === 'hidden'
                                                ? 'Restaurer le commentaire'
                                                : 'Masquer le commentaire' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if ($post->status === 'published')
                        <form
                            method="POST"
                            action="{{ route(
                                'student.community.comments.store',
                                $post
                            ) }}"
                            class="mt-4 flex gap-2"
                        >
                            @csrf

                            <label
                                for="comment-body-{{ $post->id }}"
                                class="sr-only"
                            >
                                Ajouter un commentaire
                            </label>

                            <input
                                id="comment-body-{{ $post->id }}"
                                name="body"
                                type="text"
                                maxlength="2000"
                                required
                                placeholder="Ajouter un commentaire…"
                                class="min-w-0 flex-1 rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <button
                                type="submit"
                                class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-700"
                            >
                                Commenter
                            </button>
                        </form>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl bg-white p-8 text-center shadow-sm">
                    <p class="text-gray-600">
                        Aucune publication pour le moment.
                    </p>

                    <p class="mt-2 text-sm text-gray-500">
                        Soyez la première personne à publier.
                    </p>
                </div>
            @endforelse
        </section>

        @if ($posts->hasPages())
            <div class="mt-6">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
