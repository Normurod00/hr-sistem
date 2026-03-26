"""
LLM Engine for HR AI Server
Поддержка локальных моделей через ctransformers
"""

import os
import json
import asyncio
import logging
from typing import Optional, Dict, Any
from abc import ABC, abstractmethod

logger = logging.getLogger(__name__)


class BaseLLMProvider(ABC):
    """Базовый класс для LLM провайдеров"""

    @abstractmethod
    async def generate(self, prompt: str, **kwargs) -> str:
        pass

    @abstractmethod
    def is_available(self) -> bool:
        pass


class LocalLLMProvider(BaseLLMProvider):
    """
    Локальный LLM через ctransformers
    Поддерживает GGUF модели (Mistral, Llama, etc.)
    """

    def __init__(self, config: Dict[str, Any]):
        self.config = config
        self.model = None
        self._load_model()

    def _load_model(self):
        """Загрузка модели"""
        try:
            from ctransformers import AutoModelForCausalLM

            model_path = self.config.get('model_path', '')
            model_type = self.config.get('model_type', 'mistral')

            if not os.path.exists(model_path):
                logger.warning(f"Model not found at {model_path}")
                return

            logger.info(f"Loading local model: {model_path}")

            self.model = AutoModelForCausalLM.from_pretrained(
                model_path,
                model_type=model_type,
                context_length=self.config.get('context_length', 4096),
                threads=self.config.get('threads', 4),
                gpu_layers=self.config.get('gpu_layers', 0)
            )

            logger.info("Local model loaded successfully")

        except ImportError:
            logger.error("ctransformers not installed. Run: pip install ctransformers")
        except Exception as e:
            logger.error(f"Failed to load local model: {e}")

    def is_available(self) -> bool:
        return self.model is not None

    async def generate(self, prompt: str, **kwargs) -> str:
        if not self.model:
            raise RuntimeError("Local model not loaded")

        max_tokens = kwargs.get('max_tokens', self.config.get('max_tokens', 2048))
        temperature = kwargs.get('temperature', self.config.get('temperature', 0.3))

        # Запускаем в executor чтобы не блокировать event loop
        loop = asyncio.get_event_loop()
        response = await loop.run_in_executor(
            None,
            lambda: self.model(
                prompt,
                max_new_tokens=max_tokens,
                temperature=temperature,
                stop=["</s>", "[INST]", "[/INST]"]
            )
        )

        return response.strip()


class OllamaProvider(BaseLLMProvider):
    """Ollama API провайдер"""

    def __init__(self, config: Dict[str, Any]):
        self.host = config.get('host', 'http://127.0.0.1:11434')
        self.model = config.get('model', 'mistral')
        self.timeout = config.get('timeout', 120)
        self._available = None

    def is_available(self) -> bool:
        if self._available is not None:
            return self._available

        import httpx
        try:
            response = httpx.get(f"{self.host}/api/tags", timeout=5)
            self._available = response.status_code == 200
        except Exception:
            self._available = False

        return self._available

    async def generate(self, prompt: str, **kwargs) -> str:
        import httpx

        async with httpx.AsyncClient(timeout=self.timeout) as client:
            response = await client.post(
                f"{self.host}/api/generate",
                json={
                    "model": self.model,
                    "prompt": prompt,
                    "stream": False,
                    "options": {
                        "temperature": kwargs.get('temperature', 0.3),
                        "num_predict": kwargs.get('max_tokens', 2048)
                    }
                }
            )
            response.raise_for_status()
            data = response.json()
            return data.get('response', '').strip()


class OpenAIProvider(BaseLLMProvider):
    """OpenAI API провайдер (fallback)"""

    def __init__(self, config: Dict[str, Any]):
        self.api_key = config.get('api_key') or os.getenv('OPENAI_API_KEY')
        self.model = config.get('model', 'gpt-4o-mini')
        self.max_tokens = config.get('max_tokens', 4096)

    def is_available(self) -> bool:
        return bool(self.api_key)

    async def generate(self, prompt: str, **kwargs) -> str:
        import httpx

        headers = {
            "Authorization": f"Bearer {self.api_key}",
            "Content-Type": "application/json"
        }

        # Определяем, нужен ли JSON режим
        json_mode = kwargs.get('json_mode', False)

        payload = {
            "model": self.model,
            "messages": [
                {"role": "user", "content": prompt}
            ],
            "temperature": kwargs.get('temperature', 0.3),
            "max_tokens": kwargs.get('max_tokens', self.max_tokens)
        }

        if json_mode:
            payload["response_format"] = {"type": "json_object"}

        async with httpx.AsyncClient(timeout=60) as client:
            response = await client.post(
                "https://api.openai.com/v1/chat/completions",
                headers=headers,
                json=payload
            )
            response.raise_for_status()
            data = response.json()
            return data['choices'][0]['message']['content'].strip()


