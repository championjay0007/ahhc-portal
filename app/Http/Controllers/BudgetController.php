<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Participant;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

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

        $quarterCol = Schema::hasColumn('budgets', 'quarter_start') ? 'quarter_start' : 'quarter_start_date';

        $budgets = Budget::when($user->cannot('viewAny', Budget::class), function ($q) use ($participantId) {
            $q->where('participant_id', $participantId);
        })->withCount('transactions')->orderBy($quarterCol, 'desc')->paginate(20);

        return view('budgets.index', compact('budgets'));
    }

    public function create(Request $request)
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $participant = null;
        $participants = null;

        if ($request->filled('participant_id')) {
            $participant = Participant::find($request->input('participant_id'));
        }

        if (! $participant) {
            $participants = Participant::orderBy('first_name')->orderBy('last_name')->get();
        }

        $period = $this->service->getQuarterPeriodForDate(now());
        $hasQuarterStartColumn = Schema::hasColumn('budgets', 'quarter_start');

        return view('budgets.create', compact('participant', 'participants', 'period', 'hasQuarterStartColumn'));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $hasQuarterStartColumn =
            Schema::hasColumn('budgets', 'quarter_start');

        $data = $request->validate([
            'participant_id' => ['required', 'integer', 'exists:participants,id'],
            'quarter_start' => [Rule::requiredIf(fn () => $hasQuarterStartColumn), 'nullable', 'date'],
            'quarter_end' => [Rule::requiredIf(fn () => $hasQuarterStartColumn), 'nullable', 'date'],
            'quarter_start_date' => [Rule::requiredIf(fn () => ! $hasQuarterStartColumn), 'nullable', 'date'],
            'quarter_end_date' => [Rule::requiredIf(fn () => ! $hasQuarterStartColumn), 'nullable', 'date'],
            'opening_budget' => 'required|numeric|min:0',
            'carry_over' => 'nullable|numeric|min:0',
        ]);

        $quarterStart = $data['quarter_start'] ?? $data['quarter_start_date'] ?? null;
        $quarterEnd = $data['quarter_end'] ?? $data['quarter_end_date'] ?? null;

        $participantId = $data['participant_id'] ?? $this->resolveParticipantId($request->user());

        if ($quarterStart) {
            $period = $this->service->getQuarterPeriodForDate($quarterStart);
        } else {
            $period = $this->service->getQuarterPeriodForDate(now());
        }

        $quarterStart = $period['quarter_start'];
        $quarterEnd = $period['quarter_end'];

        $existingBudgetQuery = Budget::where('participant_id', $participantId);
        if ($hasQuarterStartColumn) {
            $existingBudgetQuery->where(function ($q) use ($period) {
                $q->where(function ($sq) use ($period) {
                    $sq->whereDate('quarter_start', $period['quarter_start'])
                        ->whereDate('quarter_end', $period['quarter_end']);
                })->orWhere(function ($sq) use ($period) {
                    $sq->whereDate('quarter_start', '<=', $period['quarter_end'])
                        ->whereDate('quarter_end', '>=', $period['quarter_start']);
                });
            });
        } else {
            $existingBudgetQuery->where(function ($q) use ($period) {
                $q->where(function ($sq) use ($period) {
                    $sq->whereDate('quarter_start_date', $period['quarter_start_date'])
                        ->whereDate('quarter_end_date', $period['quarter_end_date']);
                })->orWhere(function ($sq) use ($period) {
                    $sq->whereDate('quarter_start_date', '<=', $period['quarter_end_date'])
                        ->whereDate('quarter_end_date', '>=', $period['quarter_start_date']);
                });
            });
        }

        if ($existingBudget = $existingBudgetQuery->first()) {
            return redirect()->route('budgets.show', $existingBudget)
                ->with('status', 'A budget already exists for this participant and quarter.');
        }

        try {
            $budget = Budget::create([
                'participant_id' => $participantId,
                'quarter_start' => $quarterStart,
                'quarter_end' => $quarterEnd,
                'quarter_start_date' => $quarterStart,
                'quarter_end_date' => $quarterEnd,
                'opening_budget' => $data['opening_budget'],
                'carry_over' => $data['carry_over'] ?? 0,
            ]);

            $this->service->calculateTotals($budget);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                $existing = $existingBudgetQuery->first();
                if ($existing) {
                    return redirect()->route('budgets.show', $existing)
                        ->with('status', 'A budget already exists for this participant and quarter.');
                }
            }

            throw $e;
        }

        return redirect()->route('budgets.index')->with('status', 'Budget created successfully for participant ID '.$participantId.'.');
    }

    public function show(Budget $budget)
    {
        $this->authorize('view', $budget);
        $budget->load('transactions.category');
        $alerts = $this->service->getAlerts($budget);

        return view('budgets.show', compact('budget', 'alerts'));
    }

    public function destroy(Budget $budget)
    {
        $this->authorize('delete', $budget);

        $budget->delete();

        return redirect()->route('budgets.index')
            ->with('status', 'Budget deleted successfully.');
    }

    public function dashboard()
    {
        $user = auth()->user();
        $participantId = $this->resolveParticipantId($user);
        $quarterCol = Schema::hasColumn('budgets', 'quarter_start') ? 'quarter_start' : 'quarter_start_date';

        $budgets = Budget::where('participant_id', $participantId)->orderBy($quarterCol, 'desc')->get();
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
