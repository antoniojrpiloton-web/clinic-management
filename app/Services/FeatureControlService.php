<?php

namespace App\Services;

use App\Models\User;

class FeatureControlService
{
    public function __construct(protected SettingService $settings) {}

    public static function definitions(): array
    {
        return [

            'feature_dashboard' => [
                'key' => 'feature_dashboard',
                'title' => 'Dashboard & Real-Time Overview',
                'description' => 'Executive clinic dashboard, KPI summary cards, and daily activity overview.',
                'icon' => 'bi-grid-fill',
                'milestone' => 25,
                'milestone_label' => 'Phase 1 — 25% Initial Turn-Over',
                'default' => '1',
                'route_patterns' => ['dashboard', 'dashboard.*'],
            ],
            'feature_patients' => [
                'key' => 'feature_patients',
                'title' => 'Patient Masterlist & Medical Profiles',
                'description' => 'Centralized registry of students and employees, batch CSV import, and baseline medical profiles.',
                'icon' => 'bi-person-vcard',
                'milestone' => 25,
                'milestone_label' => 'Phase 1 — 25% Initial Turn-Over',
                'default' => '1',
                'route_patterns' => ['patients.*', 'students.*', 'employees.*', 'medical-profiles.*'],
            ],
            'feature_logbook' => [
                'key' => 'feature_logbook',
                'title' => 'Daily Clinic Logbook & Self-Service Kiosk',
                'description' => 'Walk-in patient check-in/time-out kiosk and daily reception logbook.',
                'icon' => 'bi-journal-check',
                'milestone' => 25,
                'milestone_label' => 'Phase 1 — 25% Initial Turn-Over',
                'default' => '1',
                'route_patterns' => ['clinic-logbooks.*', 'logbook.*'],
            ],

            'feature_medical_consultations' => [
                'key' => 'feature_medical_consultations',
                'title' => 'Medical Consultations & Visit Logs',
                'description' => 'Outpatient medical consultations, clinical notes, medical certificates, and referral slips.',
                'icon' => 'bi-clipboard2-pulse',
                'milestone' => 50,
                'milestone_label' => 'Phase 2 — 50% Clinical Consultations',
                'default' => '0',
                'route_patterns' => ['medical-consultations.*', 'clinic-visits.*'],
            ],
            'feature_dental_consultations' => [
                'key' => 'feature_dental_consultations',
                'title' => 'Dental Consultations & Oral Health Card',
                'description' => 'Interactive FDI tooth odontogram, DMFT/dft indices, dental procedures, and 2-page Oral Health Card PDF.',
                'icon' => 'bi-emoji-smile',
                'milestone' => 50,
                'milestone_label' => 'Phase 2 — 50% Clinical Consultations',
                'default' => '0',
                'route_patterns' => ['dental-consultations.*'],
            ],
            'feature_emergency_first_aid' => [
                'key' => 'feature_emergency_first_aid',
                'title' => 'Emergency & First Aid Records',
                'description' => 'Acute emergency case documentation, triage interventions, and first aid treatment logs.',
                'icon' => 'bi-heart-pulse-fill',
                'milestone' => 50,
                'milestone_label' => 'Phase 2 — 50% Clinical Consultations',
                'default' => '0',
                'route_patterns' => ['emergency-records.*', 'first-aid-records.*'],
            ],

            'feature_physical_examinations' => [
                'key' => 'feature_physical_examinations',
                'title' => 'Physical Examinations, Schedules & Providers',
                'description' => 'Scheduled PE cohorts, physical examination forms, PE certificates, exemptions, and clinician directory.',
                'icon' => 'bi-lungs',
                'milestone' => 75,
                'milestone_label' => 'Phase 3 — 75% PE & Pharmacy Operations',
                'default' => '0',
                'route_patterns' => ['physical-examinations.*', 'physical-exam-schedules.*', 'clinicians.*'],
            ],
            'feature_pharmacy_dispensing' => [
                'key' => 'feature_pharmacy_dispensing',
                'title' => 'Pharmacy POS Dispensing Terminal',
                'description' => 'Full-screen Point-of-Sale medicine dispenser with FEFO batch allocation and dispensing slips.',
                'icon' => 'bi-capsule-pill',
                'milestone' => 75,
                'milestone_label' => 'Phase 3 — 75% PE & Pharmacy Operations',
                'default' => '0',
                'route_patterns' => ['inventory.dispensings.*'],
            ],
            'feature_pharmacy_inventory' => [
                'key' => 'feature_pharmacy_inventory',
                'title' => 'Medicine Masterlist & Stock Inventory',
                'description' => 'Medicine catalog, stock-in receiving, stock-out adjustments, expiration monitoring, and ledger.',
                'icon' => 'bi-boxes',
                'milestone' => 75,
                'milestone_label' => 'Phase 3 — 75% PE & Pharmacy Operations',
                'default' => '0',
                'route_patterns' => [
                    'medicines.*',
                    'medicine-categories.*',
                    'inventory.stock-in*',
                    'inventory.stock-out*',
                    'inventory.expirations',
                    'inventory.batches.*',
                    'inventory.transactions',
                ],
            ],

            'feature_reports_analytics' => [
                'key' => 'feature_reports_analytics',
                'title' => 'Reports, Analytics & Official PDF Exports',
                'description' => 'Phase 8 Medical & Dental Service Reports, Patient Classification, Monthly/Annual Statistics, and Morbidity PDFs.',
                'icon' => 'bi-bar-chart-line-fill',
                'milestone' => 100,
                'milestone_label' => 'Phase 4 — 100% Analytics & System Admin',
                'default' => '0',
                'route_patterns' => ['reports.clinic.*', 'inventory.reports'],
            ],
            'feature_administration' => [
                'key' => 'feature_administration',
                'title' => 'User, Role, Permission & Audit Administration',
                'description' => 'RBAC user accounts, role permissions matrix, and comprehensive security audit trails.',
                'icon' => 'bi-shield-lock-fill',
                'milestone' => 100,
                'milestone_label' => 'Phase 4 — 100% Analytics & System Admin',
                'default' => '0',
                'route_patterns' => ['users.*', 'roles.*', 'permissions.*', 'audit-logs.*'],
            ],
            'feature_configuration_backups' => [
                'key' => 'feature_configuration_backups',
                'title' => 'System Configuration, Academic Setup & Backups',
                'description' => 'Institutional branding, academic years, colleges, programs, disease categories, and database backups.',
                'icon' => 'bi-sliders2',
                'milestone' => 100,
                'milestone_label' => 'Phase 4 — 100% Analytics & System Admin',
                'default' => '0',
                'route_patterns' => [
                    'settings.*',
                    'academic-years.*',
                    'colleges.*',
                    'programs.*',
                    'disease-categories.*',
                    'backups.*',
                ],
            ],
        ];
    }

