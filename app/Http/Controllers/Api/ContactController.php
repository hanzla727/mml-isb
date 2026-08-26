<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(StoreContactRequest $request)
    {
        $path = $request->hasFile('photo') ? $request->file('photo')->store('contact-photos', 'public') : null;
        $creator = $request->user();

        $contact = Contact::create([
            'name' => $request->validated('name'),
            'phone' => $request->validated('phone'),
            'cnic' => $request->validated('cnic'),
            'address' => $request->validated('address'),
            'notes' => $request->validated('notes'),
            'photo_path' => $path,
            'created_by' => $creator->id,
            // A contact belongs to whichever NA/UC the volunteer who logged
            // it operates in — taken from the creator, not asked for again,
            // so it can never mismatch what's already on their own profile.
            'na_id' => $creator->na_id,
            'uc_id' => $creator->uc_id,
        ]);

        return new ContactResource($contact);
    }

    /**
     * Contacts are private: visible only to whoever created them, plus
     * Admin/Super Admin — not NA Head, UC Head, or Team Leader, even though
     * they hold 'view-reports' for everything else in the app.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Contact::query()->with(['na', 'uc'])->withCount('meetings');

        if (! $user->hasAnyRole(['super_admin', 'admin'])) {
            $query->where('created_by', $user->id);
        }

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->string('search');
            $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('cnic', 'like', "%{$search}%");
            });
        });

        $contacts = $query->orderBy('name')->paginate($request->integer('per_page', 20));

        return ContactResource::collection($contacts);
    }

    public function show(Request $request, Contact $contact)
    {
        $user = $request->user();

        if (! $user->hasAnyRole(['super_admin', 'admin']) && $contact->created_by !== $user->id) {
            abort(403);
        }

        return new ContactResource($contact->load(['na', 'uc', 'meetings.dailyReport.user']));
    }
}
