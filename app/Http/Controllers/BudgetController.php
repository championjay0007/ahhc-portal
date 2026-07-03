<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Participant;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    protected $service;

    public function __construct(BudgetService $service)
    {
        $this->service = $service;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $participantId = $this->resolveParticipantId($user);

        $budgets = Budget::when($user->cannot('viewAny', Budget::class), function ($q) use ($participantId) {
            $q->where('participant_id', $participantId);
        })->withCount('transactions')->latest('quarter_start')->paginate(20);

        return view('budgets.index', compact('budgets'));
    }

    public function create()
    {
        return view('budgets.create');
    }

    public function store(Request $request)
    {
        $hasQuarterStartColumn =
            Schema::hasColumn('budgets', 'quarter_start');

        $data = $request->validate([
            'participant_id' => 'nullable|integer',
            'quarter_start' => [Rule::requiredIf(fn () => $hasQuarterStartColumn), 'nullable', 'date'],
            'quarter_end' => [Rule::requiredIf(fn () => $hasQuarterStartColumn), 'nullable', 'date'],
            'quarter_start_date' => [Rule::requiredIf(fn () => ! $hasQuarterStartColumn), 'nullable', 'date'],
            'quarter_end_date' => [Rule::requiredIf(fn () => ! $hasQuarterStartColumn), 'nullable', 'date'],
            'opening_budget' => 'required|numeric',
            'carry_over' => 'nullable|numeric',
        ]);

        $quarterStart = $data['quarter_start'] ?? $data['quarter_start_date'] ?? null;
        $quarterEnd = $data['quarter_end'] ?? $data['quarter_end_date'] ?? null;

        $participantId = $this->resolveParticipantId($request->user());

        $budget = Budget::create([
            'participant_id' => $data['participant_id'] ?? $participantId,
            'quarter_start' => $quarterStart,
            'quarter_end' => $quarterEnd,
            'quarter_start_date' => $quarterStart,
            'quarter_end_date' => $quarterEnd,
            'opening_budget' => $data['opening_budget'],
            'carry_over' => $data['carry_over'] ?? 0,
        ]);

        $this->service->calculateTotals($budget);

        return redirect()->route('budgets.show', $budget);
    }

    public function show(Budget $budget)
    {
        $this->authorize('view', $budget);
        $budget->load('transactions.category');
        $alerts = $this->service->getAlerts($budget);

        return view('budgets.show', compact('budget', 'alerts'));
    }

    public function dashboard()
    {
        $user = auth()->user();
        $participantId = $this->resolveParticipantId($user);

        $budgets = Budget::where('participant_id', $participantId)->orderBy('quarter_start', 'desc')->get();
        foreach ($budgets as $b) {
            $b->alerts = $this->service->getAlerts($b);
        }

        return view('budgets.dashboard', compact('budgets'));
    }

    private function resolveParticipantId($user)
    {
        if ($user->participant) {
            return $user->participant->id;
        }

        return Participant::firstOrCreate([
            'user_id' => $user->id,
        ], [
            'participant_number' => 'P-'.strtoupper(Str::random(8)),
            'first_name' => explode(' ', trim($user->name ?? 'Participant'))[0] ?? 'Participant',
            'last_name' => explode(' ', trim($user->name ?? 'Participant'))[1] ?? 'User',
            'status' => Participant::STATUS_ACTIVE,
            'phone' => $user->phone ?? '0000000000',
            'email' => $user->email,
        ])->id;
    }
}
