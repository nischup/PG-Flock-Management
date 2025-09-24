<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware for guest users
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Check if user has a selected company
        $selectedCompany = session('selected_company_id');

        if (! $selectedCompany) {
            // If user has a company assigned, auto-select it
            if ($user->company_id) {
                session(['selected_company_id' => $user->company_id]);
                $selectedCompany = $user->company_id;
            }
        }

        // Share selected company with views
        view()->share('selectedCompany', $this->getSelectedCompany($selectedCompany));

        return $next($request);
    }

    /**
     * Get the selected company
     */
    private function getSelectedCompany($companyId)
    {
        try {
            return \App\Models\Master\Company::find($companyId);
        } catch (\Exception $e) {
            return null;
        }
    }
}
