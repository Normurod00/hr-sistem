"""
Response Validator — валидация ответов LLM.

Проверяет: JSON schema, галлюцинации, sensitive data, consistency.
"""

import re
import logging
from typing import Optional

logger = logging.getLogger(__name__)

# Запрещённые паттерны в ответах
SENSITIVE_PATTERNS = [
    re.compile(r'\b\d{13,16}\b'),          # card numbers
    re.compile(r'\b\d{14}\b'),             # PINFL
    re.compile(r'\b[A-Z]{2}\d{7}\b'),      # passport
    re.compile(r'password\s*[:=]\s*\S+', re.I),
    re.compile(r'api[_-]?key\s*[:=]\s*\S+', re.I),
]


class ValidationResult:
    """Результат валидации."""

    def __init__(self):
        self.is_valid = True
        self.errors: list = []
        self.warnings: list = []
        self.sanitized_data: Optional[dict] = None

    def add_error(self, msg: str):
        self.is_valid = False
        self.errors.append(msg)

    def add_warning(self, msg: str):
        self.warnings.append(msg)


class ResponseValidator:
    """Валидатор ответов LLM."""

    def validate(self, data: Optional[dict], schema: Optional[dict] = None, task_name: str = "") -> ValidationResult:
        """
        Полная валидация ответа.

        Args:
            data: распарсенный JSON от LLM
            schema: ожидаемая структура (optional)
            task_name: имя задачи для логирования
        """
        result = ValidationResult()

        if data is None:
            result.add_error("Response is None — LLM did not return valid JSON")
            return result

        if not isinstance(data, dict):
            result.add_error(f"Expected dict, got {type(data).__name__}")
            return result

        # Schema validation
        if schema:
            self._validate_schema(data, schema, result)

        # Sensitive data check
        self._check_sensitive(data, result)

        # Hallucination detection
        self._check_hallucinations(data, task_name, result)

        # Empty content check
        self._check_empty(data, result)

        result.sanitized_data = data

        if result.errors:
            logger.warning(f"Validation [{task_name}] failed: {result.errors}")
        elif result.warnings:
            logger.info(f"Validation [{task_name}] passed with warnings: {result.warnings}")

        return result

    def _validate_schema(self, data: dict, schema: dict, result: ValidationResult):
        """Проверка обязательных полей из schema."""
        required = schema.get("required", [])
        properties = schema.get("properties", {})

        for field_name in required:
            if field_name not in data:
                result.add_error(f"Missing required field: {field_name}")
            elif data[field_name] is None:
                result.add_warning(f"Required field is null: {field_name}")

        for field_name, field_spec in properties.items():
            if field_name in data and data[field_name] is not None:
                expected_type = field_spec.get("type")
                if expected_type == "array" and not isinstance(data[field_name], list):
                    result.add_error(f"Field {field_name} should be array, got {type(data[field_name]).__name__}")
                elif expected_type == "number" and not isinstance(data[field_name], (int, float)):
                    result.add_warning(f"Field {field_name} should be number")
                elif expected_type == "string" and not isinstance(data[field_name], str):
                    result.add_warning(f"Field {field_name} should be string")

    def _check_sensitive(self, data: dict, result: ValidationResult):
        """Проверка на утечку sensitive data в ответе."""
        text = str(data)
        for pattern in SENSITIVE_PATTERNS:
            if pattern.search(text):
                result.add_warning("Response may contain sensitive data — masking recommended")
                break

    def _check_hallucinations(self, data: dict, task_name: str, result: ValidationResult):
        """Базовая проверка на галлюцинации."""
        text = str(data).lower()

        hallucination_markers = [
            "as an ai",
            "i cannot",
            "i don't have access",
            "i'm not sure but",
            "i think maybe",
            "let me guess",
        ]

        for marker in hallucination_markers:
            if marker in text:
                result.add_warning(f"Possible hallucination marker: '{marker}'")

        # Score sanity check
        if task_name in ("candidate_analysis", "match_score"):
            score = data.get("final_score") or data.get("match_score") or data.get("score")
            if score is not None:
                if isinstance(score, (int, float)):
                    if score < 0 or score > 100:
                        result.add_error(f"Score out of range: {score} (expected 0-100)")

    def _check_empty(self, data: dict, result: ValidationResult):
        """Проверка на пустые ключевые поля."""
        # Все значения None или пустые
        non_empty = sum(1 for v in data.values() if v not in (None, "", [], {}))
        if non_empty == 0:
            result.add_error("Response is empty — all fields are null/empty")
        elif non_empty <= 1:
            result.add_warning("Response is mostly empty")
