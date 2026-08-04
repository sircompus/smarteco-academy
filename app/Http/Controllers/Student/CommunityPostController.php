<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\StoreCommunityPostRequest;
use App\Http\Requests\Community\UpdateCommunityPostRequest;
use App\Models\CommunityPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CommunityPostController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', CommunityPost::class);

        $userId = (int) $request->user()->id;
        $isAdmin = $request->user()->hasRole('admin');

        $postsQuery = CommunityPost::query();

        if (! $isAdmin) {
            $postsQuery->published();
        }

        $posts = $postsQuery
            ->with([
                'author',
                'comments' => function ($query) use ($isAdmin): void {
                    if (! $isAdmin) {
                        $query->where('status', 'published');
                    }

                    $query
                        ->with('author')
                        ->oldest();
                },
                'likes' => function ($query) use ($userId): void {
                    $query->where('user_id', $userId);
                },
            ])
            ->withCount([
                'comments as comments_count' => function ($query): void {
                    $query->where('status', 'published');
                },
                'likes',
            ])
            ->latest()
            ->paginate(10);

        return view('student.community.index', [
            'posts' => $posts,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function store(
        StoreCommunityPostRequest $request
    ): RedirectResponse {
        Gate::authorize('create', CommunityPost::class);

        CommunityPost::query()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
            'status' => 'published',
        ]);

        return redirect()
            ->route('student.community.index')
            ->with(
                'success',
                'Votre publication a été ajoutée.'
            );
    }

    public function edit(
        CommunityPost $communityPost
    ): View {
        Gate::authorize('update', $communityPost);

        return view('student.community.edit', [
            'communityPost' => $communityPost,
        ]);
    }

    public function update(
        UpdateCommunityPostRequest $request,
        CommunityPost $communityPost
    ): RedirectResponse {
        Gate::authorize('update', $communityPost);

        $communityPost->update([
            'body' => $request->validated('body'),
        ]);

        return redirect()
            ->route('student.community.index')
            ->with(
                'success',
                'Votre publication a été modifiée.'
            );
    }

    public function destroy(
        CommunityPost $communityPost
    ): RedirectResponse {
        Gate::authorize('delete', $communityPost);

        $communityPost->delete();

        return redirect()
            ->route('student.community.index')
            ->with(
                'success',
                'Votre publication a été supprimée.'
            );
    }
}
