"""
Employee API Endpoints - Эндпоинты для сотрудников

Роутер FastAPI для обработки запросов портала сотрудников.
Включается в main.py
"""

import logging
from typing import List, Dict, Any, Optional
from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field

from .employee_module import (
    chat_handler,
    kpi_explainer,
    recommendation_engine,
    EmployeeIntent,
)

logger = logging.getLogger(__name__)

# ============== Pydantic Models ==============

class EmployeeContext(BaseModel):
    """Контекст сотрудника"""
    type: str = Field(default="employee", description="Тип контекста: employee")
    employee_id: Optional[str] = Field(None, description="ID сотрудника")
    department: Optional[str] = Field(None, description="Отдел")
    position: Optional[str] = Field(None, description="Должность")
    conversation_type: Optional[str] = Field(None, description="Тип разговора")


class ChatMessage(BaseModel):
    """Сообщение чата"""
    role: str = Field(..., description="Роль: user/assistant/system")
    content: str = Field(..., description="Содержимое сообщения")


class PolicyInfo(BaseModel):
    """Информация о политике"""
    id: Optional[int] = None
    code: Optional[str] = None
    title: str
    category: Optional[str] = None
    summary: Optional[str] = None


class EmployeeChatRequest(BaseModel):
    """Запрос чата сотрудника"""
    context: EmployeeContext
    message: str = Field(..., min_length=1, max_length=4000, description="Сообщение пользователя")
    intent: Optional[str] = Field(None, description="Предопределённый интент")
    history: List[ChatMessage] = Field(default=[], description="История сообщений")
    facts: Dict[str, Any] = Field(default={}, description="Факты (KPI, etc)")
    policies: List[PolicyInfo] = Field(default=[], description="Релевантные политики")


class EmployeeChatResponse(BaseModel):
    """Ответ чата"""
    response: str = Field(..., description="Ответ AI")
    intent: str = Field(..., description="Определённый интент")
    confidence: float = Field(default=0.0, description="Уверенность")
    sources: List[Dict[str, str]] = Field(default=[], description="Источники")


class KpiExplainRequest(BaseModel):
    """Запрос объяснения KPI"""
    context: EmployeeContext
    kpi_data: Dict[str, Any] = Field(..., description="Данные KPI")
    employee: Optional[Dict[str, Any]] = Field(None, description="Данные сотрудника")


class KpiExplainResponse(BaseModel):
    """Ответ объяснения KPI"""
    explanation: str = Field(..., description="Общее объяснение")
    metric_explanations: Dict[str, str] = Field(default={}, description="Объяснения по метрикам")
    improvement_suggestions: List[str] = Field(default=[], description="Рекомендации")
    risk_assessment: Optional[Dict[str, Any]] = Field(None, description="Оценка рисков")


class RecommendationsRequest(BaseModel):
    """Запрос рекомендаций"""
    context: EmployeeContext
    kpi_data: Dict[str, Any] = Field(..., description="Данные KPI")
    employee: Optional[Dict[str, Any]] = Field(None, description="Данные сотрудника")


class Recommendation(BaseModel):
    """Рекомендация"""
    type: str = Field(..., description="Тип: quick/medium/long")
    action: str = Field(..., description="Действие")
    priority: int = Field(default=1, description="Приоритет")
    expected_effect: Optional[str] = Field(None, description="Ожидаемый эффект")
    expected_impact: Optional[float] = Field(None, description="Ожидаемое влияние на KPI")
    metric: Optional[str] = Field(None, description="Связанная метрика")


class RecommendationsResponse(BaseModel):
    """Ответ с рекомендациями"""
    recommendations: List[Recommendation] = Field(default=[], description="Рекомендации")
    priority_actions: List[Recommendation] = Field(default=[], description="Приоритетные действия")
    expected_improvement: Optional[Dict[str, Any]] = Field(None, description="Ожидаемое улучшение")


# ============== Router ==============

router = APIRouter(prefix="/ai", tags=["Employee AI"])


