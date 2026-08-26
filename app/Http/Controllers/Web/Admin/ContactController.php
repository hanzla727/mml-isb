<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Na;
use App\Models\Uc;
use Illuminate\Http\Request;

/**
 * Contacts are private: visible only to whoever created them, plus
 * Admin/Super Admin — not NA Head, UC Head, or Team Leader, even though
 * they hold 'view-reports' for everything else in the app. Mirrors
 * Api\ContactController's same rule, applied here for the admin panel.
 */
class ContactController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Contact::query()->with(['na', 'uc', 'creator'])->withCount('meetings');

        if (! $user->hasAnyRole(['super_admin', 'admin'])) {
            $query->where('created_by', $user->id);
        }

        $contacts = $query
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(fn ($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            })
            ->when($request->filled('na_id'), fn ($q) => $q->where('na_id', $request->integer('na_id')))
            ->when($request->filled('uc_id'), fn ($q) => $q->where('uc_id', $request->integer('uc_id')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.contacts.index', [
            'contacts' => $contacts,
            'nas' => Na::orderBy('name')->get(),
            'ucs' => Uc::orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, Contact $contact)
    {
        $user = $request->user();

        abort_unless($user->hasAnyRole(['super_admin', 'admin']) || $contact->created_by === $user->id, 403);

        return view('admin.contacts.show', [
            'contact' => $contact->load(['na', 'uc', 'creator', 'meetings.dailyReport.user']),
        ]);
    }
}
