<?php

namespace App\Http\Middleware;

use App\Models\Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleIsActive
{
    public function handle(
        Request $request,
        Closure $next,
        string $slug
    ): Response {
        $moduleIsActive = Module::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->exists();

        abort_unless(
            $moduleIsActive,
            404,
            'Ce module est actuellement désactivé.'
        );

        return $next($request);
    }
}