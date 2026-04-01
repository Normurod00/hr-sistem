<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(): View
    {
        $employee = auth()->user()->employeeProfile;

        if (!$employee) {
            abort(403, 'Профиль сотрудника не найден');
        }

        $documents = $employee->documents()->latest()->paginate(20);

        return view('employee.documents.index', compact('documents', 'employee'));
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = auth()->user()->employeeProfile;

        if (!$employee) {
            abort(403);
        }

        $request->validate([
            'document_type' => ['required', 'in:contract,diploma,certificate,id_document,medical,other'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, EmployeeDocument::getAllowedExtensions())) {
            return back()->with('error', 'Неподдерживаемый формат файла.');
        }

        $path = $file->store('public/employee-documents');

        EmployeeDocument::create([
            'employee_profile_id' => $employee->id,
            'uploaded_by' => auth()->id(),
            'document_type' => $request->input('document_type'),
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => EmployeeDocument::STATUS_PENDING,
        ]);

        return back()->with('success', 'Документ загружен. AI обработка начнётся автоматически.');
    }
}
