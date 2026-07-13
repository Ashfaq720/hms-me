<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:setting_access']);
    }

    public function index(): View
    {
        $activities = Activity::with('causer', 'subject')
            ->latest()
            ->paginate(20);

        return view('backend.activity-logs.index', compact('activities'));
    }
}
