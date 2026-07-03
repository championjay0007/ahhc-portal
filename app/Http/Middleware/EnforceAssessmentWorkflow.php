<?php

namespace App\Http\Middleware;

use App\Models\Assessment;
use App\Models\Participant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceAssessmentWorkflow
{
    /**
     * Handle an incoming request.
     *
     * CRITICAL GUARD RAIL:
     * No participant can access portal features without completing assessment workflow.
     * This middleware ensures participants cannot bypass assessment stages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for admin/staff accessing admin routes
        if ($request->user() && $request->user()->hasRole(['admin', 'system_admin', 'ahhc_staff'])) {
            return $next($request);
        }

        // If user is authenticated and is a participant
        if ($request->user() && $request->user()->participant) {
            if ($request->routeIs('portal.participant.documents.*')) {
                return $next($request);
            }

            $participant = $request->user()->participant;

            // If participant is still in onboarding process, redirect to onboarding status
            if ($participant->status === Participant::STATUS_ONBOARDING) {
                if ($request->routeIs('portal.onboarding.*') || $request->routeIs('portal.onboarding.status')) {
                    return $next($request);
                }

                return redirect()->route('portal.onboarding.status');
            }

            // If participant status indicates they're in review or assessment phase
            if (in_array($participant->status, [
                Participant::STATUS_PENDING_ADMIN_REVIEW,
                Participant::STATUS_AHHC_REVIEW,
                Participant::STATUS_ELIGIBILITY_ASSESSMENT,
                Participant::STATUS_SUITABILITY_ASSESSMENT,
            ], true)) {
                if ($request->routeIs('portal.onboarding.status')) {
                    return $next($request);
                }

                return redirect()->route('portal.onboarding.status');
            }

            // For ACTIVE participants, check if they have an assessment and it's activated
            if ($participant->status === Participant::STATUS_ACTIVE) {
                $assessment = $participant->assessment;

                // If they have an active assessment, allow access
                if ($assessment && in_array($assessment->status, [
                    Assessment::STATUS_PORTAL_ACTIVATED,
                    Assessment::STATUS_ACTIVE_PARTICIPANT,
                ], true)) {
                    return $next($request);
                }

                // If active but no assessment or assessment not activated, allow dashboard access
                // (assessment may not be set up yet in some workflows)
                return $next($request);
            }
        }

        return $next($request);
    }
}
