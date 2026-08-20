<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\SecurityEventService;
use App\Services\UploadSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(protected UploadSecurityService $uploadSecurity) {}

    public function index(Request $request): View
    {
        $this->authorize('configure');

        $documents = Document::query()
            ->with('uploadedBy:id,name,email')
            ->where('company_id', CompanyContext::id())
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.documents.index', [
            'documents' => $documents,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('configure');

        $data = $request->validate([
            'file' => ['required', 'file'],
            'title' => ['required', 'string', 'max:255'],
            'documentable_type' => ['nullable', 'string', 'max:120'],
            'documentable_id' => ['nullable', 'integer'],
        ]);

        $file = $data['file'];
        $documentableType = $data['documentable_type'] ?? null;

        try {
            $extension = $this->uploadSecurity->validate($file);
        } catch (ValidationException $e) {
            SecurityEventService::record('upload', 'upload_blocked', "Blocked upload attempt: {$file->getClientOriginalName()}", 'high');

            return back()->withErrors($e->errors())->withInput();
        }

        $safeName = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs('documents/'.now()->format('Y/m'), $safeName, 'local');

        if (! $path) {
            return back()->with('error', 'Unable to store the file.');
        }

        $scan = $this->uploadSecurity->scan(storage_path('app/private/'.$path));
        $quarantined = $scan['status'] === 'infected';

        $document = Document::create([
            'company_id' => CompanyContext::id(),
            'type' => 'manual',
            'documentable_type' => $documentableType,
            'documentable_id' => $documentableType ? ($data['documentable_id'] ?? null) : null,
            'title' => $data['title'],
            'original_name' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => mime_content_type(storage_path('app/private/'.$path)),
            'size' => $file->getSize(),
            'status' => $quarantined ? 'rejected' : 'new',
            'uploaded_by' => auth()->id(),
            'scan_status' => $scan['status'],
            'scan_result' => $scan['output'],
            'scanned_at' => now(),
            'is_quarantined' => $quarantined,
        ]);

        AuditLogger::log('upload', 'documents', $document);

        if ($quarantined) {
            SecurityEventService::record('upload', 'upload_quarantined', "File quarantined: {$file->getClientOriginalName()}", 'critical');

            return back()->with('error', 'The uploaded file failed the security scan and was quarantined.');
        }

        SecurityEventService::record('upload', 'upload_clean', "File uploaded and scanned clean: {$file->getClientOriginalName()}", 'info');

        return back()->with('success', 'Document uploaded and scanned.');
    }

    public function download(Document $document)
    {
        $this->authorize('configure');

        if ((int) $document->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        if ($document->is_quarantined || $document->scan_status === 'infected') {
            abort(403, 'This document is quarantined and cannot be downloaded.');
        }

        AuditLogger::log('download', 'documents', $document);

        return Storage::disk('local')->download(
            $document->storage_path,
            $document->original_name
        );
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('configure');

        if ((int) $document->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        Storage::disk('local')->delete($document->storage_path);
        $document->delete();
        AuditLogger::deleted($document);

        return back()->with('success', 'Document deleted.');
    }
}
