"""
Fallback Manager — переключение на rule-based при ошибке LLM.

Если LLM недоступен или ответ невалидный → используем старый rule-based analyzer.
"""

import logging
from typing import Optional, Any

logger = logging.getLogger(__name__)


class FallbackManager:
    """Менеджер fallback на rule-based систему."""

    def __init__(self, rule_based_analyzer):
        self.analyzer = rule_based_analyzer
        self.fallback_count = 0
        self.total_calls = 0

    def execute(self, task_name: str, input_data: dict) -> Optional[Any]:
        """
        Выполнить task через rule-based analyzer.

        Args:
            task_name: тип задачи
            input_data: входные данные

        Returns:
            Результат rule-based обработки или None
        """
        self.total_calls += 1
        self.fallback_count += 1
        logger.info(f"Fallback [{task_name}] → rule-based (total fallbacks: {self.fallback_count})")

        try:
            if task_name == "resume_parsing":
                return self._fallback_resume(input_data)
            elif task_name == "candidate_analysis":
                return self._fallback_analysis(input_data)
            elif task_name == "match_score":
                return self._fallback_match_score(input_data)
            elif task_name == "interview_questions":
                return self._fallback_questions(input_data)
            elif task_name == "employee_chat":
                return self._fallback_chat(input_data)
            elif task_name == "kpi_explain":
                return self._fallback_kpi_explain(input_data)
            elif task_name == "kpi_recommendations":
                return self._fallback_kpi_recommendations(input_data)
            elif task_name == "generate_test":
                return self._fallback_test(input_data)
            else:
                logger.warning(f"No fallback handler for task: {task_name}")
                return None

        except Exception as e:
            logger.error(f"Fallback [{task_name}] also failed: {e}")
            return None

    def _fallback_resume(self, data: dict):
        text = data.get("text", "")
        profile = self.analyzer.parse_resume(text)
        return {"profile": profile.__dict__ if hasattr(profile, '__dict__') else profile}

    def _fallback_analysis(self, data: dict):
        profile = data.get("profile", {})
        vacancy = data.get("vacancy", {})
        result = self.analyzer.analyze_candidate(profile, vacancy)
        return result.__dict__ if hasattr(result, '__dict__') else result

    def _fallback_match_score(self, data: dict):
        profile = data.get("profile", {})
        vacancy = data.get("vacancy", {})
        score = self.analyzer.calculate_match_score(profile, vacancy)
        breakdown = self.analyzer.get_match_breakdown(profile, vacancy)
        return {"match_score": score, "breakdown": breakdown}

    def _fallback_questions(self, data: dict):
        profile = data.get("profile", {})
        vacancy = data.get("vacancy", {})
        # Rule-based analyzer не имеет async generate_interview_questions
        # Возвращаем базовые вопросы
        return {"questions": []}

    def _fallback_chat(self, data: dict):
        return {
            "answer": "Извините, AI-ассистент временно недоступен. Пожалуйста, попробуйте позже или обратитесь в HR-отдел.",
            "sources": [],
            "confidence": "low",
            "requires_hr_confirmation": True,
        }

    def _fallback_kpi_explain(self, data: dict):
        kpi_data = data.get("kpi_data", {})
        total_score = kpi_data.get("total_score", 0)
        metrics = kpi_data.get("metrics", {})

        explanation = f"Ваш общий KPI составляет {total_score}%. "
        if total_score >= 80:
            explanation += "Это отличный результат."
        elif total_score >= 60:
            explanation += "Это хороший результат, но есть потенциал для роста."
        elif total_score >= 40:
            explanation += "Результат ниже ожидаемого. Рекомендуется обратить внимание на отстающие показатели."
        else:
            explanation += "Требуется серьёзная работа над улучшением показателей."

        return {
            "success": True,
            "explanation": explanation,
            "metric_explanations": {},
            "improvement_suggestions": ["Обратитесь к менеджеру для получения плана развития."],
        }

    def _fallback_kpi_recommendations(self, data: dict):
        return {
            "recommendations": [
                {"action": "Обратитесь к менеджеру для составления плана развития", "type": "medium", "priority": 1}
            ],
            "priority_actions": [],
        }

    def _fallback_test(self, data: dict):
        return {"questions": [], "error": "AI недоступен для генерации теста"}

    def get_stats(self) -> dict:
        return {
            "fallback_count": self.fallback_count,
            "total_calls": self.total_calls,
            "fallback_rate": round(self.fallback_count / max(self.total_calls, 1) * 100, 1),
        }
