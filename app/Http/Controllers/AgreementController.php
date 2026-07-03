<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgreementController extends Controller
{
    /**
     * List all agreements
     */
    public function index(Request $request): View
    {
        $query = Agreement::query();

        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $agreements = $query->latest()->paginate(20);

        return view('admin.agreements.index', compact('agreements'));
    }

    /**
     * Create agreement form
     */
    public function create(): View
    {
        return view('admin.agreements.form', ['agreement' => null]);
    }

    /**
     * Store new agreement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:agreements,title'],
            'description' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        Agreement::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        // TODO: Log audit trail

        return redirect()->route('admin.agreements.index')
            ->with('success', 'Agreement created.');
    }

    /**
     * Edit agreement form
     */
    public function edit(Agreement $agreement): View
    {
        return view('admin.agreements.form', compact('agreement'));
    }

    /**
     * Update agreement
     */
    public function update(Agreement $agreement, Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', "unique:agreements,title,{$agreement->id}"],
            'description' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $agreement->update([
            ...$validated,
            'updated_by' => auth()->id(),
            'version' => $agreement->version + 1,
        ]);

        // TODO: Log audit trail

        return redirect()->route('admin.agreements.index')
            ->with('success', 'Agreement updated.');
    }

    /**
     * Delete agreement
     */
    public function destroy(Agreement $agreement)
    {
        if ($agreement->signatures()->exists()) {
            return back()->with('error', 'Cannot delete agreement with existing signatures.');
        }

        $agreement->delete();

        // TODO: Log audit trail

        return redirect()->route('admin.agreements.index')
            ->with('success', 'Agreement deleted.');
    }

    /**
     * Assign agreement to participant
     */
    public function assignToParticipant(Participant $participant, Agreement $agreement)
    {
        $participant->agreements()->syncWithoutDetaching([$agreement->id]);

        // TODO: Log audit trail

        return back()->with('success', 'Agreement assigned.');
    }

    /**
     * Remove agreement from participant
     */
    public function removeFromParticipant(Participant $participant, Agreement $agreement)
    {
        $participant->agreements()->detach($agreement->id);

        // TODO: Log audit trail

        return back()->with('success', 'Agreement removed.');
    }

    /**
     * View signed agreement
     */
    public function viewSignature(Agreement $agreement, Participant $participant): View
    {
        $signature = $participant->signedAgreements()
            ->where('agreement_id', $agreement->id)
            ->firstOrFail();

        return view('admin.agreements.signature', compact('agreement', 'participant', 'signature'));
    }
}
