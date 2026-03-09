<?php

namespace App\Http\Middleware;

use App\Models\LicenseKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        // Support both POST body and GET query parameters
        $licenseKey = $request->input('license_key') ?? $request->query('license_key');

        if (empty($licenseKey)) {
            return response()->json([
                'success' => false,
                'message' => 'License key is required.',
                'debug' => [
                    'method' => $request->method(),
                    'has_query' => $request->query->count(),
                ],
            ], 401);
        }

        $license = LicenseKey::where('key', $licenseKey)->first();

        if (! $license) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid license key.',
                'provided_key' => substr($licenseKey, 0, 8) . '...',
            ], 401);
        }

        if (! $license->isValid()) {
            $reason = ! $license->is_active ? 'License is deactivated.' : 'License has expired.';
            return response()->json([
                'success' => false,
                'message' => $reason,
            ], 403);
        }

        // Attach license to request for downstream use
        $request->merge(['_license' => $license]);

        return $next($request);
    }
}
