<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentService $documentService) {}

    public function index(Request $request): JsonResponse
    {
        $documents = Document::where('company_id', $request->user()->company_id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($documents);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200',
            'name' => 'nullable|string|max:255',
        ]);

        $user     = $request->user();
        $document = $this->documentService->upload(
            file:      $request->file('file'),
            companyId: $user->company_id,
            meta:      ['name' => $request->name, 'user_id' => $user->id]
        );

        return response()->json($document, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $document = Document::where('company_id', $request->user()->company_id)
            ->findOrFail($id);

        return response()->json($document);
    }

    public function download(Request $request, int $id)
    {
        $document = Document::where('company_id', $request->user()->company_id)
            ->findOrFail($id);

        return redirect($this->documentService->getTemporaryUrl($document));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $document = Document::where('company_id', $request->user()->company_id)
            ->findOrFail($id);

        $this->documentService->delete($document);

        return response()->json(['message' => 'Document deleted.']);
    }
}
