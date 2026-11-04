<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Program;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ProgramController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:programs.view', only: ['index']),
            new Middleware('permission:programs.create', only: ['store']),
            new Middleware('permission:programs.edit', only: ['update']),
            new Middleware('permission:programs.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $collegeFilter = $request->query('college_id');

        $programs = Program::query()
            ->with('college:id,name,code')
            ->when($collegeFilter, fn ($q) => $q->where('college_id', $collegeFilter))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $colleges = College::orderBy('name')->get(['id', 'name', 'code']);

        return view('programs.index', compact('programs', 'colleges', 'collegeFilter'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'college_id' => ['required', 'exists:colleges,id'],
            'code' => ['required', 'string', 'max:50', 'unique:programs,code'],
            'name' => ['required', 'string', 'max:150'],
            'degree_level' => ['required', 'string', 'max:50'],
            'years' => ['required', 'integer', 'min:1', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $program = Program::create($validated);

        $this->auditLogger->log('created', "Created program {$program->code}", $program);

        return redirect()->route('programs.index')->with('success', 'Academic program created successfully.');
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $validated = $request->validate([
            'college_id' => ['required', 'exists:colleges,id'],
            'code' => ['required', 'string', 'max:50', 'unique:programs,code,'.$program->id],
            'name' => ['required', 'string', 'max:150'],
            'degree_level' => ['required', 'string', 'max:50'],
            'years' => ['required', 'integer', 'min:1', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $program->update($validated);

        $this->auditLogger->log('updated', "Updated program {$program->code}", $program);

        return redirect()->route('programs.index')->with('success', 'Academic program updated successfully.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $code = $program->code;
        $program->delete();

        $this->auditLogger->log('deleted', "Deleted program {$code}", $program);

        return redirect()->route('programs.index')->with('success', 'Academic program deleted successfully.');
    }
}
