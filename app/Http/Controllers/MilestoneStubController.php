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

        $module = 'Clinical Operations Module';
        $icon = 'bi-cone-striped';
        $description = 'Ang clinical module na ito ay kasalukuyang nasa active sprint development para sa susunod na delivery phase (Phase 3).';

        if (str_contains($routeName, 'dispens') || str_contains($path, 'dispens')) {
            $module = 'Pharmacy POS Dispensing Terminal';
            $icon = 'bi-capsule-pill';
            $description = 'Ang Point-of-Sale (POS) medicine dispenser at electronic prescription fulfillment ay kasalukuyang ongoing ang development para sa Phase 3 delivery.';
        } elseif (str_contains($routeName, 'medic') || str_contains($path, 'medic') || str_contains($path, 'inventory')) {
            $module = 'Medicine Catalog & Stock Inventory';
            $icon = 'bi-boxes';
            $description = 'Ang medicine masterlist catalog, stock-in receiving, expiration monitoring, at inventory ledger ay kasalukuyang ongoing ang development para sa Phase 3 delivery.';
        } elseif (str_contains($routeName, 'schedule') || str_contains($path, 'schedule')) {
            $module = 'Physical Examination Schedules & Batches';
            $icon = 'bi-calendar-check';
            $description = 'Ang entrance cohorts PE batch scheduling at attendee roster management ay kasalukuyang ongoing ang development para sa Phase 3 delivery.';
        } elseif (str_contains($routeName, 'clinician') || str_contains($path, 'clinician')) {
            $module = 'Healthcare Providers Directory';
            $icon = 'bi-person-badge';
            $description = 'Ang clinician profiles at medical signature roster ay kasalukuyang ongoing ang development para sa Phase 3 delivery.';
        } elseif (str_contains($routeName, 'physical-exam') || str_contains($path, 'physical-exam')) {
            $module = 'Physical Examination Records';
            $icon = 'bi-lungs';
            $description = 'Ang full physical examination forms, fitness classification, at medical waivers ay kasalukuyang ongoing ang development para sa Phase 3 delivery.';
        }

        return view('errors.in-development', [
            'title' => $module,
            'icon' => $icon,
            'phase' => 'Phase 3 (75% Milestone)',
            'phaseBadge' => 'Ongoing Development — Scheduled for Phase 3',
            'status' => 'Ongoing Development',
            'description' => $description,
        ]);
    }

    public function phase4(Request $request): View
    {
        $routeName = (string) $request->route()?->getName();
        $path = $request->path();

        $module = 'Reports & System Administration';
        $icon = 'bi-file-earmark-bar-graph';
        $description = 'Ang advanced reporting at system maintenance tools ay naka-schedule para sa Phase 4 release.';

        if (str_contains($routeName, 'backup') || str_contains($path, 'backup')) {
            $module = 'Database Backup Management';
            $icon = 'bi-database-gear';
            $description = 'Ang automated database snapshots, archive compression, at direct download tools ay naka-schedule para sa Phase 4 (100% Final Release).';
        } elseif (str_contains($routeName, 'report') || str_contains($path, 'report')) {
            $module = 'Morbidity & Institutional Statistical Reports';
            $icon = 'bi-bar-chart-line';
            $description = 'Ang annual communicable / non-communicable morbidity surveillance tables at official 2-page PDF exports ay naka-schedule para sa Phase 4 (100% Final Release).';
        }

        return view('errors.in-development', [
            'title' => $module,
            'icon' => $icon,
            'phase' => 'Phase 4 (100% Final Release)',
            'phaseBadge' => 'Scheduled Sprint — Next Milestone',
            'status' => 'Scheduled for Phase 4',
            'description' => $description,
        ]);
    }
}
