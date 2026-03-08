<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateDomain
{
    public function __construct(
        protected LicenseService $licenseService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $licenseKey = $request->input('license_key');
        $domain = $request->input('domain');

        if (empty($domain)) {
            return response()->json([
                'success' => false,
                'message' => 'Domain is required.',
            ], 400);
        }

        $activeDomain = $this->licenseService->findActiveDomain($licenseKey, $domain);

        if (! $activeDomain) {
            return response()->json([
                'success' => false,
                'message' => 'Domain is not registered or not active for this license.',
            ], 403);
        }

        // Attach domain to request for downstream use
        $request->merge(['_domain' => $activeDomain]);

        return $next($request);
    }
}
