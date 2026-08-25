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

        $contact = Contact::create([
            'name' => $request->validated('name'),
            'phone' => $request->validated('phone'),
            'cnic' => $request->validated('cnic'),
            'address' => $request->validated('address'),
            'notes' => $request->validated('notes'),
            'photo_path' => $path,
            'created_by' => $request->user()->id,
        ]);

        return new ContactResource($contact);
    }

    public function index(Request $request)
    {
        $query = Contact::query()->withCount('meetings');

        if (! $request->user()->can('view-reports')) {
            $query->where('created_by', $request->user()->id);
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
        if (! $request->user()->can('view-reports') && $contact->created_by !== $request->user()->id) {
            abort(403);
        }

        return new ContactResource($contact->load(['meetings.dailyReport.user']));
    }
}
