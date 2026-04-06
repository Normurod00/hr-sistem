"""
LLM Engine — единый клиент для работы с LLM провайдерами.

Hybrid AI: Anthropic Claude / OpenAI GPT с fallback на rule-based.
Structured JSON output, retry, timeout, token/cost tracking, sensitive data masking.
"""

import os
import re
import json
import time
import logging
import asyncio
from typing import Optional, Dict, Any, List
from dataclasses import dataclass

import httpx

logger = logging.getLogger(__name__)

# ============== Model Configuration ==============

MODEL_TIERS = {
    "cheap": {
        "anthropic": "claude-haiku-4-5-20251001",
        "openai": "gpt-4o-mini",
        "max_tokens": 2048,
    },
    "medium": {
        "anthropic": "claude-sonnet-4-6",
        "openai": "gpt-4o",
        "max_tokens": 4096,
    },
    "strong": {
        "anthropic": "claude-opus-4-6",
        "openai": "gpt-4o",
        "max_tokens": 8192,
    },
}

MODEL_COSTS_PER_1M = {
    "claude-haiku-4-5-20251001": {"input": 1.0, "output": 5.0},
    "claude-sonnet-4-6": {"input": 3.0, "output": 15.0},
    "claude-opus-4-6": {"input": 15.0, "output": 75.0},
    "gpt-4o-mini": {"input": 0.15, "output": 0.6},
    "gpt-4o": {"input": 5.0, "output": 15.0},
}


# ============== Data Classes ==============

@dataclass
class LLMResponse:
    """Результат вызова LLM."""
    success: bool
    data: Optional[dict] = None
    raw_text: str = ""
    model: str = ""
    input_tokens: int = 0
    output_tokens: int = 0
    cost_usd: float = 0.0
    latency_ms: int = 0
    error: Optional[str] = None
    retries: int = 0


class UsageTracker:
    """Трекер использования токенов и стоимости."""

    def __init__(self):
        self.total_requests = 0
        self.total_input_tokens = 0
        self.total_output_tokens = 0
        self.total_cost_usd = 0.0
        self.total_errors = 0

    def record(self, response: LLMResponse):
        self.total_requests += 1
        self.total_input_tokens += response.input_tokens
        self.total_output_tokens += response.output_tokens
        self.total_cost_usd += response.cost_usd
        if not response.success:
            self.total_errors += 1

    def to_dict(self) -> dict:
        return {
            "total_requests": self.total_requests,
            "total_input_tokens": self.total_input_tokens,
            "total_output_tokens": self.total_output_tokens,
            "total_cost_usd": round(self.total_cost_usd, 4),
            "total_errors": self.total_errors,
        }


# ============== Sensitive Data Masking ==============

_MASK_PATTERNS = [
    (re.compile(r'\b\d{13,16}\b'), '[CARD_MASKED]'),
    (re.compile(r'\b\d{14}\b'), '[PINFL_MASKED]'),
    (re.compile(r'\b[A-Z]{2}\d{7}\b'), '[PASSPORT_MASKED]'),
]


def mask_sensitive(text: str) -> str:
    """Маскирует чувствительные данные перед отправкой в LLM."""
    for pattern, replacement in _MASK_PATTERNS:
        text = pattern.sub(replacement, text)
    return text


# ============== JSON Extraction ==============

def extract_json(text: str) -> Optional[dict]:
    """Извлечение JSON из ответа LLM (может быть обёрнут в markdown)."""
    text = text.strip()

    # Direct parse
    try:
        return json.loads(text)
    except (json.JSONDecodeError, ValueError):
        pass

    # From ```json ... ```
    match = re.search(r'```(?:json)?\s*\n?(.*?)\n?\s*```', text, re.DOTALL)
    if match:
        try:
            return json.loads(match.group(1).strip())
        except (json.JSONDecodeError, ValueError):
            pass

    # Find first balanced { ... }
    start = text.find('{')
    if start >= 0:
        depth = 0
        for i in range(start, len(text)):
            if text[i] == '{':
                depth += 1
            elif text[i] == '}':
                depth -= 1
                if depth == 0:
                    try:
                        return json.loads(text[start:i + 1])
                    except (json.JSONDecodeError, ValueError):
                        break

    return None


# ============== LLM Engine ==============

