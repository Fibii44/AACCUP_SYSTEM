<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedException;

class CheckTenantDomainStatus extends InitializeTenancyByDomain
{
    public function handle($request, Closure $next)
    {
        try {
            // First, let the parent middleware identify the tenant
            $response = parent::handle($request, $next);
            
            // Get the current tenant
            $tenant = tenant();
            
            // Check if the domain is enabled
            if (!$tenant->is_domain_enabled) {
                abort(403, 'This domain has been disabled by the administrator.');
            }
            
            return $response;
        } catch (TenantCouldNotBeIdentifiedException $e) {
            // If tenant couldn't be identified, let the parent middleware handle it
            return parent::handle($request, $next);
        }
    }
} 