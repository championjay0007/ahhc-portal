<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicEnquiryRequest;
use App\Models\Enquiry;
use App\Models\Participant;
use App\Models\User;
use App\Services\NotificationCenterService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PublicWebsiteController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'admin') {
                return redirect()->route('portal.admin.dashboard');
            }

            if ($user->role === 'worker') {
                return redirect()->route('portal.worker.dashboard');
            }

            return redirect()->route('portal.dashboard');
        }

        return view('welcome', [
            'portalUrl' => 'https://portal.allegiancehearthomecare.com.au/',
        ]);
    }

    public function storeEnquiry(StorePublicEnquiryRequest $request)
    {
        try {
            $enquiry = Enquiry::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'role' => $request->input('role'),
                'support_at_home_status' => $request->input('support_status'),
                'message' => $request->input('message'),
                'consent' => true,
                'status' => Enquiry::STATUS_NEW,
            ]);

            // Create a lightweight Participant record so the submitter appears in the admin participants list
            // if no matching participant already exists for this email.
            try {
                $existing = Participant::where('email', $request->input('email'))->first();
                if (! $existing) {
                    $names = preg_split('/\s+/', trim($request->input('name')));
                    $first = $names[0] ?? null;
                    $last = count($names) > 1 ? implode(' ', array_slice($names, 1)) : null;

                    Participant::create([
                        'first_name' => $first,
                        'last_name' => $last,
                        'email' => $request->input('email'),
                        'phone' => $request->input('phone'),
                        'status' => Participant::STATUS_AHHC_REVIEW,
                        'created_by_id' => null,
                    ]);
                }
            } catch (\Exception $e) {
                // Don't fail the enquiry submission if participant creation fails — log and continue
                \Log::warning('Failed to create participant from enquiry: '.$e->getMessage());
            }

            $admins = User::whereIn('role', ['admin', 'system_admin'])->get();

            foreach ($admins as $admin) {
                NotificationCenterService::send(
                    'new_enquiry',
                    $admin->id,
                    [
                        'title' => 'New Public Enquiry',
                        'message' => "{$enquiry->name} submitted an enquiry for AHHC Self-Management Support.",
                        'enquiry_id' => $enquiry->id,
                    ]
                );
            }

            $message = 'Thank you for your enquiry. A team member from Allegiance Heart Home care will contact you to discuss your self-management support request and next steps.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return redirect()->back()->with('status', $message);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Enquiry submission failed: '.$e->getMessage(), ['exception' => $e]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit enquiry. Please try again.',
                ], 500);
            }

            return redirect()->back()->withErrors(['error' => 'Failed to submit enquiry. Please try again.'])->withInput();
        }
    }
}