class LLMEngine:
    """
    Production LLM Engine.
    Supports Anthropic Claude and OpenAI GPT with structured JSON output.
    """

    def __init__(self, config: dict):
        llm_config = config.get("llm", config)

        self.provider = llm_config.get("provider", "anthropic")
        self.anthropic_key = llm_config.get("anthropic_api_key") or os.getenv("ANTHROPIC_API_KEY", "")
        self.openai_key = llm_config.get("openai_api_key") or os.getenv("OPENAI_API_KEY", "")
        self.default_tier = llm_config.get("default_tier", "medium")
        self.timeout = llm_config.get("timeout", 60)
        self.max_retries = llm_config.get("max_retries", 2)
        self.enabled = llm_config.get("enabled", False)

        self.usage = UsageTracker()

        status = "enabled" if self.is_available else "disabled"
        logger.info(f"LLM Engine: provider={self.provider}, tier={self.default_tier}, status={status}")

    @property
    def is_available(self) -> bool:
        if not self.enabled:
            return False
        if self.provider == "anthropic":
            return bool(self.anthropic_key)
        if self.provider == "openai":
            return bool(self.openai_key)
        return False

    def _resolve_model(self, tier: str) -> str:
        tier_cfg = MODEL_TIERS.get(tier, MODEL_TIERS["medium"])
        return tier_cfg.get(self.provider, tier_cfg.get("anthropic", "claude-sonnet-4-6"))

    def _resolve_max_tokens(self, tier: str) -> int:
        return MODEL_TIERS.get(tier, MODEL_TIERS["medium"])["max_tokens"]

    def _calc_cost(self, model: str, input_tokens: int, output_tokens: int) -> float:
        costs = MODEL_COSTS_PER_1M.get(model, {"input": 0, "output": 0})
        return (input_tokens * costs["input"] + output_tokens * costs["output"]) / 1_000_000

    # ============== Main API ==============

    async def generate_json(
        self,
        system_prompt: str,
        user_prompt: str,
        tier: str = "medium",
        temperature: float = 0.1,
        task_name: str = "unknown",
        mask_input: bool = True,
    ) -> LLMResponse:
        """
        Генерация structured JSON ответа от LLM.

        Args:
            system_prompt: Системный промпт с инструкциями
            user_prompt: Данные задачи
            tier: cheap / medium / strong
            temperature: 0.0 - 1.0
            task_name: для логирования и аудита
            mask_input: маскировать чувствительные данные
        """
        if not self.is_available:
            return LLMResponse(success=False, error="LLM not available or disabled")

        if mask_input:
            user_prompt = mask_sensitive(user_prompt)

        model = self._resolve_model(tier)
        max_tokens = self._resolve_max_tokens(tier)
        start_time = time.time()
        last_error = None

        for attempt in range(self.max_retries + 1):
            try:
                if self.provider == "anthropic":
                    result = await self._call_anthropic(model, system_prompt, user_prompt, max_tokens, temperature)
                elif self.provider == "openai":
                    result = await self._call_openai(model, system_prompt, user_prompt, max_tokens, temperature)
                else:
                    return LLMResponse(success=False, error=f"Unknown provider: {self.provider}")

                latency = int((time.time() - start_time) * 1000)
                cost = self._calc_cost(model, result["input_tokens"], result["output_tokens"])
                parsed = extract_json(result["text"])

                response = LLMResponse(
                    success=parsed is not None,
                    data=parsed,
                    raw_text=result["text"],
                    model=model,
                    input_tokens=result["input_tokens"],
                    output_tokens=result["output_tokens"],
                    cost_usd=round(cost, 6),
                    latency_ms=latency,
                    retries=attempt,
                    error="Failed to parse JSON from LLM response" if parsed is None else None,
                )

                self.usage.record(response)

                logger.info(
                    f"LLM [{task_name}] model={model} "
                    f"tokens={result['input_tokens']}+{result['output_tokens']} "
                    f"cost=${cost:.4f} latency={latency}ms attempt={attempt} ok={response.success}"
                )

                return response

            except httpx.TimeoutException:
                last_error = f"Timeout after {self.timeout}s"
                logger.warning(f"LLM [{task_name}] timeout (attempt {attempt + 1})")
            except httpx.HTTPStatusError as e:
                last_error = f"HTTP {e.response.status_code}"
                logger.warning(f"LLM [{task_name}] HTTP {e.response.status_code} (attempt {attempt + 1})")
                if e.response.status_code == 429:
                    await asyncio.sleep(2 ** (attempt + 1))
                elif e.response.status_code < 500:
                    break
            except Exception as e:
                last_error = str(e)
                logger.error(f"LLM [{task_name}] error: {e}")

            if attempt < self.max_retries:
                await asyncio.sleep(0.5 * (attempt + 1))

        error_response = LLMResponse(
            success=False,
            error=last_error or "Max retries exceeded",
            latency_ms=int((time.time() - start_time) * 1000),
            retries=self.max_retries,
        )
        self.usage.record(error_response)
        return error_response

    # ============== Provider Calls ==============

    async def _call_anthropic(self, model, system, user, max_tokens, temperature) -> dict:
        async with httpx.AsyncClient(timeout=self.timeout) as client:
            resp = await client.post(
                "https://api.anthropic.com/v1/messages",
                headers={
                    "x-api-key": self.anthropic_key,
                    "anthropic-version": "2023-06-01",
                    "content-type": "application/json",
                },
                json={
                    "model": model,
                    "max_tokens": max_tokens,
                    "temperature": temperature,
                    "system": system,
                    "messages": [{"role": "user", "content": user}],
                },
            )
            resp.raise_for_status()
            data = resp.json()
            return {
                "text": data["content"][0]["text"],
                "input_tokens": data["usage"]["input_tokens"],
                "output_tokens": data["usage"]["output_tokens"],
            }

    async def _call_openai(self, model, system, user, max_tokens, temperature) -> dict:
        async with httpx.AsyncClient(timeout=self.timeout) as client:
            resp = await client.post(
                "https://api.openai.com/v1/chat/completions",
                headers={
                    "Authorization": f"Bearer {self.openai_key}",
                    "Content-Type": "application/json",
                },
                json={
                    "model": model,
                    "max_tokens": max_tokens,
                    "temperature": temperature,
                    "response_format": {"type": "json_object"},
                    "messages": [
                        {"role": "system", "content": system},
                        {"role": "user", "content": user},
                    ],
                },
            )
            resp.raise_for_status()
            data = resp.json()
            usage = data.get("usage", {})
            return {
                "text": data["choices"][0]["message"]["content"],
                "input_tokens": usage.get("prompt_tokens", 0),
                "output_tokens": usage.get("completion_tokens", 0),
            }
