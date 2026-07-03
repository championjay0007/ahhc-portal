<?php

namespace App\Http\Controllers\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use Illuminate\Support\Facades\Auth;

class WorkerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $worker = Worker::where('user_id', $user->id)->firstOrFail();

        $assignments = $worker->assignments()->with('participant')->latest()->take(10)->get();

        return view('worker.dashboard', compact('worker', 'assignments'));
    }
}
