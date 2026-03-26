"""
Data Models for HR AI Server
Pydantic модели для валидации данных
"""

from typing import Optional, List, Dict, Any
from pydantic import BaseModel, Field
from datetime import datetime


class SkillItem(BaseModel):
    """Навык с уровнем владения"""
    name: str
    level: str = "middle"  # basic, middle, strong


class LanguageItem(BaseModel):
    """Язык с уровнем владения"""
    name: str
    level: str = "B1"  # A1, A2, B1, B2, C1, C2, native


class EducationItem(BaseModel):
    """Образование"""
    degree: Optional[str] = None
    specialization: Optional[str] = None
    institution: Optional[str] = None
    year: Optional[int] = None


class ContactInfo(BaseModel):
    """Контактная информация"""
    email: Optional[str] = None
    phone: Optional[str] = None
    location: Optional[str] = None
    linkedin: Optional[str] = None
    telegram: Optional[str] = None


class CandidateProfile(BaseModel):
    """Структурированный профиль кандидата"""
    position_title: Optional[str] = None
    years_of_experience: float = 0.0
    skills: List[SkillItem] = Field(default_factory=list)
    languages: List[LanguageItem] = Field(default_factory=list)
    domains: List[str] = Field(default_factory=list)
    education: List[EducationItem] = Field(default_factory=list)
    management_experience: bool = False
    remote_experience: bool = False
    summary: Optional[str] = None
    contact_info: Optional[ContactInfo] = None

    class Config:
        json_schema_extra = {
            "example": {
                "position_title": "Senior Python Developer",
                "years_of_experience": 5.5,
                "skills": [
                    {"name": "Python", "level": "strong"},
                    {"name": "FastAPI", "level": "strong"},
                    {"name": "PostgreSQL", "level": "middle"}
                ],
                "languages": [
                    {"name": "Русский", "level": "native"},
                    {"name": "English", "level": "B2"}
                ],
                "domains": ["Fintech", "E-commerce"],
                "education": [
                    {
                        "degree": "Бакалавр",
                        "specialization": "Информатика",
                        "institution": "МГУ",
                        "year": 2018
                    }
                ],
                "management_experience": True,
                "remote_experience": True,
                "summary": "Опытный разработчик с фокусом на backend"
            }
        }


class CandidateAnalysis(BaseModel):
    """Результат анализа кандидата"""
    strengths: List[str] = Field(default_factory=list)
    weaknesses: List[str] = Field(default_factory=list)
    risks: List[str] = Field(default_factory=list)
    suggested_questions: List[str] = Field(default_factory=list)
    recommendation: str = ""
    match_score: int = Field(ge=0, le=100, default=0)

    class Config:
        json_schema_extra = {
            "example": {
                "strengths": [
                    "Сильные технические навыки в Python",
                    "Опыт в Fintech домене",
                    "Хорошие коммуникативные навыки"
                ],
                "weaknesses": [
                    "Нет опыта с Kubernetes",
                    "Ограниченный опыт менеджмента"
                ],
                "risks": [
                    "Частая смена работы (3 места за 2 года)"
                ],
                "suggested_questions": [
                    "Расскажите о вашем опыте оптимизации производительности",
                    "Как вы решаете конфликты в команде?"
                ],
                "recommendation": "Рекомендуется к найму. Сильный технический кандидат с релевантным опытом.",
                "match_score": 85
            }
        }


class VacancyData(BaseModel):
    """Данные вакансии для анализа"""
    title: str
    description: Optional[str] = None
    must_have_skills: List[str] = Field(default_factory=list)
    nice_to_have_skills: List[str] = Field(default_factory=list)
    min_experience_years: Optional[float] = None
    language_requirements: Optional[List[LanguageItem]] = None
    employment_type: str = "full_time"
    location: Optional[str] = None

    class Config:
        json_schema_extra = {
            "example": {
                "title": "Senior Python Developer",
                "description": "Разработка backend сервисов на Python",
                "must_have_skills": ["Python", "FastAPI", "PostgreSQL"],
                "nice_to_have_skills": ["Docker", "Kubernetes", "Redis"],
                "min_experience_years": 4.0,
                "employment_type": "full_time",
                "location": "Ташкент"
            }
        }


# ========== Request/Response Models ==========

class ParseResumeRequest(BaseModel):
    """Запрос на парсинг резюме"""
    text: str = Field(..., min_length=50, description="Текст резюме")


class ParseResumeResponse(BaseModel):
    """Ответ с распарсенным профилем"""
    success: bool
    profile: Optional[CandidateProfile] = None
    text: Optional[str] = None  # Извлечённый текст из файла
    error: Optional[str] = None


class AnalyzeRequest(BaseModel):
    """Запрос на анализ кандидата"""
    profile: Dict[str, Any]
    vacancy: Dict[str, Any]


class AnalyzeResponse(BaseModel):
    """Ответ с анализом"""
    success: bool
    analysis: Optional[CandidateAnalysis] = None
    error: Optional[str] = None


class MatchScoreRequest(BaseModel):
    """Запрос на расчёт match score"""
    profile: Dict[str, Any]
    vacancy: Dict[str, Any]


class MatchScoreResponse(BaseModel):
    """Ответ с match score"""
    success: bool
    match_score: int = 0
    breakdown: Optional[Dict[str, float]] = None
    error: Optional[str] = None


class GenerateQuestionsRequest(BaseModel):
    """Запрос на генерацию вопросов"""
    profile: Dict[str, Any]
    vacancy: Dict[str, Any]
    focus_areas: Optional[List[str]] = None
    count: int = Field(default=10, ge=5, le=20)


class GenerateQuestionsResponse(BaseModel):
    """Ответ с вопросами"""
    success: bool
    questions: List[str] = Field(default_factory=list)
    error: Optional[str] = None


class ParseFileRequest(BaseModel):
    """Запрос на парсинг файла (base64)"""
    file_content: str = Field(..., description="Base64 encoded file content")
    filename: str
    mime_type: Optional[str] = None


class HealthResponse(BaseModel):
    """Ответ health check"""
    status: str
    version: str
    llm_provider: str
    llm_model: str
    timestamp: datetime = Field(default_factory=datetime.now)
    details: Optional[Dict[str, Any]] = None
