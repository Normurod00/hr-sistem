<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PolicyController extends Controller
{
    /**
     * Список политик
     */
    public function index(Request $request): View
    {
        $query = Policy::query();

        if ($category = $request->query('category')) {
            $query->byCategory($category);
        }

        if ($search = $request->query('q')) {
            $query->search($search);
        }

        $policies = $query->orderBy('effective_date', 'desc')->paginate(20);

        return view('admin.policies.index', [
            'policies' => $policies,
            'categories' => Policy::getCategories(),
            'currentCategory' => $category,
            'searchQuery' => $search,
        ]);
    }

    /**
     * Форма создания
     */
    public function create(): View
    {
        return view('admin.policies.create', [
            'categories' => Policy::getCategories(),
        ]);
    }

    /**
     * Сохранить политику
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:50',
            'code' => 'required|string|max:50|unique:policies,code',
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'required|string',
            'file' => 'nullable|file|mimes:pdf|max:10240',
            'effective_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:effective_date',
            'tags' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Обрабатываем теги
        $validated['tags'] = $validated['tags']
            ? array_map('trim', explode(',', $validated['tags']))
            : [];

        // Загружаем файл
        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('policies', 'public');
        }

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $validated['is_active'] ?? true;

        unset($validated['file']);

        Policy::create($validated);

        return redirect()->route('admin.policies.index')
            ->with('success', 'Политика успешно создана');
    }

    /**
     * Показать политику
     */
    public function show(Policy $policy): View
    {
        return view('admin.policies.show', [
            'policy' => $policy,
        ]);
    }

    /**
     * Форма редактирования
     */
    public function edit(Policy $policy): View
    {
        return view('admin.policies.edit', [
            'policy' => $policy,
            'categories' => Policy::getCategories(),
        ]);
    }

    /**
     * Обновить политику
     */
    public function update(Request $request, Policy $policy)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:50',
            'code' => 'required|string|max:50|unique:policies,code,' . $policy->id,
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'required|string',
            'file' => 'nullable|file|mimes:pdf|max:10240',
            'effective_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:effective_date',
            'tags' => 'nullable|string',
            'is_active' => 'boolean',
            'version' => 'nullable|string|max:20',
        ]);

        // Обрабатываем теги
        $validated['tags'] = $validated['tags']
            ? array_map('trim', explode(',', $validated['tags']))
            : [];

        // Загружаем файл
        if ($request->hasFile('file')) {
            // Удаляем старый файл
            if ($policy->file_path) {
                \Storage::disk('public')->delete($policy->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('policies', 'public');
        }

        $validated['updated_by'] = auth()->id();
        $validated['is_active'] = $validated['is_active'] ?? false;

        unset($validated['file']);

        $policy->update($validated);

        return redirect()->route('admin.policies.index')
            ->with('success', 'Политика успешно обновлена');
    }

    /**
     * Удалить политику
     */
    public function destroy(Policy $policy)
    {
        // Мягкое удаление
        $policy->delete();

        return redirect()->route('admin.policies.index')
            ->with('success', 'Политика удалена');
    }

    /**
     * Переключить статус активности
     */
    public function toggle(Policy $policy)
    {
        $policy->update(['is_active' => !$policy->is_active]);

        return back()->with('success', 'Статус политики изменён');
    }
}
