<?php

namespace App\Http\Controllers;

use App\Models\CvProfile;
use Illuminate\View\View;

class PublicPortfolioController extends Controller
{
    public function show(string $slug): View
    {
        $profile = CvProfile::query()
            ->where('public_slug', $slug)
            ->where('is_public', true)
            ->with(['educations', 'experiences', 'skills', 'languages', 'certifications', 'projects'])
            ->firstOrFail();

        return view('portfolio.show', ['profile' => $profile]);
    }
}
