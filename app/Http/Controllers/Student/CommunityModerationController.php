<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommunityModerationController extends Controller
{
    public function post(
        Request $request,
        CommunityPost $communityPost
    ): RedirectResponse {
        $this->authorizeAdministrator($request);

        $validated = $this->validatedAction($request);

        $this->applyModeration(
            $communityPost,
            $validated['action'],
            $request,
            $validated['moderation_note'] ?? null
        );

        return back()->with(
            'success',
            $validated['action'] === 'hide'
                ? 'La publication a été masquée.'
                : 'La publication a été restaurée.'
        );
    }

    public function comment(
        Request $request,
        CommunityComment $communityComment
    ): RedirectResponse {
        $this->authorizeAdministrator($request);

        $validated = $this->validatedAction($request);

        $this->applyModeration(
            $communityComment,
            $validated['action'],
            $request,
            $validated['moderation_note'] ?? null
        );

        return back()->with(
            'success',
            $validated['action'] === 'hide'
                ? 'Le commentaire a été masqué.'
                : 'Le commentaire a été restauré.'
        );
    }

    private function authorizeAdministrator(Request $request): void
    {
        abort_unless(
            $request->user()?->hasRole('admin') === true,
            403
        );
    }

    private function validatedAction(Request $request): array
    {
        return $request->validate([
            'action' => [
                'required',
                Rule::in(['hide', 'restore']),
            ],
            'moderation_note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);
    }

    private function applyModeration(
        CommunityPost|CommunityComment $content,
        string $action,
        Request $request,
        ?string $note
    ): void {
        if ($action === 'hide') {
            $content->update([
                'status' => 'hidden',
                'hidden_by' => $request->user()->id,
                'hidden_at' => now(),
                'moderation_note' => $note,
            ]);

            return;
        }

        $content->update([
            'status' => 'published',
            'hidden_by' => null,
            'hidden_at' => null,
            'moderation_note' => null,
        ]);
    }
}
