<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RegistrationDocumentController extends Controller
{
    public function store(
        Request $request,
        Registration $registration
    ): RedirectResponse {
        abort_unless(
            $registration->user_id === $request->user()->id,
            403
        );

        abort_unless($registration->canBeEdited(), 403);

        $data = $request->validate([
            'type' => [
                'required',
                'in:identity,diploma,transcript,photo,other',
            ],
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ]);

        $file = $request->file('file');

        $existingDocument = $registration
            ->documents()
            ->where('type', $data['type'])
            ->first();

        if ($existingDocument) {
            Storage::disk($existingDocument->disk)
                ->delete($existingDocument->path);
        }

        $path = $file->store(
            'registrations/' . $registration->uuid,
            'local'
        );

        RegistrationDocument::updateOrCreate(
            [
                'registration_id' => $registration->id,
                'type' => $data['type'],
            ],
            [
                'uploaded_by' => $request->user()->id,
                'title' => $data['title'] ?? null,
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'is_verified' => false,
                'verified_at' => null,
                'verified_by' => null,
                'admin_note' => null,
            ]
        );

        return back()->with(
            'success',
            'Le document a été enregistré.'
        );
    }

    public function destroy(
        Request $request,
        RegistrationDocument $document
    ): RedirectResponse {
        abort_unless(
            $document->registration->user_id === $request->user()->id,
            403
        );

        abort_unless(
            $document->registration->canBeEdited(),
            403
        );

        Storage::disk($document->disk)
            ->delete($document->path);

        $document->delete();

        return back()->with(
            'success',
            'Le document a été supprimé.'
        );
    }

    public function download(
        Request $request,
        RegistrationDocument $document
    ) {
        $isOwner =
            $document->registration->user_id ===
            $request->user()->id;

        $isAdmin = $request->user()->hasRole('admin');

        abort_unless($isOwner || $isAdmin, 403);

        abort_unless(
            Storage::disk($document->disk)
                ->exists($document->path),
            404
        );

        return Storage::disk($document->disk)->download(
            $document->path,
            $document->original_name
        );
    }
}