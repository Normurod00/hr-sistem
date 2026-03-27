<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\EmploymentType;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ============ USERS ============

        // Admin
        $admin = new User([
            'name' => 'Администратор',
            'email' => 'admin@brb.uz',
            'password' => Hash::make('password'),
            'phone' => '+998 71 123 45 67',
        ]);
        $admin->role = UserRole::Admin;
        $admin->is_employee = true;
        $admin->save();

        // HR Manager
        $hr = new User([
            'name' => 'HR Менеджер',
            'email' => 'hr@brb.uz',
            'password' => Hash::make('password'),
            'phone' => '+998 71 234 56 78',
        ]);
        $hr->role = UserRole::Hr;
        $hr->is_employee = true;
        $hr->save();

        // Candidates
        $candidate1 = new User([
            'name' => 'Иванов Иван Петрович',
            'email' => 'ivanov@mail.uz',
            'password' => Hash::make('password'),
            'phone' => '+998 90 111 22 33',
        ]);
        $candidate1->role = UserRole::Candidate;
        $candidate1->save();

        $candidate2 = new User([
            'name' => 'Петрова Анна Сергеевна',
            'email' => 'petrova@mail.uz',
            'password' => Hash::make('password'),
            'phone' => '+998 91 222 33 44',
        ]);
        $candidate2->role = UserRole::Candidate;
        $candidate2->save();

        $candidate3 = new User([
            'name' => 'Сидоров Алексей Михайлович',
            'email' => 'sidorov@mail.uz',
            'password' => Hash::make('password'),
        ]);
        $candidate3->role = UserRole::Candidate;
        $candidate3->save();

        // ============ VACANCIES ============

        $vacancy1 = Vacancy::create([
            'title' => 'Senior Java Developer',
            'description' => "Мы ищем опытного Java-разработчика для работы над банковскими системами.\n\nОбязанности:\n- Разработка и поддержка backend-сервисов на Java\n- Проектирование архитектуры высоконагруженных систем\n- Code review и менторинг junior-разработчиков\n- Участие в планировании спринтов\n\nМы предлагаем:\n- Конкурентную заработную плату\n- Гибкий график работы\n- ДМС для сотрудников и членов семьи\n- Обучение и сертификации за счёт компании",
            'must_have_skills' => ['Java', 'Spring Boot', 'PostgreSQL', 'Microservices', 'REST API'],
            'nice_to_have_skills' => ['Kubernetes', 'Docker', 'Kafka', 'Redis'],
            'min_experience_years' => 5,
            'language_requirements' => [
                ['name' => 'Русский', 'level' => 'native'],
                ['name' => 'English', 'level' => 'B2'],
            ],
            'salary_min' => 15000000,
            'salary_max' => 25000000,
            'location' => 'Ташкент',
            'employment_type' => EmploymentType::FullTime,
            'is_active' => true,
            'created_by' => $hr->id,
        ]);

        $vacancy2 = Vacancy::create([
            'title' => 'Frontend React Developer',
            'description' => "Приглашаем Frontend-разработчика в команду интернет-банкинга.\n\nОбязанности:\n- Разработка пользовательских интерфейсов на React\n- Работа с REST API\n- Оптимизация производительности\n- Написание unit-тестов\n\nТребования:\n- Опыт работы с React от 2 лет\n- Знание TypeScript\n- Опыт работы с Redux/MobX",
            'must_have_skills' => ['React', 'TypeScript', 'JavaScript', 'HTML', 'CSS'],
            'nice_to_have_skills' => ['Redux', 'Next.js', 'Tailwind CSS', 'Jest'],
            'min_experience_years' => 2,
            'language_requirements' => [
                ['name' => 'English', 'level' => 'B1'],
            ],
            'salary_min' => 10000000,
            'salary_max' => 18000000,
            'location' => 'Ташкент / Удалённо',
            'employment_type' => EmploymentType::Remote,
            'is_active' => true,
            'created_by' => $hr->id,
        ]);

        $vacancy3 = Vacancy::create([
            'title' => 'Data Analyst',
            'description' => "Ищем аналитика данных для работы в департаменте риск-менеджмента.\n\nЗадачи:\n- Анализ финансовых данных\n- Построение отчётов и дашбордов\n- Выявление паттернов и аномалий\n- Подготовка рекомендаций для бизнеса",
            'must_have_skills' => ['SQL', 'Python', 'Excel', 'Power BI'],
            'nice_to_have_skills' => ['Tableau', 'Machine Learning', 'R'],
            'min_experience_years' => 1,
            'salary_min' => 8000000,
            'salary_max' => 14000000,
            'location' => 'Ташкент',
            'employment_type' => EmploymentType::FullTime,
            'is_active' => true,
            'created_by' => $hr->id,
        ]);

        $vacancy4 = Vacancy::create([
            'title' => 'DevOps Engineer',
            'description' => "Требуется DevOps инженер для автоматизации инфраструктуры.\n\nОбязанности:\n- Настройка CI/CD пайплайнов\n- Управление Kubernetes кластерами\n- Мониторинг и алертинг\n- Обеспечение отказоустойчивости сервисов",
            'must_have_skills' => ['Linux', 'Docker', 'Kubernetes', 'CI/CD', 'Terraform'],
            'nice_to_have_skills' => ['AWS', 'Ansible', 'Prometheus', 'Grafana'],
            'min_experience_years' => 3,
            'salary_min' => 12000000,
            'salary_max' => 20000000,
            'location' => 'Ташкент',
            'employment_type' => EmploymentType::FullTime,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $vacancy5 = Vacancy::create([
            'title' => 'Project Manager (IT)',
            'description' => "Ищем опытного Project Manager для управления IT-проектами банка.",
            'must_have_skills' => ['Agile', 'Scrum', 'Jira', 'Управление командой'],
            'nice_to_have_skills' => ['PMP', 'Confluence', 'MS Project'],
            'min_experience_years' => 4,
            'salary_min' => 15000000,
            'salary_max' => 22000000,
            'location' => 'Ташкент',
            'employment_type' => EmploymentType::FullTime,
            'is_active' => false, // неактивная
            'created_by' => $hr->id,
        ]);

        // ============ APPLICATIONS ============

        Application::create([
            'user_id' => $candidate1->id,
            'vacancy_id' => $vacancy1->id,
            'status' => ApplicationStatus::InReview,
            'match_score' => 78,
            'source' => 'website',
            'cover_letter' => 'Здравствуйте! Имею 6 лет опыта разработки на Java. Работал в банковской сфере.',
        ]);

        Application::create([
            'user_id' => $candidate2->id,
            'vacancy_id' => $vacancy2->id,
            'status' => ApplicationStatus::New,
            'match_score' => 85,
            'source' => 'website',
        ]);

        Application::create([
            'user_id' => $candidate3->id,
            'vacancy_id' => $vacancy1->id,
            'status' => ApplicationStatus::New,
            'match_score' => null,
            'source' => 'website',
        ]);

        Application::create([
            'user_id' => $candidate1->id,
            'vacancy_id' => $vacancy3->id,
            'status' => ApplicationStatus::Invited,
            'match_score' => 65,
            'source' => 'website',
        ]);

        $this->command->info('');
        $this->command->info('=== BRB HR Platform Seeded Successfully ===');
        $this->command->info('');
        $this->command->info('Test accounts:');
        $this->command->info('  Admin:     admin@brb.uz     / password');
        $this->command->info('  HR:        hr@brb.uz        / password');
        $this->command->info('  Candidate: ivanov@mail.uz   / password');
        $this->command->info('');
    }
}
