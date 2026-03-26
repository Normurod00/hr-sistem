<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\FileType;
use App\Jobs\ProcessApplicationFile;
use App\Models\Application;
use App\Models\ApplicationFile;
use App\Models\CandidateResume;
use App\Models\Vacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * Форма подачи заявки на вакансию
     */
    public function create(Vacancy $vacancy): View
    {
        // Проверяем, что вакансия активна
        if (!$vacancy->is_active) {
            abort(404);
        }

        // Проверяем, не подавал ли уже заявку
        $existingApplication = $vacancy->applications()
            ->where('user_id', auth()->id())
            ->first();

        if ($existingApplication) {
            return redirect()->route('profile.applications.show', $existingApplication)
                ->with('info', 'Вы уже подали заявку на эту вакансию.');
        }

        return view('applications.create', compact('vacancy'));
    }

    /**
     * Сохранение заявки
     */
    public function store(Request $request, Vacancy $vacancy): RedirectResponse
    {
        // Проверяем, что вакансия активна
        if (!$vacancy->is_active) {
            abort(404);
        }

        // Проверяем, не подавал ли уже заявку
        $existingApplication = $vacancy->applications()
            ->where('user_id', auth()->id())
            ->first();

        if ($existingApplication) {
            return redirect()->route('profile.applications.show', $existingApplication)
                ->with('info', 'Вы уже подали заявку на эту вакансию.');
        }

        $resumeType = $request->input('resume_type', 'upload');

        // Валидация в зависимости от типа
        if ($resumeType === 'upload') {
            $request->validate([
                'cover_letter' => ['nullable', 'string', 'max:5000'],
                'resume' => ['required', 'file', 'mimes:pdf,doc,docx,txt,rtf', 'max:10240'],
            ], [
                'resume.required' => 'Прикрепите резюме',
                'resume.mimes' => 'Резюме должно быть в формате PDF, DOC, DOCX, TXT или RTF',
                'resume.max' => 'Размер файла не должен превышать 10 МБ',
                'cover_letter.max' => 'Сопроводительное письмо не должно превышать 5000 символов',
            ]);
        } else {
            $request->validate([
                'full_name' => ['required', 'string', 'max:255'],
                'birth_date' => ['required', 'date'],
                'phone' => ['required', 'string', 'max:50'],
                'email' => ['required', 'email', 'max:255'],
                'city' => ['required', 'string', 'max:255'],
                'citizenship' => ['nullable', 'string', 'max:255'],
                'desired_position' => ['nullable', 'string', 'max:255'],
                'desired_salary' => ['nullable', 'string', 'max:100'],
                'education' => ['nullable', 'array'],
                'education.*.level' => ['nullable', 'string'],
                'education.*.year' => ['nullable', 'integer', 'min:1970', 'max:2030'],
                'education.*.institution' => ['nullable', 'string', 'max:500'],
                'education.*.speciality' => ['nullable', 'string', 'max:500'],
                'experience' => ['nullable', 'array'],
                'experience.*.company' => ['nullable', 'string', 'max:255'],
                'experience.*.position' => ['nullable', 'string', 'max:255'],
                'experience.*.start_date' => ['nullable', 'string'],
                'experience.*.end_date' => ['nullable', 'string'],
                'experience.*.current' => ['nullable'],
                'experience.*.description' => ['nullable', 'string', 'max:2000'],
                'skills' => ['nullable', 'string', 'max:1000'],
                'languages' => ['nullable', 'array'],
                'languages.*.name' => ['nullable', 'string', 'max:100'],
                'languages.*.level' => ['nullable', 'string', 'max:50'],
                'about' => ['nullable', 'string', 'max:3000'],
                'cover_letter' => ['nullable', 'string', 'max:5000'],
            ], [
                'full_name.required' => 'Укажите ваше ФИО',
                'birth_date.required' => 'Укажите дату рождения',
                'phone.required' => 'Укажите номер телефона',
                'email.required' => 'Укажите email',
                'city.required' => 'Укажите город проживания',
            ]);
        }

        // Создаём заявку
        $application = Application::create([
            'user_id' => auth()->id(),
            'vacancy_id' => $vacancy->id,
            'status' => ApplicationStatus::New,
            'cover_letter' => $request->input('cover_letter'),
            'source' => 'website',
        ]);

        if ($resumeType === 'upload') {
            // Загружаем резюме файлом
            if ($request->hasFile('resume')) {
                $file = $request->file('resume');
                $path = $file->store('resumes/' . date('Y/m'), 'public');

                $applicationFile = ApplicationFile::create([
                    'application_id' => $application->id,
                    'file_type' => FileType::Resume,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'is_parsed' => false,
                ]);

                // Запускаем обработку файла в очереди
                ProcessApplicationFile::dispatch($applicationFile);
            }
        } else {
            // Создаём резюме из формы
            $resumeData = $this->buildResumeData($request);

            // Сохраняем резюме кандидата
            $candidateResume = CandidateResume::create([
                'user_id' => auth()->id(),
                'application_id' => $application->id,
                'full_name' => $request->input('full_name'),
                'birth_date' => $request->input('birth_date'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'city' => $request->input('city'),
                'citizenship' => $request->input('citizenship'),
                'desired_position' => $request->input('desired_position'),
                'desired_salary' => $request->input('desired_salary'),
                'education' => $request->input('education', []),
                'experience' => $request->input('experience', []),
                'skills' => $request->input('skills'),
                'languages' => $request->input('languages', []),
                'about' => $request->input('about'),
            ]);

            // Генерируем текстовый файл резюме
            $resumeText = $this->generateResumeText($resumeData);
            $fileName = 'resume_' . auth()->id() . '_' . time() . '.txt';
            $path = 'resumes/' . date('Y/m') . '/' . $fileName;

            Storage::disk('public')->put($path, $resumeText);

            $applicationFile = ApplicationFile::create([
                'application_id' => $application->id,
                'file_type' => FileType::Resume,
                'path' => $path,
                'original_name' => 'Резюме_' . $request->input('full_name') . '.txt',
                'mime_type' => 'text/plain',
                'size' => strlen($resumeText),
                'is_parsed' => false,
            ]);

            // Запускаем обработку файла в очереди
            ProcessApplicationFile::dispatch($applicationFile);
        }

        // Перенаправляем на тест
        return redirect()->route('tests.show', $application)
            ->with('success', 'Заявка успешно отправлена! Пройдите тест для оценки ваших знаний.');
    }

    /**
     * Собирает данные резюме из формы
     */
    private function buildResumeData(Request $request): array
    {
        return [
            'full_name' => $request->input('full_name'),
            'birth_date' => $request->input('birth_date'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'city' => $request->input('city'),
            'citizenship' => $request->input('citizenship'),
            'desired_position' => $request->input('desired_position'),
            'desired_salary' => $request->input('desired_salary'),
            'education' => array_filter($request->input('education', []), fn($edu) => !empty($edu['institution']) || !empty($edu['speciality'])),
            'experience' => array_filter($request->input('experience', []), fn($exp) => !empty($exp['company']) || !empty($exp['position'])),
            'skills' => $request->input('skills'),
            'languages' => array_filter($request->input('languages', []), fn($lang) => !empty($lang['name'])),
            'about' => $request->input('about'),
        ];
    }

    /**
     * Генерирует текст резюме из данных формы
     */
    private function generateResumeText(array $data): string
    {
        $lines = [];

        // Заголовок
        $lines[] = '=' . str_repeat('=', 50);
        $lines[] = 'РЕЗЮМЕ';
        $lines[] = '=' . str_repeat('=', 50);
        $lines[] = '';

        // Личные данные
        $lines[] = '--- ЛИЧНЫЕ ДАННЫЕ ---';
        $lines[] = '';
        $lines[] = 'ФИО: ' . ($data['full_name'] ?? '');

        if (!empty($data['birth_date'])) {
            $birthDate = \Carbon\Carbon::parse($data['birth_date']);
            $age = $birthDate->age;
            $lines[] = 'Дата рождения: ' . $birthDate->format('d.m.Y') . ' (' . $age . ' лет)';
        }

        $lines[] = 'Телефон: ' . ($data['phone'] ?? '');
        $lines[] = 'Email: ' . ($data['email'] ?? '');
        $lines[] = 'Город: ' . ($data['city'] ?? '');

        if (!empty($data['citizenship'])) {
            $lines[] = 'Гражданство: ' . $data['citizenship'];
        }
        $lines[] = '';

        // Желаемая должность
        if (!empty($data['desired_position']) || !empty($data['desired_salary'])) {
            $lines[] = '--- ЖЕЛАЕМАЯ ДОЛЖНОСТЬ ---';
            $lines[] = '';
            if (!empty($data['desired_position'])) {
                $lines[] = 'Должность: ' . $data['desired_position'];
            }
            if (!empty($data['desired_salary'])) {
                $lines[] = 'Ожидаемая зарплата: ' . $data['desired_salary'];
            }
            $lines[] = '';
        }

        // Образование
        if (!empty($data['education'])) {
            $lines[] = '--- ОБРАЗОВАНИЕ ---';
            $lines[] = '';

            $levelLabels = [
                'secondary' => 'Среднее',
                'vocational' => 'Среднее специальное',
                'incomplete_higher' => 'Неоконченное высшее',
                'bachelor' => 'Бакалавр',
                'master' => 'Магистр',
                'phd' => 'Доктор наук',
            ];

            foreach ($data['education'] as $edu) {
                if (empty($edu['institution']) && empty($edu['speciality'])) continue;

                $eduLine = '';
                if (!empty($edu['year'])) {
                    $eduLine .= $edu['year'] . ' г. - ';
                }
                if (!empty($edu['institution'])) {
                    $eduLine .= $edu['institution'];
                }
                if (!empty($edu['speciality'])) {
                    $eduLine .= ', ' . $edu['speciality'];
                }
                if (!empty($edu['level']) && isset($levelLabels[$edu['level']])) {
                    $eduLine .= ' (' . $levelLabels[$edu['level']] . ')';
                }

                $lines[] = $eduLine;
            }
            $lines[] = '';
        }

        // Опыт работы
        if (!empty($data['experience'])) {
            $lines[] = '--- ОПЫТ РАБОТЫ ---';
            $lines[] = '';

            foreach ($data['experience'] as $exp) {
                if (empty($exp['company']) && empty($exp['position'])) continue;

                // Период работы
                $period = '';
                if (!empty($exp['start_date'])) {
                    $startDate = \Carbon\Carbon::createFromFormat('Y-m', $exp['start_date']);
                    $period = $startDate->format('m.Y');

                    if (!empty($exp['current'])) {
                        $period .= ' - по настоящее время';
                    } elseif (!empty($exp['end_date'])) {
                        $endDate = \Carbon\Carbon::createFromFormat('Y-m', $exp['end_date']);
                        $period .= ' - ' . $endDate->format('m.Y');
                    }
                }

                $lines[] = ($exp['company'] ?? 'Компания не указана');
                if (!empty($exp['position'])) {
                    $lines[] = 'Должность: ' . $exp['position'];
                }
                if ($period) {
                    $lines[] = 'Период: ' . $period;
                }
                if (!empty($exp['description'])) {
                    $lines[] = 'Обязанности: ' . $exp['description'];
                }
                $lines[] = '';
            }
        }

        // Навыки
        if (!empty($data['skills'])) {
            $lines[] = '--- НАВЫКИ ---';
            $lines[] = '';
            $lines[] = $data['skills'];
            $lines[] = '';
        }

        // Языки
        if (!empty($data['languages'])) {
            $lines[] = '--- ЗНАНИЕ ЯЗЫКОВ ---';
            $lines[] = '';

            $levelLabels = [
                'native' => 'Родной',
                'fluent' => 'Свободно',
                'advanced' => 'Продвинутый',
                'intermediate' => 'Средний',
                'basic' => 'Базовый',
            ];

            foreach ($data['languages'] as $lang) {
                if (empty($lang['name'])) continue;

                $langLine = $lang['name'];
                if (!empty($lang['level']) && isset($levelLabels[$lang['level']])) {
                    $langLine .= ' - ' . $levelLabels[$lang['level']];
                }
                $lines[] = $langLine;
            }
            $lines[] = '';
        }

        // О себе
        if (!empty($data['about'])) {
            $lines[] = '--- О СЕБЕ ---';
            $lines[] = '';
            $lines[] = $data['about'];
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Мои заявки (для кандидата)
     */
    public function myApplications(): View
    {
        $applications = Application::query()
            ->where('user_id', auth()->id())
            ->with(['vacancy', 'files'])
            ->latest()
            ->paginate(10);

        return view('profile.applications.index', compact('applications'));
    }

    /**
     * Просмотр своей заявки
     */
    public function showMyApplication(Application $application): View
    {
        // Проверяем, что заявка принадлежит текущему пользователю
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        $application->load(['vacancy', 'files', 'analysis', 'candidateTest']);

        return view('profile.applications.show', compact('application'));
    }
}
