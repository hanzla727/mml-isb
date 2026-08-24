<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVolunteerDocumentRequest;
use App\Http\Resources\VolunteerDocumentResource;
use App\Models\Media;
use App\Models\VolunteerDocument;
use Illuminate\Http\Request;

class VolunteerDocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = $request->user()->documents()
            ->with(['file', 'uploader'])
            ->orderByDesc('created_at')
            ->get();

        return VolunteerDocumentResource::collection($documents);
    }

    public function store(StoreVolunteerDocumentRequest $request)
    {
        $document = $request->user()->documents()->create([
            'title' => $request->validated('title'),
            'document_type' => $request->validated('document_type'),
            'uploaded_by' => $request->user()->id,
        ]);

        $path = $request->file('file')->store('volunteer-documents', 'public');
        $document->file()->save(new Media([
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $request->file('file')->getClientMimeType(),
            'size' => $request->file('file')->getSize(),
        ]));

        return new VolunteerDocumentResource($document->load(['file', 'uploader']));
    }

    public function destroy(Request $request, VolunteerDocument $document)
    {
        $this->authorize('delete', $document);

        $document->delete();

        return response()->json(['message' => 'Document deleted.']);
    }
}
