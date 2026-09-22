<?php

namespace App\Http\Controllers;

use App\Models\Policy;

class PolicyController extends Controller
{
    /**
     * Show all active policies as a list (optional landing page).
     */
    public function index()
    {
        $policies = Policy::active()->ordered()->get();

        if ($policies->isEmpty()) {
            abort(404, 'No policies available yet.');
        }

        // Show the first policy by default
        return view('frontend.policies.show', [
            'policy'   => $policies->first(),
            'policies' => $policies,
        ]);
    }

    /**
     * Show a single policy by its slug.
     */
    public function show(Policy $policy)
    {
        
        if (! $policy->is_active) {
            abort(404);
        }

        $policies = Policy::active()->ordered()->get();

        return view('frontend.policies.show', [
            'policy'   => $policy,
            'policies' => $policies,
        ]);
    }
}