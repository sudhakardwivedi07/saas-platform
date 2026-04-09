<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * Upload a file and create a Document record.
     */
    public function upload(
        UploadedFile $file,
        int          $companyId,
        array        $meta = []
    ): Document {
        $disk = config('filesystems.default', 'local');
        $path = $this->buildPath($companyId, $file);

        Storage::disk($disk)->put($path, file_get_contents($file), [
            'visibility' => 'private',
        ]);

        $document = Document::create([
            'company_id'        => $companyId,
            'user_id'           => $meta['user_id']            ?? auth()->id(),
            'name'              => $meta['name']               ?? $file->getClientOriginalName(),
            'original_name'     => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType(),
            'disk'              => $disk,
            'path'              => $path,
            'size'              => $file->getSize(),
            'documentable_type' => $meta['documentable_type']  ?? null,
            'documentable_id'   => $meta['documentable_id']    ?? null,
            'is_private'        => $meta['is_private']         ?? true,
        ]);

        // Create initial version record
        DocumentVersion::create([
            'document_id'    => $document->id,
            'version_number' => 1,
            'path'           => $path,
            'size'           => $file->getSize(),
            'uploaded_by'    => auth()->id(),
            'notes'          => $meta['notes'] ?? 'Initial upload',
            'created_at'     => now(),
        ]);

        return $document;
    }

    /**
     * Upload a new version of an existing document.
     */
    public function uploadVersion(Document $document, UploadedFile $file, ?string $notes = null): DocumentVersion
    {
        $disk        = $document->disk;
        $path        = $this->buildPath($document->company_id, $file, 'versions');
        $nextVersion = ($document->versions()->max('version_number') ?? 0) + 1;

        Storage::disk($disk)->put($path, file_get_contents($file), ['visibility' => 'private']);

        // Update the document's active path to the new version
        $document->update(['path' => $path, 'size' => $file->getSize()]);

        return DocumentVersion::create([
            'document_id'    => $document->id,
            'version_number' => $nextVersion,
            'path'           => $path,
            'size'           => $file->getSize(),
            'uploaded_by'    => auth()->id(),
            'notes'          => $notes,
            'created_at'     => now(),
        ]);
    }

    /**
     * Get a temporary signed URL for downloading a private document.
     * S3: uses presigned URL. Local: returns storage URL.
     */
    public function getTemporaryUrl(Document $document, int $minutes = 5): string
    {
        if ($document->disk === 's3') {
            return Storage::disk('s3')->temporaryUrl($document->path, now()->addMinutes($minutes));
        }

        return Storage::disk($document->disk)->url($document->path);
    }

    /**
     * Delete document file from storage and soft-delete the record.
     */
    public function delete(Document $document): void
    {
        // Keep file on disk — soft delete only (for audit/recovery)
        $document->delete();
    }

    /**
     * Permanently remove file from storage and hard-delete record.
     */
    public function forceDelete(Document $document): void
    {
        Storage::disk($document->disk)->delete($document->path);

        foreach ($document->versions as $version) {
            Storage::disk($document->disk)->delete($version->path);
        }

        $document->forceDelete();
    }

    private function buildPath(int $companyId, UploadedFile $file, string $subDir = 'uploads'): string
    {
        $year  = now()->format('Y');
        $month = now()->format('m');
        $hash  = Str::random(16) . '.' . $file->getClientOriginalExtension();

        return "companies/{$companyId}/documents/{$subDir}/{$year}/{$month}/{$hash}";
    }
}
