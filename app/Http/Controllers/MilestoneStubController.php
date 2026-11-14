<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MilestoneStubController extends Controller
{
    public function phase3(Request $request): View
    {
        $routeName = (string) $request->route()?->getName();
        $path = $request->path();

        $title = 'Clinical Operations';
        $description = 'This feature is currently under active development and will be included in the next scheduled release.';

        if (str_contains($routeName, 'dispens') || str_contains($path, 'dispens')) {
            $title = 'Medicine Dispensing';
            $description = 'The pharmacy point-of-sale dispensing terminal is currently in development.';
        } elseif (str_contains($routeName, 'medic') || str_contains($path, 'medic') || str_contains($path, 'inventory')) {
            $title = 'Inventory Management';
            $description = 'Medicine stock-in, expiration tracking, and inventory transaction features are currently in development.';
        } elseif (str_contains($routeName, 'schedule') || str_contains($path, 'schedule')) {
            $title = 'Physical Exam Schedules';
            $description = 'Batch scheduling and cohort attendance rosters are currently in development.';
        } elseif (str_contains($routeName, 'clinician') || str_contains($path, 'clinician')) {
            $title = 'Healthcare Providers';
            $description = 'The clinician management roster is currently in development.';
        } elseif (str_contains($routeName, 'physical-exam') || str_contains($path, 'physical-exam')) {
            $title = 'Physical Examination';
            $description = 'Physical examination records, fitness classification, and medical clearances are currently in development.';
        }

        return view('errors.in-development', compact('title', 'description'));
    }

    public function phase4(Request $request): View
    {
        $routeName = (string) $request->route()?->getName();
        $path = $request->path();

        $title = 'System Administration';
        $description = 'This module is scheduled for development in the upcoming phase.';

        if (str_contains($routeName, 'backup') || str_contains($path, 'backup')) {
            $title = 'Database Backups';
            $description = 'Database backup management and archive tools are currently in development.';
        } elseif (str_contains($routeName, 'report') || str_contains($path, 'report')) {
            $title = 'Reports & Analytics';
            $description = 'Morbidity reports, patient classification summaries, and official statistics are currently in development.';
        }

        return view('errors.in-development', compact('title', 'description'));
    }
}
