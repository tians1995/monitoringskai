<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->role === 'ADMIN', 403);

        return Inertia::render('Audits/Index', [
            'audits' => Audit::orderByDesc('year')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->role === 'ADMIN', 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('audits')->where('year', $request->input('year'))],
            'year' => ['required', 'integer', 'min:1900', 'max:2200'],
        ]);

        Audit::create($data);

        return redirect()->route('audits.index')->with('success', 'Audit berhasil ditambahkan.');
    }

    public function update(Request $request, Audit $audit)
    {
        abort_unless(auth()->user()->role === 'ADMIN', 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('audits')->where('year', $request->input('year'))->ignore($audit->id)],
            'year' => ['required', 'integer', 'min:1900', 'max:2200'],
        ]);

        $audit->update($data);

        return redirect()->route('audits.index')->with('success', 'Audit berhasil diperbarui.');
    }
}
