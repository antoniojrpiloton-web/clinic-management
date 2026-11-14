<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MilestoneStubController extends Controller
{
    public function phase3(Request $request): View
    {
        $title = $request->query('module', 'Phase 3 Clinical Operations');
        $phase = '3';
        $description = 'Physical examination cohorts, automated scheduling, clinician roster, and pharmacy POS dispensing are scheduled under Milestone Phase 3.';

        return view('errors.in-development', compact('title', 'phase', 'description'));
    }

    public function phase4(Request $request): View
    {
        $title = $request->query('module', 'Phase 4 Analytics & Admin');
        $phase = '4';
        $description = 'Official morbidity statistical reports, patient classification summaries, and institutional backups are scheduled under Milestone Phase 4.';

        return view('errors.in-development', compact('title', 'phase', 'description'));
    }
}
