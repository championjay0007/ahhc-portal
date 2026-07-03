<?php

namespace App\Http\Controllers\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use Illuminate\Support\Facades\Auth;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        // basic aggregates for participant
        $documents = $participant->documents()->latest()->take(5)->get();
        $invoices = $participant->invoices()->latest()->take(5)->get();

        return view('portal.dashboard', compact('participant', 'documents', 'invoices'));
    }
}
