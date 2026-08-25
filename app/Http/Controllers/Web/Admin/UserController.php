<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\Department;
use App\Models\Media;
use App\Models\Na;
use App\Models\Team;
use App\Models\Uc;
use App\Models\User;
use App\Models\VolunteerDocument;
use App\Services\HierarchyScope;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Full user management is an Admin/Super Admin/NA Head concern —
        // Team Leaders use the dedicated "My Team" page instead, scoped to
        // their own team.
        abort_unless($request->user()->hasAnyRole(['super_admin', 'admin', 'na_head', 'uc_head']), 403);

        $query = User::query()->with(['na', 'uc', 'department', 'team', 'roles']);
        HierarchyScope::restrictUsersQuery($query, $request->user());

        $users = $query
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    public function show(Request $request, User $user)
    {
        abort_unless(HierarchyScope::canView($request->user(), $user), 403);

        return view('admin.users.show', [
            'viewedUser' => $user->load(['na', 'uc', 'department', 'team']),
            'documents' => $user->documents()->with(['file', 'uploader'])->orderByDesc('created_at')->get(),
        ]);
    }

    public function storeDocument(Request $request, User $user)
    {
        $this->authorize('uploadFor', [VolunteerDocument::class, $user]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', Rule::in(['cnic', 'certificate', 'agreement', 'training', 'other'])],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $document = $user->documents()->create([
            'title' => $validated['title'],
            'document_type' => $validated['document_type'],
            'uploaded_by' => $request->user()->id,
        ]);

        $path = $request->file('file')->store('volunteer-documents', 'public');
        $document->file()->save(new Media([
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $request->file('file')->getClientMimeType(),
            'size' => $request->file('file')->getSize(),
        ]));

        return back()->with('status', 'Document uploaded.');
    }

    public function destroyDocument(VolunteerDocument $document)
    {
        $this->authorize('delete', $document);

        $document->delete();

        return back()->with('status', 'Document deleted.');
    }

    public function create()
    {
        return view('admin.users.form', [
            'user' => new User(),
            'nas' => Na::orderBy('name')->get(),
            'ucs' => Uc::with('na')->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'teams' => Team::with('uc')->orderBy('name')->get(),
            'potentialHeads' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'pin' => $validated['pin'] ?? null,
            'na_id' => $this->resolveNaId($validated),
            'uc_id' => in_array($validated['role'], ['na_head', 'uc_head'], true) ? null : ($validated['uc_id'] ?? null),
            'department_id' => $validated['department_id'] ?? null,
            'team_id' => $validated['team_id'] ?? null,
            'reporting_head_id' => $validated['reporting_head_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $user->assignRole($validated['role']);
        $this->syncNaLeadership($user, $validated);
        $this->syncUcLeadership($user, $validated);

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', [
            'user' => $user,
            'nas' => Na::orderBy('name')->get(),
            'ucs' => Uc::with('na')->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'teams' => Team::with('uc')->orderBy('name')->get(),
            'potentialHeads' => User::where('is_active', true)->where('id', '!=', $user->id)->orderBy('name')->get(),
        ]);
    }

    public function update(StoreUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'na_id' => $this->resolveNaId($validated),
            'uc_id' => in_array($validated['role'], ['na_head', 'uc_head'], true) ? null : ($validated['uc_id'] ?? null),
            'department_id' => $validated['department_id'] ?? null,
            'team_id' => $validated['team_id'] ?? null,
            'reporting_head_id' => $validated['reporting_head_id'] ?? null,
            'is_active' => $validated['is_active'] ?? $user->is_active,
            'pin' => $validated['pin'] ?? null,
            ...(! empty($validated['password']) ? ['password' => $validated['password']] : []),
        ]);

        $user->syncRoles([$validated['role']]);
        $this->syncNaLeadership($user, $validated);
        $this->syncUcLeadership($user, $validated);

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($request->user()->id === $user->id, 422, 'You cannot delete your own account.');

        $user->delete();

        return back()->with('status', 'User deleted.');
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', 'User status updated.');
    }

    /**
     * An NA Head has no single UC (they oversee every UC under their NA), so
     * their na_id is picked directly. A UC Head can span several UCs (and
     * in principle several NAs), so their na_id is left null too — their
     * actual scope lives entirely in the uc_heads pivot, resolved via
     * HierarchyScope, not this single column. Everyone else's na_id is
     * derived from their chosen UC rather than picked separately — it's
     * always exactly that UC's own na_id, so asking the admin to keep both
     * in sync manually would just invite mismatches.
     */
    private function resolveNaId(array $validated): ?int
    {
        if ($validated['role'] === 'na_head') {
            return $validated['na_id'] ?? null;
        }

        if ($validated['role'] === 'uc_head') {
            return null;
        }

        $ucId = $validated['uc_id'] ?? null;

        return $ucId ? Uc::find($ucId)?->na_id : null;
    }

    /**
     * Keep the uc_heads pivot in sync with the role just assigned — mirrors
     * syncNaLeadership()'s handling of admin_na, but for a UC Head's
     * possibly-multiple UCs (uc_ids[]) rather than an Admin's NAs.
     */
    private function syncUcLeadership(User $user, array $validated): void
    {
        if ($user->hasRole('uc_head')) {
            $user->ucsHeaded()->sync($validated['uc_ids'] ?? []);
        } else {
            $user->ucsHeaded()->sync([]);
        }
    }

    /**
     * Keep NA/admin_na ownership in sync with the role just assigned: an
     * Admin can be granted several NAs (na_ids[]), an NA Head owns exactly
     * one (their own na_id, mirrored onto Na::na_head_id). Clears stale
     * ownership if the role changed away from either.
     */
    private function syncNaLeadership(User $user, array $validated): void
    {
        Na::where('na_head_id', $user->id)->update(['na_head_id' => null]);

        if ($user->hasRole('na_head') && $user->na_id !== null) {
            Na::where('id', $user->na_id)->update(['na_head_id' => $user->id]);
        }

        if ($user->hasRole('admin')) {
            $user->adminNas()->sync($validated['na_ids'] ?? []);
        } else {
            $user->adminNas()->sync([]);
        }
    }
}
