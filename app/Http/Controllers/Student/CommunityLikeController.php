<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommunityLikeController extends Controller
{
    public function __invoke(
        Request $request,
        CommunityPost $communityPost
    ): RedirectResponse {
        abort_unless(
            $communityPost->status === 'published',
            404
        );

        $existingLike = $communityPost->likes()
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingLike !== null) {
            $existingLike->delete();

            return back()->with(
                'success',
                'Votre mention J’aime a été retirée.'
            );
        }

        $communityPost->likes()->create([
            'user_id' => $request->user()->id,
        ]);

        return back()->with(
            'success',
            'Vous aimez cette publication.'
        );
    }
}