class LLMEngine:
    """
    Главный класс для работы с LLM
    Автоматически выбирает доступный провайдер
    """

    def __init__(self, config: Dict[str, Any]):
        self.config = config
        self.provider_name = config.get('provider', 'local')
        self.provider: Optional[BaseLLMProvider] = None
        self._init_provider()

    def _init_provider(self):
        """Инициализация провайдера"""
        llm_config = self.config.get('llm', {})
        provider_name = llm_config.get('provider', 'local')

        providers = []

        # Добавляем провайдеры в порядке приоритета
        if provider_name == 'local':
            providers.append(('local', LocalLLMProvider(llm_config.get('local', {}))))
            providers.append(('ollama', OllamaProvider(llm_config.get('ollama', {}))))
            providers.append(('openai', OpenAIProvider(llm_config.get('openai', {}))))

        elif provider_name == 'ollama':
            providers.append(('ollama', OllamaProvider(llm_config.get('ollama', {}))))
            providers.append(('local', LocalLLMProvider(llm_config.get('local', {}))))
            providers.append(('openai', OpenAIProvider(llm_config.get('openai', {}))))

        elif provider_name == 'openai':
            providers.append(('openai', OpenAIProvider(llm_config.get('openai', {}))))

        # Выбираем первый доступный
        for name, provider in providers:
            if provider.is_available():
                self.provider = provider
                self.provider_name = name
                logger.info(f"Using LLM provider: {name}")
                return

        logger.warning("No LLM provider available!")

    def is_available(self) -> bool:
        return self.provider is not None and self.provider.is_available()

    def get_provider_info(self) -> Dict[str, str]:
        """Информация о текущем провайдере"""
        if not self.provider:
            return {"provider": "none", "model": "none"}

        if isinstance(self.provider, LocalLLMProvider):
            return {
                "provider": "local",
                "model": self.config.get('llm', {}).get('local', {}).get('model_path', 'unknown')
            }
        elif isinstance(self.provider, OllamaProvider):
            return {
                "provider": "ollama",
                "model": self.provider.model
            }
        elif isinstance(self.provider, OpenAIProvider):
            return {
                "provider": "openai",
                "model": self.provider.model
            }

        return {"provider": "unknown", "model": "unknown"}

    async def generate(self, prompt: str, json_mode: bool = False, **kwargs) -> str:
        """
        Генерация ответа

        Args:
            prompt: Текст промпта
            json_mode: Ожидать JSON ответ
            **kwargs: Дополнительные параметры (temperature, max_tokens)

        Returns:
            Сгенерированный текст
        """
        if not self.provider:
            raise RuntimeError("No LLM provider available")

        kwargs['json_mode'] = json_mode
        return await self.provider.generate(prompt, **kwargs)

    async def generate_json(self, prompt: str, **kwargs) -> Dict[str, Any]:
        """
        Генерация и парсинг JSON ответа

        Args:
            prompt: Промпт с инструкцией вернуть JSON

        Returns:
            Распарсенный словарь
        """
        response = await self.generate(prompt, json_mode=True, **kwargs)

        # Пытаемся извлечь JSON
        response = response.strip()

        # Убираем markdown code blocks
        if response.startswith('```'):
            lines = response.split('\n')
            json_lines = []
            in_block = False
            for line in lines:
                if line.startswith('```'):
                    in_block = not in_block
                    continue
                if in_block or not line.startswith('```'):
                    json_lines.append(line)
            response = '\n'.join(json_lines)

        # Ищем JSON объект
        start = response.find('{')
        end = response.rfind('}')
        if start != -1 and end != -1:
            response = response[start:end + 1]

        return json.loads(response)
