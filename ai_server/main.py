"""
HR AI Server - Main Application
FastAPI сервер для HR-робота (Rule-Based версия без внешних LLM)
"""

import os
import sys
import logging
import yaml
import asyncio
from datetime import datetime
from typing import Optional, List
from contextlib import asynccontextmanager
from concurrent.futures import ThreadPoolExecutor

from fastapi import FastAPI, HTTPException, UploadFile, File, Request
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from slowapi import Limiter, _rate_limit_exceeded_handler
from slowapi.util import get_remote_address
from slowapi.errors import RateLimitExceeded

# Thread pool для параллельной обработки
executor = ThreadPoolExecutor(max_workers=4)

# Добавляем путь к модулям
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from core.document_parser import DocumentParser
from core.rule_based_analyzer import RuleBasedAnalyzer
from core.employee_endpoints import router as employee_router
from core.models import (
    ParseResumeRequest, ParseResumeResponse,
    AnalyzeRequest, AnalyzeResponse,
    MatchScoreRequest, MatchScoreResponse,
    GenerateQuestionsRequest, GenerateQuestionsResponse,
    ParseFileRequest, HealthResponse
)

# ============== Конфигурация ==============

def load_config(config_path: str = "config.yaml") -> dict:
    """Загрузка конфигурации"""
    if os.path.exists(config_path):
        with open(config_path, 'r', encoding='utf-8') as f:
            return yaml.safe_load(f)
    return {}

CONFIG = load_config()

