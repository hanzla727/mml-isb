<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
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