    public function isEnabled(string $featureKey): bool
    {
        $defs = self::definitions();
        $default = $defs[$featureKey]['default'] ?? '1';

        return (string) $this->settings->get($featureKey, $default) === '1';
    }

    public function userCanAccess(string $featureKey, ?User $user = null): bool
    {
        if ($user && $user->isFeatureController()) {
            return true;
        }

        return $this->isEnabled($featureKey);
    }

    public function resolveFeatureForRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        foreach (self::definitions() as $key => $def) {
            foreach ($def['route_patterns'] as $pattern) {
                if (fnmatch($pattern, $routeName)) {
                    return $key;
                }
            }
        }

        return null;
    }

    public function allWithStatus(): array
    {
        $result = [];
        foreach (self::definitions() as $key => $def) {
            $def['enabled'] = $this->isEnabled($key);
            $result[$key] = $def;
        }

        return $result;
    }

    public function progressSummary(): array
    {
        $all = $this->allWithStatus();
        $total = count($all);
        $enabled = count(array_filter($all, fn (array $item): bool => (bool) $item['enabled']));
        $percentage = $total > 0 ? (int) round(($enabled / $total) * 100) : 100;

        return [
            'enabled_count' => $enabled,
            'total_count' => $total,
            'percentage' => $percentage,
        ];
    }

    public function applyMilestonePreset(int $milestonePercentage): void
    {
        $updates = [];
        foreach (self::definitions() as $key => $def) {
            $updates[$key] = $def['milestone'] <= $milestonePercentage ? '1' : '0';
        }

        $this->settings->set($updates);
    }
}
