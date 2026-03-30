<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\EmployeeRole;
use App\Enums\EmployeeStatus;
use App\Enums\UserRole;
use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Список пользователей (сотрудники, HR, администраторы)
     */
    public function index(Request $request): View
    {
        $query = User::query()
            ->whereIn('role', [UserRole::Employee, UserRole::Hr, UserRole::Admin])
            ->with('employeeProfile')
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->paginate(20)->withQueryString();

        $roles = [UserRole::Employee, UserRole::Hr, UserRole::Admin];

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Форма создания пользователя
     */
    public function create(): View
    {
        $roles = [UserRole::Employee, UserRole::Hr, UserRole::Admin];
        $employeeRoles = EmployeeRole::cases();
        $managers = EmployeeProfile::with('user')->active()->get();

        return view('admin.users.create', compact('roles', 'employeeRoles', 'managers'));
    }

    /**
     * Сохранение нового пользователя
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string'],
            'department' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'employee_role' => ['nullable', 'string'],
            'manager_id' => ['nullable', 'exists:employee_profiles,id'],
        ], [
            'name.required' => 'Введите имя',
            'email.required' => 'Введите email',
            'email.unique' => 'Этот email уже занят',
            'password.required' => 'Введите пароль',
            'password.confirmed' => 'Пароли не совпадают',
            'role.required' => 'Выберите роль',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = new User([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
            ]);
            $user->role = $validated['role'];
            $user->is_employee = in_array($validated['role'], ['employee', 'hr', 'admin']);
            $user->save();

            if (in_array($validated['role'], ['employee', 'hr', 'admin'])) {
                $employeeRole = match ($validated['role']) {
                    'hr' => EmployeeRole::Hr,
                    'admin' => EmployeeRole::SysAdmin,
                    default => EmployeeRole::from($validated['employee_role'] ?? 'employee'),
                };

                EmployeeProfile::create([
                    'user_id' => $user->id,
                    'employee_number' => 'EMP-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                    'department' => $validated['department'] ?? null,
                    'position' => $validated['position'] ?? null,
                    'role' => $employeeRole,
                    'manager_id' => $validated['manager_id'] ?? null,
                    'hire_date' => now(),
                    'status' => EmployeeStatus::Active,
                ]);
            }

            return $user;
        });

        return redirect()->route('admin.users.index')
            ->with('success', "Пользователь {$user->name} создан. Логин: {$user->email}");
    }

    /**
     * Просмотр пользователя
     */
    public function show(User $user): View
    {
        $user->load(['applications.vacancy', 'candidateProfile', 'employeeProfile.manager.user']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Форма редактирования
     */
    public function edit(User $user): View
    {
        $user->load('employeeProfile');
        $roles = [UserRole::Employee, UserRole::Hr, UserRole::Admin];
        $employeeRoles = EmployeeRole::cases();
        $managers = EmployeeProfile::with('user')->active()->where('user_id', '!=', $user->id)->get();

        return view('admin.users.edit', compact('user', 'roles', 'employeeRoles', 'managers'));
    }

    /**
     * Обновление пользователя
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string'],
            'department' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'employee_role' => ['nullable', 'string'],
            'manager_id' => ['nullable', 'exists:employee_profiles,id'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'role' => $validated['role'],
                'is_employee' => in_array($validated['role'], ['employee', 'hr', 'admin']),
            ]);

            if (in_array($validated['role'], ['employee', 'hr', 'admin'])) {
                $employeeRole = match ($validated['role']) {
                    'hr' => EmployeeRole::Hr,
                    'admin' => EmployeeRole::SysAdmin,
                    default => EmployeeRole::from($validated['employee_role'] ?? 'employee'),
                };

                $user->employeeProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'department' => $validated['department'] ?? null,
                        'position' => $validated['position'] ?? null,
                        'role' => $employeeRole,
                        'manager_id' => $validated['manager_id'] ?? null,
                    ]
                );
            }
        });

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Пользователь обновлён.');
    }

    /**
     * Сброс пароля
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Пароль сброшен.');
    }

    /**
     * Удаление пользователя
     */
    public function destroy(User $user): RedirectResponse
    {
        // Нельзя удалить себя
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Вы не можете удалить свой аккаунт.');
        }

        // Нельзя удалить, если есть заявки
        if ($user->applications()->exists()) {
            return back()->with('error', 'Невозможно удалить пользователя с заявками.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь удалён.');
    }
}
