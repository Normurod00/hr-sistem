"""
Task Router — маршрутизация AI-задач.

Определяет для каждой задачи: prompt, модель, температуру, нужен ли retrieval и fallback.
"""

import logging
from dataclasses import dataclass
from typing import Optional

logger = logging.getLogger(__name__)


@dataclass
class TaskConfig:
    """Конфигурация задачи для AI pipeline."""
    task_name: str
    prompt_key: str              # ключ промпта в prompt_service
    tier: str = "medium"         # cheap / medium / strong
    temperature: float = 0.1     # 0.0 = детерминизм, 0.7 = креатив
    needs_retrieval: bool = False # нужен ли RAG
    retrieval_top_k: int = 5     # сколько документов достать
    needs_fallback: bool = True  # fallback на rule-based
    mask_input: bool = True      # маскировать sensitive data
    max_retries: int = 2


# ============== Task Definitions ==============

TASK_CONFIGS = {
    "resume_parsing": TaskConfig(
        task_name="resume_parsing",
        prompt_key="resume_parsing",
        tier="cheap",              # парсинг — дешёвая модель справится
        temperature=0.0,           # нужен детерминизм
        needs_retrieval=False,
        needs_fallback=True,       # fallback на regex parser
        mask_input=True,
    ),

    "candidate_analysis": TaskConfig(
        task_name="candidate_analysis",
        prompt_key="candidate_analysis",
        tier="medium",             # анализ — нужна средняя модель
        temperature=0.1,
        needs_retrieval=False,
        needs_fallback=True,
        mask_input=True,
    ),

    "match_score": TaskConfig(
        task_name="match_score",
        prompt_key="match_score",
        tier="cheap",
        temperature=0.0,           # score должен быть стабильным
        needs_retrieval=False,
        needs_fallback=True,
        mask_input=False,          # только навыки, не personal data
    ),

    "interview_questions": TaskConfig(
        task_name="interview_questions",
        prompt_key="interview_questions",
        tier="medium",
        temperature=0.4,           # немного вариативности
        needs_retrieval=False,
        needs_fallback=True,
    ),

    "employee_chat": TaskConfig(
        task_name="employee_chat",
        prompt_key="employee_chat",
        tier="medium",
        temperature=0.3,           # разговорный стиль
        needs_retrieval=True,      # RAG по политикам
        retrieval_top_k=5,
        needs_fallback=True,
        mask_input=True,
    ),

    "kpi_explain": TaskConfig(
        task_name="kpi_explain",
        prompt_key="kpi_explain",
        tier="medium",
        temperature=0.1,
        needs_retrieval=False,
        needs_fallback=True,
        mask_input=True,
    ),

    "kpi_recommendations": TaskConfig(
        task_name="kpi_recommendations",
        prompt_key="kpi_recommendations",
        tier="medium",
        temperature=0.2,
        needs_retrieval=False,
        needs_fallback=True,
    ),

    "generate_test": TaskConfig(
        task_name="generate_test",
        prompt_key="generate_test",
        tier="medium",
        temperature=0.5,           # вариативность вопросов
        needs_retrieval=False,
        needs_fallback=True,
    ),
}


class TaskRouter:
    """Маршрутизатор AI-задач."""

    def route(self, task_type: str) -> TaskConfig:
        """Получить конфигурацию для задачи."""
        config = TASK_CONFIGS.get(task_type)
        if not config:
            logger.warning(f"Unknown task type: {task_type}, using default")
            config = TaskConfig(
                task_name=task_type,
                prompt_key=task_type,
                tier="medium",
                temperature=0.1,
                needs_fallback=True,
            )
        return config

    def list_tasks(self) -> list:
        """Список доступных задач."""
        return list(TASK_CONFIGS.keys())
