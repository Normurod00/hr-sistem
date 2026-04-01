<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessEmployeeDocument;
use App\Models\EmployeeDocument;
use App\Models\EmployeeProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeDocumentController extends Controller
{
    /**
     * Список документов сотрудников с аналитикой
     */
    public function index(Request $request): View
    {
        $query = EmployeeDocument::with(['employeeProfile.user', 'uploader'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_profile_id', $request->employee_id);
        }

        $documents = $query->paginate(20)->withQueryString();

        // KPI stats
        $total = EmployeeDocument::count();
        $parsed = EmployeeDocument::where('status', 'parsed')->count();
        $pending = EmployeeDocument::where('status', 'pending')->count();
        $processing = EmployeeDocument::where('status', 'processing')->count();
        $failed = EmployeeDocument::where('status', 'failed')->count();

        $kpi = [
            'total' => $total,
            'parsed' => $parsed,
            'pending' => $pending,
            'processing' => $processing,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($parsed / $total) * 100, 1) : 0,
        ];

        // Type distribution
        $typeDistribution = DB::table('employee_documents')
            ->select('document_type', DB::raw('COUNT(*) as count'))
            ->groupBy('document_type')
            ->orderByDesc('count')
            ->get();

        // Status distribution
        $statusDistribution = DB::table('employee_documents')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        $employees = EmployeeProfile::active()->with('user')->get();

        return view('admin.employee-documents.index', compact(
            'documents', 'kpi', 'typeDistribution', 'statusDistribution', 'employees'
        ));
    }

    /**
     * Загрузка документа сотрудника (от admin)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'document_type' => ['required', 'in:contract,diploma,certificate,id_document,medical,other'],
            'file' => ['required', 'file', 'max:10240'], // 10 MB
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, EmployeeDocument::getAllowedExtensions())) {
            return back()->with('error', 'Неподдерживаемый формат файла.');
        }

        $path = $file->store('public/employee-documents');

        EmployeeDocument::create([
            'employee_profile_id' => $validated['employee_profile_id'],
            'uploaded_by' => auth()->id(),
            'document_type' => $validated['document_type'],
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => EmployeeDocument::STATUS_PENDING,
        ]);

        return back()->with('success', 'Документ загружен и отправлен на обработку.');
    }

    /**
     * Просмотр результатов анализа документа
     */
    public function show(EmployeeDocument $document): View
    {
        $document->load(['employeeProfile.user', 'uploader']);

        return view('admin.employee-documents.show', compact('document'));
    }

    /**
     * Переобработка документа
     */
    public function reprocess(EmployeeDocument $document): RedirectResponse
    {
        $document->update([
            'status' => EmployeeDocument::STATUS_PENDING,
            'error_message' => null,
            'analysis_result' => null,
            'parsed_text' => null,
            'processed_at' => null,
        ]);

        ProcessEmployeeDocument::dispatch($document);

        return back()->with('success', 'Документ отправлен на повторную обработку.');
    }

    /**
     * Удаление документа
     */
    public function destroy(EmployeeDocument $document): RedirectResponse
    {
        $fullPath = $document->getFullPath();
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $document->delete();

        return back()->with('success', 'Документ удалён.');
    }
}
