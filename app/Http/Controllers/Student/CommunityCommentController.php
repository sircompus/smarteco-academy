<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\StoreCommunityCommentRequest;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CommunityCommentController extends Controller
{
    public function store(
        StoreCommunityCommentRequest $request,
        CommunityPost $communityPost
    ): RedirectResponse {
        abort_unless(
            $communityPost->status === 'published',
            404
        );

        Gate::authorize('create', CommunityComment::class);

        $communityPost->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
            'status' => 'published',
        ]);

        return back()->with(
            'success',
            'Votre commentaire a été ajouté.'
        );
    }

    public function destroy(
        CommunityPost $communityPost,
        CommunityComment $communityComment
    ): RedirectResponse {
        abort_unless(
            (int) $communityComment->community_post_id
                === (int) $communityPost->id,
            404
        );

        Gate::authorize('delete', $communityComment);

        $communityComment->delete();

        return back()->with(
            'success',
            'Votre commentaire a été supprimé.'
        );
    }
}
