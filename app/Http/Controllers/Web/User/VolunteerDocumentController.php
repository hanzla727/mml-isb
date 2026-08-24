<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\VolunteerDocument;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VolunteerDocumentController extends Controller
{
    public function index(Request $request)
    {
        return view('user.documents.index', [
            'documents' => $request->user()->documents()->with('file')->orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', Rule::in(['cnic', 'certificate', 'agreement', 'training', 'other'])],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $document = $request->user()->documents()->create([
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

    public function destroy(Request $request, VolunteerDocument $document)
    {
        $this->authorize('delete', $document);

        $document->delete();

        return back()->with('status', 'Document deleted.');
    }
}
