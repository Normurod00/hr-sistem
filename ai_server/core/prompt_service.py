"""
Prompt Service — загрузка и рендеринг промптов из YAML файлов.

Промпты хранятся в ai_server/prompts/*.yaml.
Каждый файл содержит system_prompt и user_prompt_template.
"""

import os
import logging
from typing import Optional
import yaml

logger = logging.getLogger(__name__)

PROMPTS_DIR = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "prompts")


class PromptService:
    """Сервис загрузки и рендеринга промптов."""

    def __init__(self):
        self._cache: dict = {}
        self._load_all()

    def _load_all(self):
        """Загрузить все промпты из директории."""
        if not os.path.isdir(PROMPTS_DIR):
            logger.warning(f"Prompts directory not found: {PROMPTS_DIR}")
            return

        for filename in os.listdir(PROMPTS_DIR):
            if filename.endswith(".yaml") or filename.endswith(".yml"):
                key = filename.rsplit(".", 1)[0]
                path = os.path.join(PROMPTS_DIR, filename)
                try:
                    with open(path, "r", encoding="utf-8") as f:
                        self._cache[key] = yaml.safe_load(f)
                    logger.info(f"Loaded prompt: {key}")
                except Exception as e:
                    logger.error(f"Failed to load prompt {filename}: {e}")

        logger.info(f"Total prompts loaded: {len(self._cache)}")

    def get_system_prompt(self, key: str) -> str:
        """Получить system prompt по ключу."""
        prompt_data = self._cache.get(key)
        if not prompt_data:
            logger.warning(f"Prompt not found: {key}")
            return "You are a helpful HR AI assistant. Return JSON."
        return prompt_data.get("system_prompt", "")

    def get_user_template(self, key: str) -> str:
        """Получить шаблон user prompt."""
        prompt_data = self._cache.get(key)
        if not prompt_data:
            return "{input_data}"
        return prompt_data.get("user_prompt_template", "{input_data}")

    def render_user_prompt(self, key: str, **kwargs) -> str:
        """Рендерить user prompt с подстановкой переменных."""
        template = self.get_user_template(key)
        try:
            return template.format(**kwargs)
        except KeyError as e:
            logger.warning(f"Missing template variable for {key}: {e}")
            return template

    def get_output_schema(self, key: str) -> Optional[dict]:
        """Получить ожидаемую JSON schema ответа."""
        prompt_data = self._cache.get(key)
        if not prompt_data:
            return None
        return prompt_data.get("output_schema")

    def reload(self):
        """Перезагрузить промпты (hot reload)."""
        self._cache.clear()
        self._load_all()

    def list_prompts(self) -> list:
        return list(self._cache.keys())
