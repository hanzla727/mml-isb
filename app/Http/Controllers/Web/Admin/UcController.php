<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Na;
use App\Models\Uc;
use App\Services\HierarchyScope;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UcController extends Controller
{
    public function index(Request $request)
    {
        $query = Uc::with('na')->withCount(['teams', 'members']);
        HierarchyScope::restrictByUc($query, $request->user(), 'id');

        return view('admin.ucs.index', [
            'ucs' => $query->orderBy('name')->get(),
            'nas' => $this->visibleNas($request),
        ]);
    }

    public function store(Request $request)
    {
        Uc::create($this->validated($request));

        return back()->with('status', 'UC created.');
    }

    public function update(Request $request, Uc $uc)
    {
        $uc->update($this->validated($request));

        return back()->with('status', 'UC updated.');
    }

    public function destroy(Uc $uc)
    {
        $uc->delete();

        return back()->with('status', 'UC deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'na_id' => ['required', 'exists:nas,id'],
            'name' => ['required', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function visibleNas(Request $request)
    {
        $naIds = HierarchyScope::visibleNaIds($request->user());

        return Na::query()
            ->when($naIds !== null, fn ($q) => $q->whereIn('id', $naIds))
            ->orderBy('name')
            ->get();
    }
}
