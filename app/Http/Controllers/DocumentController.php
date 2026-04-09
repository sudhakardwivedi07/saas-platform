<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\Request;

class DocumentController extends AccountBaseController
{
    public function __construct(private readonly DocumentService $documentService)
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Documents';
    }

    public function index(Request $request)
    {
        $this->documents = Document::with('uploader')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->type,   fn ($q) => $q->where('mime_type', 'like', "%{$request->type}%"))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('documents.index', $this->data);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200',
            'name' => 'nullable|string|max:255',
        ]);

        abort_403(!auth()->user()->can('upload_documents'));

        $companyId = company()?->id ?? auth()->user()->company_id;
        abort_403(!$companyId);

        $document = $this->documentService->upload(
            file:      $request->file('file'),
            companyId: $companyId,
            meta:      ['name' => $request->name, 'user_id' => auth()->id()]
        );

        return Reply::successWithData('Document uploaded successfully.', [
            'document' => $document,
        ]);
    }

    public function download(int $id)
    {
        $document = Document::findOrFail($id);
        abort_403(!auth()->user()->can('download_documents'));

        return redirect($this->documentService->getTemporaryUrl($document));
    }

    public function uploadVersion(Request $request, int $id)
    {
        $request->validate([
            'file'  => 'required|file|max:51200',
            'notes' => 'nullable|string|max:500',
        ]);

        $document = Document::findOrFail($id);
        $this->documentService->uploadVersion($document, $request->file('file'), $request->notes);

        return Reply::success('New version uploaded.');
    }

    public function destroy(int $id)
    {
        $document = Document::findOrFail($id);
        abort_403(!auth()->user()->can('delete_documents'));

        $this->documentService->delete($document);
        return Reply::success('Document deleted.');
    }
}
