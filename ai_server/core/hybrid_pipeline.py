"""
Hybrid AI Pipeline — оркестратор AI обработки.

Pipeline: Input → TaskRouter → [Retrieval?] → PromptService → LLMEngine
          → ResponseValidator → [Fallback?] → AuditLogger → Output

Feature flag: USE_LLM=true/false
Если LLM недоступен → автоматический fallback на rule-based.
"""

import json
import time
import logging
from typing import Optional, Any

from .llm_engine import LLMEngine, LLMResponse
from .task_router import TaskRouter, TaskConfig
from .prompt_service import PromptService
from .response_validator import ResponseValidator
from .fallback_manager import FallbackManager
from .audit_logger import AuditLogger

logger = logging.getLogger(__name__)


class HybridPipeline:
    """
    Единый AI pipeline.
    LLM + Rules + Validation + Fallback + Audit.
    """

    def __init__(self, config: dict, rule_based_analyzer):
        self.config = config
        self.llm = LLMEngine(config)
        self.router = TaskRouter()
        self.prompts = PromptService()
        self.validator = ResponseValidator()
        self.fallback = FallbackManager(rule_based_analyzer)
        self.audit = AuditLogger()

        logger.info(
            f"HybridPipeline initialized: llm_available={self.llm.is_available}, "
            f"prompts={len(self.prompts.list_prompts())}, "
            f"tasks={len(self.router.list_tasks())}"
        )

    async def execute(self, task_type: str, input_data: dict) -> dict:
        """
        Выполнить AI-задачу через hybrid pipeline.

        Args:
            task_type: тип задачи (resume_parsing, candidate_analysis, etc.)
            input_data: входные данные

        Returns:
            dict с результатом (всегда, даже при ошибке — через fallback)
        """
        start_time = time.time()
        task_config = self.router.route(task_type)

        # Попытка LLM
        if self.llm.is_available:
            result = await self._execute_llm(task_config, input_data)
            if result is not None:
                self._log_audit(task_config, input_data, result, used_fallback=False,
                                latency_ms=int((time.time() - start_time) * 1000))
                return result

        # Fallback на rule-based
        if task_config.needs_fallback:
            fallback_result = self.fallback.execute(task_type, input_data)
            if fallback_result is not None:
                self._log_audit(task_config, input_data, fallback_result, used_fallback=True,
                                latency_ms=int((time.time() - start_time) * 1000))
                return fallback_result

        # Полный провал
        error_result = {"success": False, "error": "AI unavailable and fallback failed"}
        self._log_audit(task_config, input_data, error_result, used_fallback=True,
                        latency_ms=int((time.time() - start_time) * 1000), error="Complete failure")
        return error_result

    async def _execute_llm(self, task_config: TaskConfig, input_data: dict) -> Optional[dict]:
        """Выполнить задачу через LLM."""
        # Получить промпт
        system_prompt = self.prompts.get_system_prompt(task_config.prompt_key)
        user_prompt = self._build_user_prompt(task_config.prompt_key, input_data)

        if not system_prompt or not user_prompt:
            logger.warning(f"Missing prompt for task: {task_config.task_name}")
            return None

        # Вызвать LLM
        llm_response: LLMResponse = await self.llm.generate_json(
            system_prompt=system_prompt,
            user_prompt=user_prompt,
            tier=task_config.tier,
            temperature=task_config.temperature,
            task_name=task_config.task_name,
            mask_input=task_config.mask_input,
        )

        if not llm_response.success or llm_response.data is None:
            logger.warning(f"LLM failed for {task_config.task_name}: {llm_response.error}")
            return None

        # Валидация ответа
        schema = self.prompts.get_output_schema(task_config.prompt_key)
        validation = self.validator.validate(llm_response.data, schema, task_config.task_name)

        if not validation.is_valid:
            logger.warning(f"Validation failed for {task_config.task_name}: {validation.errors}")
            return None

        # Добавить мета-данные
        result = llm_response.data
        result["_meta"] = {
            "source": "llm",
            "model": llm_response.model,
            "tokens": llm_response.input_tokens + llm_response.output_tokens,
            "cost_usd": llm_response.cost_usd,
            "latency_ms": llm_response.latency_ms,
        }

        return result

    def _build_user_prompt(self, prompt_key: str, input_data: dict) -> str:
        """Построить user prompt из шаблона и данных."""
        template = self.prompts.get_user_template(prompt_key)

        # Подготовить переменные для шаблона
        template_vars = {}
        for key, value in input_data.items():
            if isinstance(value, (dict, list)):
                template_vars[key] = json.dumps(value, ensure_ascii=False, indent=2)
            else:
                template_vars[key] = str(value) if value is not None else ""

        # Также добавить _json версии dict-полей
        for key, value in input_data.items():
            if isinstance(value, (dict, list)):
                template_vars[f"{key}_json"] = json.dumps(value, ensure_ascii=False, indent=2)

        try:
            return template.format(**template_vars)
        except KeyError:
            # Если шаблон требует переменных, которых нет — передаём как raw JSON
            return json.dumps(input_data, ensure_ascii=False, indent=2)

    def _log_audit(
        self,
        task_config: TaskConfig,
        input_data: dict,
        result: dict,
        used_fallback: bool,
        latency_ms: int,
        error: Optional[str] = None,
    ):
        """Записать аудит."""
        meta = result.get("_meta", {})
        confidence = result.get("confidence", "medium")

        # Краткое описание входа (без sensitive)
        input_keys = list(input_data.keys())
        input_summary = f"keys={input_keys}"

        self.audit.log(
            task_name=task_config.task_name,
            prompt_key=task_config.prompt_key,
            tier=task_config.tier,
            model=meta.get("model", "rule-based"),
            input_summary=input_summary,
            output_data=result,
            success=error is None,
            used_fallback=used_fallback,
            confidence=confidence,
            input_tokens=meta.get("tokens", 0),
            output_tokens=0,
            cost_usd=meta.get("cost_usd", 0),
            latency_ms=latency_ms,
            error=error,
        )

    # ============== Convenience Methods ==============

    async def parse_resume(self, text: str) -> dict:
        """Парсинг резюме через hybrid pipeline."""
        return await self.execute("resume_parsing", {"text": text, "resume_text": text})

    async def analyze_candidate(self, profile: dict, vacancy: dict) -> dict:
        """Анализ кандидата через hybrid pipeline."""
        return await self.execute("candidate_analysis", {"profile": profile, "vacancy": vacancy})

    async def calculate_match_score(self, profile: dict, vacancy: dict) -> dict:
        """Расчёт match score через hybrid pipeline."""
        return await self.execute("match_score", {"profile": profile, "vacancy": vacancy})

    async def generate_questions(self, profile: dict, vacancy: dict, count: int = 10, focus_areas: list = None) -> dict:
        """Генерация вопросов через hybrid pipeline."""
        return await self.execute("interview_questions", {
            "profile": profile, "vacancy": vacancy,
            "count": count, "focus_areas": ", ".join(focus_areas) if focus_areas else "general",
        })

    async def employee_chat(self, message: str, context: str = "", history: str = "",
                            department: str = "", position: str = "") -> dict:
        """Чат сотрудника через hybrid pipeline."""
        return await self.execute("employee_chat", {
            "message": message, "context": context, "history": history,
            "department": department, "position": position,
        })

    async def explain_kpi(self, kpi_data: dict, department: str = "", position: str = "") -> dict:
        """Объяснение KPI через hybrid pipeline."""
        return await self.execute("kpi_explain", {
            "period": kpi_data.get("period", ""),
            "total_score": kpi_data.get("total_score", 0),
            "metrics": kpi_data.get("metrics", {}),
            "bonus": kpi_data.get("bonus_info", {}),
            "low_metrics": kpi_data.get("low_metrics", []),
            "department": department,
            "position": position,
        })

    def get_stats(self) -> dict:
        """Статистика pipeline."""
        return {
            "llm_available": self.llm.is_available,
            "llm_usage": self.llm.usage.to_dict(),
            "fallback": self.fallback.get_stats(),
            "audit": self.audit.get_stats(),
            "prompts_loaded": len(self.prompts.list_prompts()),
        }
