# HR AI Server

AI сервер для HR-робота на базе FastAPI + локальных LLM.

## Возможности

- **Парсинг резюме** — извлечение структурированного профиля из PDF/DOCX/TXT
- **Анализ кандидатов** — сильные/слабые стороны, риски, рекомендации
- **Match Score** — расчёт соответствия кандидата вакансии (0-100)
- **Генерация вопросов** — вопросы для интервью под конкретного кандидата
- **Письма-отказы** — генерация вежливых отказов

## Быстрый старт

### 1. Установка зависимостей

```bash
cd ai_server
pip install -r requirements.txt
```

### 2. Настройка модели

**Вариант A: Локальная модель (рекомендуется)**

```bash
# Создайте папку models
mkdir models

# Скачайте модель с HuggingFace:
# https://huggingface.co/TheBloke/Mistral-7B-Instruct-v0.2-GGUF
# Файл: mistral-7b-instruct-v0.2.Q4_K_M.gguf (~4.4 GB)
```

**Вариант B: Ollama**

```bash
# Установите Ollama: https://ollama.ai
ollama pull mistral
```

В `config.yaml`:
```yaml
llm:
  provider: ollama
```

**Вариант C: OpenAI API**

В `.env`:
```
OPENAI_API_KEY=sk-your-key-here
```

В `config.yaml`:
```yaml
llm:
  provider: openai
```

### 3. Запуск сервера

```bash
python run.py
# или
python main.py
```

Сервер запустится на `http://127.0.0.1:8080`

## API Endpoints

### Health Check
```
GET /health
```

### Парсинг резюме

**Из текста:**
```
POST /parse-resume
Content-Type: application/json

{
  "text": "Текст резюме..."
}
```

**Из файла (upload):**
```
POST /upload-resume
Content-Type: multipart/form-data

file: resume.pdf
```

**Из base64:**
```
POST /parse-file
Content-Type: application/json

{
  "file_content": "base64...",
  "filename": "resume.pdf"
}
```

### Анализ кандидата
```
POST /analyze
Content-Type: application/json

{
  "profile": {
    "position_title": "Senior Python Developer",
    "years_of_experience": 5,
    "skills": [{"name": "Python", "level": "strong"}]
  },
  "vacancy": {
    "title": "Backend Developer",
    "must_have_skills": ["Python", "FastAPI"],
    "nice_to_have_skills": ["Docker", "PostgreSQL"],
    "min_experience_years": 3
  }
}
```

### Match Score
```
POST /match-score
Content-Type: application/json

{
  "profile": {...},
  "vacancy": {...}
}
```

### Вопросы для интервью
```
POST /questions
Content-Type: application/json

{
  "profile": {...},
  "vacancy": {...},
  "count": 10,
  "focus_areas": ["Python", "System Design"]
}
```

## Конфигурация

Файл `config.yaml`:

```yaml
server:
  host: "127.0.0.1"
  port: 8080
  debug: true

llm:
  provider: "local"  # local, ollama, openai

  local:
    model_path: "models/mistral-7b-instruct-v0.2.Q4_K_M.gguf"
    model_type: "mistral"
    threads: 4

  ollama:
    host: "http://127.0.0.1:11434"
    model: "mistral"

  openai:
    model: "gpt-4o-mini"

hr:
  match_weights:
    must_have_skills: 0.5
    nice_to_have_skills: 0.3
    experience: 0.2
```

## Интеграция с Laravel

В Laravel проекте используйте HTTP клиент:

```php
// config/services.php
'hr_ai' => [
    'url' => env('HR_AI_URL', 'http://127.0.0.1:8080'),
    'timeout' => 120,
],

// app/Services/AiClient.php
use Illuminate\Support\Facades\Http;

class AiClient
{
    public function parseResume(string $text): array
    {
        $response = Http::timeout(120)
            ->post(config('services.hr_ai.url') . '/parse-resume', [
                'text' => $text
            ]);

        return $response->json();
    }

    public function analyzeCandidate(array $profile, array $vacancy): array
    {
        $response = Http::timeout(120)
            ->post(config('services.hr_ai.url') . '/analyze', [
                'profile' => $profile,
                'vacancy' => $vacancy
            ]);

        return $response->json();
    }
}
```

## Структура проекта

```
ai_server/
├── core/
│   ├── __init__.py
│   ├── llm_engine.py      # LLM провайдеры
│   ├── document_parser.py  # Парсинг PDF/DOCX
│   ├── hr_analyzer.py      # HR анализ
│   └── models.py           # Pydantic модели
├── models/                  # Папка для GGUF моделей
├── temp/                    # Временные файлы
├── logs/                    # Логи
├── config.yaml
├── requirements.txt
├── main.py                  # FastAPI приложение
├── run.py                   # Launcher
└── README.md
```

## Производительность

| Операция | CPU (8 cores) | GPU (RTX 3060) |
|----------|---------------|----------------|
| Парсинг резюме | 15-30 сек | 3-5 сек |
| Анализ кандидата | 20-40 сек | 5-10 сек |
| Match Score | < 1 сек | < 1 сек |

## Лицензия

MIT
