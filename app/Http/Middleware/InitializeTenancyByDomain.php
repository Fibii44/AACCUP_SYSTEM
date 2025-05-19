<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain as BaseMiddleware;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByDomain extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            Log::info('Attempting to initialize tenancy for domain: ' . $request->getHost());
            
            // Use the parent handle method to initialize tenancy
            return parent::handle($request, $next);
            
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Failed to initialize tenancy: ' . $e->getMessage(), [
                'domain' => $request->getHost(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            // Check if a tenant 404 view exists
            if (View::exists('errors.tenant-404')) {
                // Return a tenant-specific 404 page with branding
                $settings = (object) [
                    'primary_color' => '#3490dc',
                    'secondary_color' => '#ffed4a',
                    'header_text' => $request->getHost(),
                    'footer_text' => 'Domain not found or not configured properly.'
                ];
                
                return response()->view('errors.tenant-404', [
                    'settings' => $settings,
                    'domain' => $request->getHost()
                ], 404);
            }
            
            // If no tenant view exists, fall back to a generic page
            return response()->view('landing', [], 404);
        }
    }
}