# Настройка логирования
logging.basicConfig(
    level=getattr(logging, CONFIG.get('logging', {}).get('level', 'INFO')),
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# ============== Глобальные объекты ==============

doc_parser: Optional[DocumentParser] = None
hr_analyzer: Optional[RuleBasedAnalyzer] = None


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Lifecycle управление"""
    global doc_parser, hr_analyzer

    logger.info("Starting HR AI Server (Rule-Based)...")

    # Инициализация компонентов
    doc_parser = DocumentParser(CONFIG.get('documents', {}))
    hr_analyzer = RuleBasedAnalyzer(CONFIG.get('hr', {}))

    logger.info("HR AI Server started successfully!")
    logger.info("Using Rule-Based analyzer (no external LLM required)")

    yield

    logger.info("Shutting down HR AI Server...")


# ============== Rate Limiting (НОВОЕ) ==============

limiter = Limiter(key_func=get_remote_address)

# ============== FastAPI Application ==============

app = FastAPI(
    title="HR AI Server",
    description="AI сервер для HR-робота: парсинг резюме, анализ кандидатов, match score (Rule-Based)",
    version="2.0.0",
    lifespan=lifespan
)

# Rate Limiter
app.state.limiter = limiter
app.add_exception_handler(RateLimitExceeded, _rate_limit_exceeded_handler)

# CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=CONFIG.get('server', {}).get('cors_origins', []),
    allow_credentials=True,
    allow_methods=["GET", "POST", "OPTIONS"],
    allow_headers=["Authorization", "Content-Type", "Accept", "X-CSRF-TOKEN"],
)

# ============== Employee AI Router ==============
app.include_router(employee_router)

# ============== Эндпоинты ==============

@app.get("/", tags=["Health"])
async def root():
    """Корневой эндпоинт"""
    return {
        "service": "HR AI Server",
        "status": "running",
        "version": "2.1.0",
        "mode": "rule-based",
        "modules": ["candidate", "employee"]
    }


@app.get("/health", response_model=HealthResponse, tags=["Health"])
async def health_check():
    """Проверка здоровья сервера с диагностикой"""
    import psutil

    # Check OCR availability
    ocr_ok = False
    try:
        import pytesseract
        pytesseract.get_tesseract_version()
        ocr_ok = True
    except Exception:
        pass

    return HealthResponse(
        status="ok",
        version="2.1.0",
        llm_provider="rule-based",
        llm_model="internal",
        timestamp=datetime.now(),
        details={
            "ocr_available": ocr_ok,
            "analyzer_ready": hr_analyzer is not None,
            "parser_ready": doc_parser is not None,
            "memory_mb": round(psutil.Process().memory_info().rss / 1024 / 1024, 1),
            "cpu_percent": psutil.cpu_percent(interval=0),
        }
    )


@app.post("/parse-resume", response_model=ParseResumeResponse, tags=["Resume"])
@limiter.limit("30/minute")  # Максимум 30 запросов в минуту
async def parse_resume(request: Request, parse_request: ParseResumeRequest):
    """
    Парсинг текста резюме в структурированный профиль

    - **text**: Текст резюме (минимум 50 символов)

    Возвращает структурированный профиль кандидата с навыками,
    опытом, образованием и контактной информацией.
    """
    try:
        profile = hr_analyzer.parse_resume(parse_request.text)
        return ParseResumeResponse(
            success=True,
            profile=profile
        )
    except ValueError as e:
        return ParseResumeResponse(success=False, error=str(e))
    except Exception as e:
        logger.error(f"Parse resume error: {e}")
        raise HTTPException(status_code=500, detail="Ошибка парсинга резюме")


@app.post("/parse-file", response_model=ParseResumeResponse, tags=["Resume"])
@limiter.limit("20/minute")  # Максимум 20 запросов в минуту (файлы тяжелее)
async def parse_file(request: Request, parse_request: ParseFileRequest):
    """
    Парсинг файла резюме (PDF, DOCX, TXT)

    - **file_content**: Base64 encoded содержимое файла
    - **filename**: Имя файла с расширением

    Сначала извлекает текст из файла, затем парсит в профиль.
    """
    try:
        # Извлекаем текст из файла
        text, error = doc_parser.parse_base64(parse_request.file_content, parse_request.filename)

        if error:
            return ParseResumeResponse(success=False, error=error)

        if not text or len(text.strip()) < 50:
            return ParseResumeResponse(
                success=False,
                error="Не удалось извлечь текст из файла или текст слишком короткий"
            )

        # Парсим резюме
        profile = hr_analyzer.parse_resume(text)
        return ParseResumeResponse(success=True, profile=profile, text=text)

    except Exception as e:
        logger.error(f"Parse file error: {e}")
        raise HTTPException(status_code=500, detail="Ошибка парсинга файла")


@app.post("/upload-resume", response_model=ParseResumeResponse, tags=["Resume"])
async def upload_resume(file: UploadFile = File(...)):
    """
    Загрузка и парсинг файла резюме

    Поддерживаемые форматы: PDF, DOCX, DOC, TXT, RTF
    Максимальный размер: 10 MB
    """
    # Валидация
    is_valid, error = doc_parser.validate_file(file.filename, file.size or 0)
    if not is_valid:
        return ParseResumeResponse(success=False, error=error)

    try:
        # Читаем файл
        content = await file.read()

        # Парсим
        text, error = doc_parser.parse_bytes(content, file.filename)
        if error:
            return ParseResumeResponse(success=False, error=error)

        if not text or len(text.strip()) < 50:
            return ParseResumeResponse(
                success=False,
                error="Не удалось извлечь текст из файла"
            )

        # Парсим в профиль
        profile = hr_analyzer.parse_resume(text)
        return ParseResumeResponse(success=True, profile=profile)

    except Exception as e:
        logger.error(f"Upload resume error: {e}")
        raise HTTPException(status_code=500, detail="Ошибка загрузки файла")


class BatchFileItem(BaseModel):
    """Элемент для batch обработки"""
    file_content: str = Field(..., description="Base64 encoded file content")
    filename: str
    file_id: Optional[str] = None  # ID для отслеживания


class BatchFileResponse(BaseModel):
    """Ответ batch обработки"""
    file_id: Optional[str] = None
    filename: str
    success: bool
    profile: Optional[dict] = None
    error: Optional[str] = None


class BatchParseResponse(BaseModel):
    """Ответ batch парсинга"""
    success: bool
    total: int
    processed: int
    failed: int
    results: List[BatchFileResponse]
    processing_time_ms: int


@app.post("/parse-files-batch", response_model=BatchParseResponse, tags=["Resume"])
@limiter.limit("10/minute")
async def parse_files_batch(request: Request, files: List[BatchFileItem]):
    """
    Параллельный парсинг нескольких файлов

    - **files**: Список файлов (base64 + filename)
    - Максимум 10 файлов за раз
    - Обрабатываются параллельно для скорости

    Поддерживаемые форматы: PDF, DOCX, DOC, TXT, RTF, JPG, PNG, JPEG, BMP, TIFF
    """
    import time
    start_time = time.time()

    if len(files) > 10:
        raise HTTPException(status_code=400, detail="Максимум 10 файлов за раз")

    if len(files) == 0:
        raise HTTPException(status_code=400, detail="Нет файлов для обработки")

    async def process_single_file(file_item: BatchFileItem) -> BatchFileResponse:
        """Обработка одного файла"""
        try:
            loop = asyncio.get_event_loop()

            # Парсим файл в thread pool
            text, error = await loop.run_in_executor(
                executor,
                doc_parser.parse_base64,
                file_item.file_content,
                file_item.filename
            )

            if error:
                return BatchFileResponse(
                    file_id=file_item.file_id,
                    filename=file_item.filename,
                    success=False,
                    error=error
                )

            if not text or len(text.strip()) < 50:
                return BatchFileResponse(
                    file_id=file_item.file_id,
                    filename=file_item.filename,
                    success=False,
                    error="Не удалось извлечь текст или текст слишком короткий"
                )

            # Парсим резюме
            profile = await loop.run_in_executor(
                executor,
                hr_analyzer.parse_resume,
                text
            )

            return BatchFileResponse(
                file_id=file_item.file_id,
                filename=file_item.filename,
                success=True,
                profile=profile.model_dump() if hasattr(profile, 'model_dump') else profile.__dict__
            )

        except Exception as e:
            logger.error(f"Batch parse error for {file_item.filename}: {e}")
            return BatchFileResponse(
                file_id=file_item.file_id,
                filename=file_item.filename,
                success=False,
                error=str(e)
            )

    # Параллельная обработка всех файлов
    tasks = [process_single_file(f) for f in files]
    results = await asyncio.gather(*tasks)

    processing_time = int((time.time() - start_time) * 1000)
    processed = sum(1 for r in results if r.success)
    failed = len(results) - processed

    logger.info(f"Batch parse: {processed}/{len(files)} успешно за {processing_time}ms")

    return BatchParseResponse(
        success=failed == 0,
        total=len(files),
        processed=processed,
        failed=failed,
        results=results,
        processing_time_ms=processing_time
    )


class BatchAnalyzeItem(BaseModel):
    """Элемент для batch анализа"""
    profile: dict
    vacancy: dict
    application_id: Optional[int] = None


class BatchAnalyzeResultItem(BaseModel):
    """Результат анализа одного кандидата"""
    application_id: Optional[int] = None
    success: bool
    match_score: Optional[int] = None
    analysis: Optional[dict] = None
    error: Optional[str] = None


class BatchAnalyzeResponse(BaseModel):
    """Ответ batch анализа"""
    success: bool
    total: int
    processed: int
    results: List[BatchAnalyzeResultItem]
    processing_time_ms: int


@app.post("/analyze-batch", response_model=BatchAnalyzeResponse, tags=["Analysis"])
@limiter.limit("5/minute")
async def analyze_candidates_batch(request: Request, items: List[BatchAnalyzeItem]):
    """
    Параллельный анализ нескольких кандидатов

    - **items**: Список {profile, vacancy, application_id}
    - Максимум 20 кандидатов за раз
    - Возвращает match_score и анализ для каждого
    """
    import time
    start_time = time.time()

    if len(items) > 20:
        raise HTTPException(status_code=400, detail="Максимум 20 кандидатов за раз")

    async def analyze_single(item: BatchAnalyzeItem) -> BatchAnalyzeResultItem:
        """Анализ одного кандидата"""
        try:
            loop = asyncio.get_event_loop()

            # Анализ в thread pool
            analysis = await loop.run_in_executor(
                executor,
                hr_analyzer.analyze_candidate,
                item.profile,
                item.vacancy
            )

            return BatchAnalyzeResultItem(
                application_id=item.application_id,
                success=True,
                match_score=analysis.match_score if hasattr(analysis, 'match_score') else analysis.get('match_score', 0),
                analysis=analysis.model_dump() if hasattr(analysis, 'model_dump') else analysis.__dict__
            )

        except Exception as e:
            logger.error(f"Batch analyze error: {e}")
            return BatchAnalyzeResultItem(
                application_id=item.application_id,
                success=False,
                error=str(e)
            )

    # Параллельная обработка
    tasks = [analyze_single(item) for item in items]
    results = await asyncio.gather(*tasks)

    processing_time = int((time.time() - start_time) * 1000)
    processed = sum(1 for r in results if r.success)

    logger.info(f"Batch analyze: {processed}/{len(items)} успешно за {processing_time}ms")

    return BatchAnalyzeResponse(
        success=processed == len(items),
        total=len(items),
        processed=processed,
        results=results,
        processing_time_ms=processing_time
    )


@app.post("/analyze", response_model=AnalyzeResponse, tags=["Analysis"])
@limiter.limit("15/minute")  # Максимум 15 запросов в минуту (самый ресурсоемкий эндпоинт)
async def analyze_candidate(request: Request, analyze_request: AnalyzeRequest):
    """
    Анализ кандидата под вакансию

    - **profile**: Профиль кандидата (из /parse-resume)
    - **vacancy**: Данные вакансии

    Возвращает:
    - Сильные стороны
    - Слабые стороны
    - Риски
    - Рекомендованные вопросы для интервью
    - Рекомендацию
    - Match score (0-100)
    """
    try:
        analysis = hr_analyzer.analyze_candidate(analyze_request.profile, analyze_request.vacancy)
        return AnalyzeResponse(success=True, analysis=analysis)
    except Exception as e:
        logger.error(f"Analyze error: {e}")
        raise HTTPException(status_code=500, detail="Ошибка анализа кандидата")


@app.post("/match-score", response_model=MatchScoreResponse, tags=["Analysis"])
async def calculate_match_score(request: MatchScoreRequest):
    """
    Расчёт match score (0-100)

    Формула:
    - 50% - покрытие обязательных навыков
    - 30% - покрытие желательных навыков
    - 20% - соответствие опыта

    Также возвращает breakdown с детализацией.
    """
    try:
        score = hr_analyzer.calculate_match_score(request.profile, request.vacancy)
        breakdown = hr_analyzer.get_match_breakdown(request.profile, request.vacancy)

        return MatchScoreResponse(
            success=True,
            match_score=score,
            breakdown=breakdown
        )
    except Exception as e:
        logger.error(f"Match score error: {e}")
        raise HTTPException(status_code=500, detail="Ошибка расчёта match score")


@app.post("/questions", response_model=GenerateQuestionsResponse, tags=["Analysis"])
async def generate_questions(request: GenerateQuestionsRequest):
    """
    Генерация вопросов для интервью

    - **profile**: Профиль кандидата
    - **vacancy**: Данные вакансии
    - **focus_areas**: Области для особого внимания (опционально)
    - **count**: Количество вопросов (5-20, по умолчанию 10)
    """
    try:
        questions = await hr_analyzer.generate_interview_questions(
            request.profile,
            request.vacancy,
            count=request.count,
            focus_areas=request.focus_areas
        )
        return GenerateQuestionsResponse(success=True, questions=questions)
    except Exception as e:
        logger.error(f"Questions generation error: {e}")
        raise HTTPException(status_code=500, detail="Ошибка генерации вопросов")


class RejectionEmailRequest(BaseModel):
    vacancy_title: str
    candidate_name: str = "Кандидат"


class RejectionEmailResponse(BaseModel):
    success: bool
    email_text: str = ""
    error: Optional[str] = None


@app.post("/rejection-email", response_model=RejectionEmailResponse, tags=["Utils"])
async def generate_rejection_email(request: RejectionEmailRequest):
    """
    Генерация письма-отказа кандидату

    Создаёт вежливое и профессиональное письмо без указания конкретных причин.
    """
    # Используем шаблон (без LLM)
    email_text = f"""Уважаемый(ая) {request.candidate_name},

Благодарим вас за интерес к позиции «{request.vacancy_title}» в нашей компании и время, уделённое собеседованию.

После тщательного рассмотрения всех кандидатур мы приняли решение продолжить процесс с другими претендентами. Это решение было непростым, учитывая ваш профессиональный опыт и навыки.

Мы сохраним ваше резюме в нашей базе данных и обязательно свяжемся с вами, если появится подходящая вакансия.

Желаем вам успехов в карьерном развитии и поиске интересных проектов!

С уважением,
HR-команда"""

    return RejectionEmailResponse(success=True, email_text=email_text)


# ============== Тестирование кандидатов ==============

class GenerateTestRequest(BaseModel):
    """Запрос на генерацию теста"""
    vacancy_title: str = Field(..., description="Название вакансии")
    vacancy_description: str = Field("", description="Описание вакансии")
    required_skills: List[str] = Field(default=[], description="Требуемые навыки")
    department: str = Field("", description="Отдел")
    difficulty_distribution: dict = Field(
        default={"easy": 5, "medium": 5, "hard": 5},
        description="Распределение вопросов по сложности"
    )


class TestQuestion(BaseModel):
    """Вопрос теста"""
    id: int
    question: str
    options: List[str]  # 4 варианта ответа
    correct_answer: int  # Индекс правильного ответа (0-3)
    difficulty: str  # easy, medium, hard


class GenerateTestResponse(BaseModel):
    """Ответ с тестом"""
    success: bool
    questions: List[TestQuestion] = []
    total_questions: int = 0
    time_limit: int = 900  # 15 минут
    error: Optional[str] = None


@app.post("/generate-test", response_model=GenerateTestResponse, tags=["Testing"])
@limiter.limit("10/minute")
async def generate_test(request: Request, test_request: GenerateTestRequest):
    """
    Генерация теста для кандидата

    - 5 лёгких вопросов
    - 5 средних вопросов
    - 5 сложных вопросов

    Вопросы генерируются на основе вакансии и требуемых навыков.
    """
    try:
        questions = hr_analyzer.generate_test_questions(
            vacancy_title=test_request.vacancy_title,
            vacancy_description=test_request.vacancy_description,
            required_skills=test_request.required_skills,
            department=test_request.department,
            difficulty_distribution=test_request.difficulty_distribution
        )

        return GenerateTestResponse(
            success=True,
            questions=questions,
            total_questions=len(questions),
            time_limit=900
        )
    except Exception as e:
        logger.error(f"Generate test error: {e}")
        return GenerateTestResponse(
            success=False,
            error=str(e)
        )


@app.get("/supported-formats", tags=["Utils"])
async def get_supported_formats():
    """Список поддерживаемых форматов файлов"""
    formats = doc_parser.get_supported_formats() if doc_parser else []

    # Проверяем доступность OCR
    ocr_available = False
    try:
        import pytesseract
        ocr_available = True
    except ImportError:
        pass

    return {
        "formats": formats,
        "documents": [f for f in formats if f in {'.pdf', '.docx', '.doc', '.txt', '.rtf'}],
        "images": [f for f in formats if f in {'.jpg', '.jpeg', '.png', '.bmp', '.tiff', '.tif'}],
        "ocr_available": ocr_available,
        "max_size_mb": CONFIG.get('documents', {}).get('max_file_size_mb', 10),
        "batch_limits": {
            "parse_files_batch": 10,
            "analyze_batch": 20,
        }
    }


# ============== Запуск ==============

if __name__ == "__main__":
    import uvicorn

    host = CONFIG.get('server', {}).get('host', '127.0.0.1')
    port = CONFIG.get('server', {}).get('port', 8080)
    debug = CONFIG.get('server', {}).get('debug', True)

    print(f"""
================================================================
                    HR AI SERVER v2.1
                    (Rule-Based Mode)
================================================================
  Server:       http://{host}:{port}
  API Docs:     http://{host}:{port}/docs
  Health:       http://{host}:{port}/health
================================================================
  Modules:
    - Candidate AI: /parse-resume, /analyze, /match-score
    - Employee AI:  /ai/chat, /ai/explain, /ai/analyze
================================================================
  No external LLM required!
  Using built-in rule-based analysis.
================================================================
    """)

    uvicorn.run(
        "main:app",
        host=host,
        port=port,
        reload=debug
    )
