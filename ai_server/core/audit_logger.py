"""
Audit Logger — логирование всех AI запросов и ответов.

Сохраняет: input, task, prompt version, model, output, confidence,
fallback status, latency, token usage, cost.
"""

import json
import time
import logging
import os
from datetime import datetime
from typing import Optional, Any
from dataclasses import dataclass, asdict

logger = logging.getLogger(__name__)

LOG_DIR = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "logs")


@dataclass
class AuditEntry:
    """Запись аудита AI-запроса."""
    timestamp: str
    task_name: str
    model: str
    prompt_key: str
    tier: str
    input_summary: str               # краткое описание входа (без sensitive data)
    output_summary: str               # краткое описание выхода
    success: bool
    used_fallback: bool
    confidence: str                   # low / medium / high
    input_tokens: int
    output_tokens: int
    cost_usd: float
    latency_ms: int
    retries: int
    validation_errors: list
    validation_warnings: list
    error: Optional[str] = None


class AuditLogger:
    """Логгер аудита AI-операций."""

    def __init__(self):
        os.makedirs(LOG_DIR, exist_ok=True)
        self._entries: list = []
        self._max_memory = 1000  # держать в памяти последние N записей

    def log(
        self,
        task_name: str,
        prompt_key: str = "",
        tier: str = "medium",
        model: str = "",
        input_summary: str = "",
        output_data: Optional[dict] = None,
        success: bool = True,
        used_fallback: bool = False,
        confidence: str = "medium",
        input_tokens: int = 0,
        output_tokens: int = 0,
        cost_usd: float = 0.0,
        latency_ms: int = 0,
        retries: int = 0,
        validation_errors: Optional[list] = None,
        validation_warnings: Optional[list] = None,
        error: Optional[str] = None,
    ):
        """Записать аудит-запись."""
        output_summary = ""
        if output_data:
            # Краткое описание результата
            keys = list(output_data.keys())[:5]
            output_summary = f"keys={keys}"

        entry = AuditEntry(
            timestamp=datetime.utcnow().isoformat() + "Z",
            task_name=task_name,
            model=model,
            prompt_key=prompt_key,
            tier=tier,
            input_summary=input_summary[:200],
            output_summary=output_summary[:200],
            success=success,
            used_fallback=used_fallback,
            confidence=confidence,
            input_tokens=input_tokens,
            output_tokens=output_tokens,
            cost_usd=round(cost_usd, 6),
            latency_ms=latency_ms,
            retries=retries,
            validation_errors=validation_errors or [],
            validation_warnings=validation_warnings or [],
            error=error,
        )

        # В память
        self._entries.append(entry)
        if len(self._entries) > self._max_memory:
            self._entries = self._entries[-self._max_memory:]

        # В файл
        self._write_to_file(entry)

        # В logging
        level = logging.WARNING if not success else logging.INFO
        logger.log(
            level,
            f"AUDIT [{task_name}] model={model} success={success} fallback={used_fallback} "
            f"confidence={confidence} tokens={input_tokens}+{output_tokens} "
            f"cost=${cost_usd:.4f} latency={latency_ms}ms"
        )

    def _write_to_file(self, entry: AuditEntry):
        """Записать в JSONL файл (append)."""
        try:
            date_str = datetime.utcnow().strftime("%Y-%m-%d")
            filepath = os.path.join(LOG_DIR, f"ai_audit_{date_str}.jsonl")

            with open(filepath, "a", encoding="utf-8") as f:
                f.write(json.dumps(asdict(entry), ensure_ascii=False) + "\n")
        except Exception as e:
            logger.error(f"Failed to write audit log: {e}")

    def get_recent(self, limit: int = 50) -> list:
        """Получить последние N записей."""
        return [asdict(e) for e in self._entries[-limit:]]

    def get_stats(self) -> dict:
        """Агрегированная статистика."""
        if not self._entries:
            return {"total": 0}

        total = len(self._entries)
        successful = sum(1 for e in self._entries if e.success)
        fallbacks = sum(1 for e in self._entries if e.used_fallback)
        total_cost = sum(e.cost_usd for e in self._entries)
        avg_latency = sum(e.latency_ms for e in self._entries) / total

        return {
            "total": total,
            "successful": successful,
            "failed": total - successful,
            "fallbacks": fallbacks,
            "success_rate": round(successful / total * 100, 1),
            "fallback_rate": round(fallbacks / total * 100, 1),
            "total_cost_usd": round(total_cost, 4),
            "avg_latency_ms": round(avg_latency),
        }