@router.post("/chat", response_model=EmployeeChatResponse)
async def employee_chat(request: EmployeeChatRequest):
    """
    Чат с AI для сотрудников

    Обрабатывает сообщения сотрудников и возвращает ответы на основе:
    - Определённого интента (KPI, отпуск, бонус, политики)
    - Переданных фактов (текущий KPI, метрики)
    - Релевантных политик
    """
    try:
        logger.info(f"Employee chat request: {request.message[:50]}...")

        # Конвертируем history в нужный формат
        history = [{"role": m.role, "content": m.content} for m in request.history]

        # Конвертируем policies в нужный формат
        policies = [p.dict() for p in request.policies]

        # Обрабатываем через chat_handler
        result = chat_handler.handle_chat(
            message=request.message,
            context=request.context.dict(),
            history=history,
            facts=request.facts,
            policies=policies,
        )

        return EmployeeChatResponse(
            response=result.get("response", "Извините, не удалось обработать запрос"),
            intent=result.get("intent", "general"),
            confidence=result.get("confidence", 0.0),
            sources=result.get("sources", []),
        )

    except Exception as e:
        logger.error(f"Employee chat error: {e}", exc_info=True)
        raise HTTPException(status_code=500, detail=str(e))


@router.post("/explain", response_model=KpiExplainResponse)
async def explain_kpi(request: KpiExplainRequest):
    """
    Объяснение KPI

    Анализирует показатели KPI и возвращает:
    - Общее объяснение результатов
    - Объяснения по отдельным метрикам
    - Рекомендации по улучшению
    - Оценку рисков
    """
    try:
        logger.info("KPI explain request")

        # Извлекаем низкие метрики если не переданы
        kpi_data = request.kpi_data.copy()
        if "low_metrics" not in kpi_data:
            metrics = kpi_data.get("metrics", {})
            low_metrics = {
                k: v for k, v in metrics.items()
                if v.get("completion", 100) < 70
            }
            kpi_data["low_metrics"] = low_metrics

        result = kpi_explainer.explain_kpi(kpi_data)

        return KpiExplainResponse(
            explanation=result.get("explanation", ""),
            metric_explanations=result.get("metric_explanations", {}),
            improvement_suggestions=result.get("improvement_suggestions", []),
            risk_assessment=result.get("risk_assessment"),
        )

    except Exception as e:
        logger.error(f"KPI explain error: {e}", exc_info=True)
        raise HTTPException(status_code=500, detail=str(e))


@router.post("/analyze", response_model=RecommendationsResponse)
async def analyze_and_recommend(request: RecommendationsRequest):
    """
    Анализ и рекомендации

    Генерирует персонализированные рекомендации для улучшения KPI:
    - Quick: быстрые действия (1-2 недели)
    - Medium: среднесрочные (1-3 месяца)
    - Long: долгосрочные (3-12 месяцев)
    """
    try:
        logger.info("Recommendations request")

        # Извлекаем низкие метрики
        kpi_data = request.kpi_data.copy()
        if "low_metrics" not in kpi_data:
            metrics = kpi_data.get("metrics", {})
            low_metrics = {
                k: v for k, v in metrics.items()
                if v.get("completion", 100) < 90
            }
            kpi_data["low_metrics"] = low_metrics

        employee_data = request.employee or {}

        result = recommendation_engine.generate_recommendations(kpi_data, employee_data)

        # Конвертируем в Pydantic модели
        recommendations = [
            Recommendation(
                type=r.get("type", "medium"),
                action=r.get("action", ""),
                priority=r.get("priority", 99),
                expected_effect=r.get("effect"),
                expected_impact=r.get("expected_impact"),
                metric=r.get("metric"),
            )
            for r in result.get("recommendations", [])
        ]

        priority_actions = [
            Recommendation(
                type=r.get("type", "quick"),
                action=r.get("action", ""),
                priority=r.get("priority", 99),
                expected_effect=r.get("effect"),
                expected_impact=r.get("expected_impact"),
            )
            for r in result.get("priority_actions", [])
        ]

        return RecommendationsResponse(
            recommendations=recommendations,
            priority_actions=priority_actions,
            expected_improvement=result.get("expected_improvement"),
        )

    except Exception as e:
        logger.error(f"Recommendations error: {e}", exc_info=True)
        raise HTTPException(status_code=500, detail=str(e))


# ============== Health Endpoint ==============

@router.get("/employee-health")
async def employee_module_health():
    """Проверка здоровья модуля сотрудников"""
    return {
        "status": "ok",
        "module": "employee",
        "handlers": {
            "chat": chat_handler is not None,
            "kpi_explainer": kpi_explainer is not None,
            "recommendation_engine": recommendation_engine is not None,
        },
    }
