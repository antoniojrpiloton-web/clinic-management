<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class CollegeController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:colleges.view', only: ['index']),
            new Middleware('permission:colleges.create', only: ['store']),
            new Middleware('permission:colleges.edit', only: ['update']),
            new Middleware('permission:colleges.delete', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $colleges = College::query()
            ->withCount('programs')
            ->latest()
            ->paginate(15);

        return view('colleges.index', compact('colleges'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:colleges,code'],
            'name' => ['required', 'string', 'max:150'],
            'dean' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $college = College::create($validated);

        $this->auditLogger->log('created', "Created college {$college->code}", $college);

        return redirect()->route('colleges.index')->with('success', 'College created successfully.');
    }

    public function update(Request $request, College $college): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:colleges,code,'.$college->id],
            'name' => ['required', 'string', 'max:150'],
            'dean' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $college->update($validated);

        $this->auditLogger->log('updated', "Updated college {$college->code}", $college);

        return redirect()->route('colleges.index')->with('success', 'College updated successfully.');
    }

    public function destroy(College $college): RedirectResponse
    {
        if ($college->programs()->exists()) {
            return back()->with('error', 'Cannot delete college with existing academic programs. Please reassign or delete programs first.');
        }

        $code = $college->code;
        $college->delete();

        $this->auditLogger->log('deleted', "Deleted college {$code}", $college);

        return redirect()->route('colleges.index')->with('success', 'College deleted successfully.');
    }
}
