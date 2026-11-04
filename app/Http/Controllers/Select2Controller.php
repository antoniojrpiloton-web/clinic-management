<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Clinician;
use App\Models\College;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class Select2Controller extends Controller
{
    public function patients(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q'));
        $type = $request->query('type');
        $page = (int) $request->query('page', 1);
        $perPage = 20;

        $query = Patient::query()
            ->with(['college', 'program'])
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($term, fn ($q) => $q->search($term))
            ->latest();

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $results = collect($paginator->items())->map(fn (Patient $p) => [
            'id' => $p->id,
            'text' => "{$p->full_name} ({$p->primary_identifier})",
            'subtext' => ucfirst($p->type).' • '.$p->patient_number.' • '.($p->phone ?: $p->email),
        ]);

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    public function colleges(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q'));
        $page = (int) $request->query('page', 1);
        $perPage = 20;

        $query = College::query()
            ->where('is_active', true)
            ->when($term, function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%");
            })
            ->orderBy('name');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $results = collect($paginator->items())->map(fn (College $c) => [
            'id' => $c->id,
            'text' => "{$c->code} — {$c->name}",
            'subtext' => $c->dean ? "Dean: {$c->dean}" : null,
        ]);

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    public function programs(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q'));
        $collegeId = $request->query('college_id');
        $page = (int) $request->query('page', 1);
        $perPage = 20;

        $query = Program::query()
            ->with('college:id,code')
            ->where('is_active', true)
            ->when($collegeId, fn ($q) => $q->where('college_id', $collegeId))
            ->when($term, function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%");
            })
            ->orderBy('name');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $results = collect($paginator->items())->map(fn (Program $p) => [
            'id' => $p->id,
            'text' => "{$p->code} — {$p->name}",
            'subtext' => "{$p->college->code} • {$p->degree_level} • {$p->years} Years",
        ]);

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    public function academicYears(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q'));
        $page = (int) $request->query('page', 1);
        $perPage = 20;

        $query = AcademicYear::query()
            ->when($term, function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%")
                    ->orWhere('semester', 'like', "%{$term}%");
            })
            ->orderByDesc('start_date');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $results = collect($paginator->items())->map(fn (AcademicYear $ay) => [
            'id' => $ay->id,
            'text' => "{$ay->code} ({$ay->semester})",
            'subtext' => "{$ay->name}".($ay->is_current ? ' • Current Term' : ''),
        ]);

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q'));
        $page = (int) $request->query('page', 1);
        $perPage = 20;

        $query = User::query()
            ->with('roles:id,name')
            ->where('is_active', true)
            ->when($term, function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->orderBy('name');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $results = collect($paginator->items())->map(fn (User $u) => [
            'id' => $u->id,
            'text' => "{$u->name} ({$u->email})",
            'subtext' => $u->roles->pluck('name')->implode(', ') ?: 'No role assigned',
        ]);

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    public function clinicians(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q'));
        $type = $request->query('type');
        $page = (int) $request->query('page', 1);
        $perPage = 20;

        $query = Clinician::query()
            ->active()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($term, fn ($q) => $q->search($term))
            ->orderBy('name');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $results = collect($paginator->items())->map(fn (Clinician $c) => [
            'id' => $c->id,
            'text' => $c->name.($c->license_number ? " (PRC #{$c->license_number})" : ''),
            'subtext' => "{$c->type} • {$c->title}".($c->specialization ? " • {$c->specialization}" : ''),
            'name' => $c->name,
            'license_number' => $c->license_number,
            'title' => $c->title,
            'type' => $c->type,
        ]);

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    public function roles(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q'));

        $roles = Role::query()
            ->when($term, fn ($q) => $q->where('name', 'like', "%{$term}%"))
            ->orderBy('name')
            ->get(['id', 'name']);

        $results = $roles->map(fn (Role $r) => [
            'id' => $r->name,
            'text' => $r->name,
        ]);

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => false],
        ]);
    }

    public function medicines(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q'));
        $page = (int) $request->query('page', 1);
        $perPage = 20;

        $query = Medicine::query()
            ->active()
            ->with(['batches' => fn ($q) => $q->where('expiration_date', '>=', now()->startOfDay())])
            ->when($term, fn ($q) => $q->search($term))
            ->orderBy('generic_name');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $results = collect($paginator->items())->map(fn (Medicine $m) => [
            'id' => $m->id,
            'text' => $m->display_name." [Stock: {$m->stock_on_hand} {$m->unit}s]",
            'subtext' => "Code: {$m->code} • Form: {$m->dosage_form} • Unit: {$m->unit}",
            'unit' => $m->unit,
            'stock_on_hand' => $m->stock_on_hand,
        ]);

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    public function medicineBatches(Request $request): JsonResponse
    {
        $medicineId = $request->query('medicine_id');
        if (! $medicineId) {
            return response()->json(['results' => []]);
        }

        $batches = MedicineBatch::where('medicine_id', $medicineId)
            ->where('quantity_remaining', '>', 0)
            ->where('expiration_date', '>=', now()->startOfDay())
            ->orderBy('expiration_date')
            ->get();

        $results = $batches->map(fn (MedicineBatch $b) => [
            'id' => $b->id,
            'text' => "Lot: {$b->batch_number} (Exp: {$b->expiration_date->format('M Y')}) — {$b->quantity_remaining} left",
            'subtext' => "Supplier: {$b->supplier} • Received: {$b->received_date->format('M d, Y')}",
            'quantity_remaining' => $b->quantity_remaining,
        ]);

        return response()->json([
            'results' => $results,
        ]);
    }
}
