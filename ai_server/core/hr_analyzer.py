"""
HR Analyzer for HR AI Server
Анализ кандидатов, парсинг резюме, расчёт match score
"""

import json
import re
import logging
from typing import Dict, Any, List, Optional

from .llm_engine import LLMEngine
from .models import CandidateProfile, CandidateAnalysis, SkillItem, LanguageItem

logger = logging.getLogger(__name__)


class HRAnalyzer:
    """
    HR Анализатор
    Выполняет парсинг резюме, анализ кандидатов, расчёт соответствия
    """

    # ============== ПРОМПТЫ ==============

    RESUME_PARSING_PROMPT = """Ты — профессиональный HR-аналитик и эксперт по резюме.

ЗАДАЧА: Извлечь из текста резюме структурированный профиль кандидата.

ВАЖНЫЕ ПРАВИЛА:
1. Оценивай ТОЛЬКО профессиональные качества
2. ЗАПРЕЩЕНО учитывать: пол, возраст, национальность, религию, семейное положение
3. Извлекай только факты, которые явно указаны в резюме
4. Если информация не указана - оставляй null или пустой массив

ФОРМАТ ОТВЕТА (строго валидный JSON, без комментариев):
{
  "position_title": "Желаемая должность или последняя должность",
  "years_of_experience": 0.0,
  "skills": [
    {"name": "Название навыка", "level": "strong|middle|basic"}
  ],
  "languages": [
    {"name": "Язык", "level": "A1|A2|B1|B2|C1|C2|native"}
  ],
  "domains": ["Сфера деятельности"],
  "education": [
    {"degree": "Степень", "specialization": "Специальность", "institution": "ВУЗ", "year": 2020}
  ],
  "management_experience": false,
  "remote_experience": false,
  "summary": "Краткое описание кандидата в 2-3 предложения",
  "contact_info": {
    "email": "email@example.com",
    "phone": "+998901234567",
    "location": "Город"
  }
}

Уровни навыков:
- strong: эксперт, 3+ года активного использования
- middle: уверенное владение, 1-3 года
- basic: базовые знания, до 1 года

ТЕКСТ РЕЗЮМЕ:
"""

    ANALYSIS_PROMPT = """Ты — опытный HR-аналитик с 15-летним стажем подбора персонала.

ЗАДАЧА: Проанализировать соответствие кандидата вакансии.

ВАЖНЫЕ ПРАВИЛА:
1. Оценивай ТОЛЬКО: навыки, опыт, образование, компетенции
2. ЗАПРЕЩЕНО учитывать: возраст, пол, национальность, религию
3. Будь объективным и конструктивным
4. Помни: финальное решение всегда за HR-менеджером

ФОРМАТ ОТВЕТА (строго валидный JSON):
{
  "strengths": [
    "Сильная сторона 1 (конкретно для этой вакансии)",
    "Сильная сторона 2",
    "Сильная сторона 3"
  ],
  "weaknesses": [
    "Область для развития 1",
    "Область для развития 2"
  ],
  "risks": [
    "Потенциальный риск (если есть)"
  ],
  "suggested_questions": [
    "Вопрос для интервью 1 (конкретный, по навыкам)",
    "Вопрос 2 (ситуационный, STAR формат)",
    "Вопрос 3 (на soft skills)",
    "Вопрос 4",
    "Вопрос 5"
  ],
  "recommendation": "Рекомендуется / Условно рекомендуется / Не рекомендуется — с кратким обоснованием в 2-3 предложения"
}

ДАННЫЕ ДЛЯ АНАЛИЗА:
"""

    QUESTIONS_PROMPT = """Ты — опытный интервьюер в IT-компании.

ЗАДАЧА: Составить список вопросов для интервью с кандидатом.

Структура вопросов:
1. Hard skills (технические навыки) - 4 вопроса
2. Soft skills (коммуникация, командная работа) - 3 вопроса
3. Ситуационные вопросы (STAR формат) - 2 вопроса
4. Мотивация и ожидания - 1 вопрос

ФОРМАТ ОТВЕТА (JSON):
{
  "questions": [
    {"category": "hard_skills", "question": "Вопрос..."},
    {"category": "soft_skills", "question": "Вопрос..."},
    {"category": "situational", "question": "Расскажите о ситуации когда..."},
    {"category": "motivation", "question": "Вопрос..."}
  ]
}

ДАННЫЕ:
"""

    def __init__(self, llm_engine: LLMEngine, config: dict = None):
        """
        Инициализация анализатора

        Args:
            llm_engine: Экземпляр LLM Engine
            config: Конфигурация HR модуля
        """
        self.llm = llm_engine
        self.config = config or {}

        # Веса для match score
        weights = self.config.get('match_weights', {})
        self.weight_must_have = weights.get('must_have_skills', 0.5)
        self.weight_nice_to_have = weights.get('nice_to_have_skills', 0.3)
        self.weight_experience = weights.get('experience', 0.2)

    async def parse_resume(self, resume_text: str) -> CandidateProfile:
        """
        Парсинг резюме в структурированный профиль

        Args:
            resume_text: Текст резюме

        Returns:
            CandidateProfile
        """
        prompt = self.RESUME_PARSING_PROMPT + resume_text

        try:
            data = await self.llm.generate_json(prompt)
            logger.info(f"Resume parsed successfully")

            # Преобразуем в CandidateProfile
            skills = []
            for s in data.get('skills', []):
                if isinstance(s, dict):
                    skills.append(SkillItem(
                        name=s.get('name', ''),
                        level=s.get('level', 'middle')
                    ))

            languages = []
            for l in data.get('languages', []):
                if isinstance(l, dict):
                    languages.append(LanguageItem(
                        name=l.get('name', ''),
                        level=l.get('level', 'B1')
                    ))

            return CandidateProfile(
                position_title=data.get('position_title'),
                years_of_experience=float(data.get('years_of_experience', 0)),
                skills=skills,
                languages=languages,
                domains=data.get('domains', []),
                education=data.get('education', []),
                management_experience=bool(data.get('management_experience', False)),
                remote_experience=bool(data.get('remote_experience', False)),
                summary=data.get('summary'),
                contact_info=data.get('contact_info')
            )

        except json.JSONDecodeError as e:
            logger.error(f"JSON parsing error: {e}")
            raise ValueError(f"Не удалось распарсить ответ LLM: {e}")
        except Exception as e:
            logger.error(f"Resume parsing error: {e}")
            raise

    async def analyze_candidate(
        self,
        profile: Dict[str, Any],
        vacancy: Dict[str, Any]
    ) -> CandidateAnalysis:
        """
        Анализ кандидата под вакансию

        Args:
            profile: Профиль кандидата (dict)
            vacancy: Данные вакансии (dict)

        Returns:
            CandidateAnalysis
        """
        context = self._build_analysis_context(profile, vacancy)
        prompt = self.ANALYSIS_PROMPT + context

        try:
            data = await self.llm.generate_json(prompt)

            # Рассчитываем match score
            match_score = self.calculate_match_score(profile, vacancy)

            return CandidateAnalysis(
                strengths=data.get('strengths', []),
                weaknesses=data.get('weaknesses', []),
                risks=data.get('risks', []),
                suggested_questions=data.get('suggested_questions', []),
                recommendation=data.get('recommendation', ''),
                match_score=match_score
            )

        except Exception as e:
            logger.error(f"Analysis error: {e}")
            raise

    def calculate_match_score(
        self,
        profile: Dict[str, Any],
        vacancy: Dict[str, Any]
    ) -> int:
        """
        Расчёт match score (0-100)

        Формула:
        - 50% - покрытие must_have_skills
        - 30% - покрытие nice_to_have_skills
        - 20% - соответствие опыта
        """
        # Извлекаем навыки кандидата
        candidate_skills = set()
        for skill in profile.get('skills', []):
            if isinstance(skill, dict):
                name = skill.get('name', '')
            else:
                name = str(skill)
            candidate_skills.add(name.lower().strip())

        # Must-have coverage
        must_have = vacancy.get('must_have_skills', [])
        if must_have:
            must_have_lower = [s.lower().strip() for s in must_have]
            must_coverage = sum(1 for s in must_have_lower if self._skill_match(s, candidate_skills)) / len(must_have)
        else:
            must_coverage = 1.0

        # Nice-to-have coverage
        nice_to_have = vacancy.get('nice_to_have_skills', [])
        if nice_to_have:
            nice_lower = [s.lower().strip() for s in nice_to_have]
            nice_coverage = sum(1 for s in nice_lower if self._skill_match(s, candidate_skills)) / len(nice_to_have)
        else:
            nice_coverage = 1.0

        # Experience score
        min_exp = vacancy.get('min_experience_years') or 0
        candidate_exp = profile.get('years_of_experience') or 0
        if min_exp > 0:
            exp_score = min(1.0, candidate_exp / min_exp)
        else:
            exp_score = 1.0

        # Итоговый score
        total = (
            self.weight_must_have * must_coverage +
            self.weight_nice_to_have * nice_coverage +
            self.weight_experience * exp_score
        ) * 100

        return int(round(min(100, max(0, total))))

    def _skill_match(self, required_skill: str, candidate_skills: set) -> bool:
        """Проверка соответствия навыка (с учётом синонимов)"""
        # Прямое совпадение
        if required_skill in candidate_skills:
            return True

        # Частичное совпадение
        for skill in candidate_skills:
            if required_skill in skill or skill in required_skill:
                return True

        # Синонимы
        synonyms = {
            'javascript': {'js', 'ecmascript'},
            'typescript': {'ts'},
            'python': {'py', 'python3'},
            'postgresql': {'postgres', 'psql', 'pgsql'},
            'mysql': {'mariadb'},
            'mongodb': {'mongo'},
            'react': {'reactjs', 'react.js'},
            'vue': {'vuejs', 'vue.js'},
            'angular': {'angularjs'},
            'node': {'nodejs', 'node.js'},
            'docker': {'containerization'},
            'kubernetes': {'k8s'},
            'aws': {'amazon web services'},
            'gcp': {'google cloud'},
            'azure': {'microsoft azure'},
        }

        if required_skill in synonyms:
            return bool(synonyms[required_skill] & candidate_skills)

        return False

    def _build_analysis_context(self, profile: Dict[str, Any], vacancy: Dict[str, Any]) -> str:
        """Построение контекста для анализа"""
        # Форматируем навыки
        skills_str = ', '.join([
            f"{s.get('name', s)} ({s.get('level', 'middle')})"
            if isinstance(s, dict) else str(s)
            for s in profile.get('skills', [])
        ])

        context = f"""
ПРОФИЛЬ КАНДИДАТА:
- Позиция: {profile.get('position_title', 'Не указана')}
- Опыт работы: {profile.get('years_of_experience', 0)} лет
- Навыки: {skills_str}
- Домены/отрасли: {', '.join(profile.get('domains', []))}
- Опыт управления: {'Да' if profile.get('management_experience') else 'Нет'}
- Удалённый опыт: {'Да' if profile.get('remote_experience') else 'Нет'}
- Краткое описание: {profile.get('summary', 'Не указано')}

ВАКАНСИЯ:
- Название: {vacancy.get('title', 'Не указано')}
- Описание: {vacancy.get('description', 'Не указано')}
- Обязательные навыки: {', '.join(vacancy.get('must_have_skills', []))}
- Желательные навыки: {', '.join(vacancy.get('nice_to_have_skills', []))}
- Минимальный опыт: {vacancy.get('min_experience_years', 'Не указан')} лет
- Тип занятости: {vacancy.get('employment_type', 'Не указан')}
- Локация: {vacancy.get('location', 'Не указана')}
"""
        return context

    async def generate_interview_questions(
        self,
        profile: Dict[str, Any],
        vacancy: Dict[str, Any],
        count: int = 10,
        focus_areas: List[str] = None
    ) -> List[str]:
        """
        Генерация вопросов для интервью

        Args:
            profile: Профиль кандидата
            vacancy: Данные вакансии
            count: Количество вопросов
            focus_areas: Области для фокуса

        Returns:
            Список вопросов
        """
        context = self._build_analysis_context(profile, vacancy)

        if focus_areas:
            context += f"\n\nОСОБОЕ ВНИМАНИЕ: {', '.join(focus_areas)}"

        context += f"\n\nКоличество вопросов: {count}"

        prompt = self.QUESTIONS_PROMPT + context

        try:
            data = await self.llm.generate_json(prompt)
            questions = data.get('questions', [])

            # Извлекаем только текст вопросов
            result = []
            for q in questions:
                if isinstance(q, dict):
                    result.append(q.get('question', ''))
                else:
                    result.append(str(q))

            return [q for q in result if q][:count]

        except Exception as e:
            logger.error(f"Questions generation error: {e}")
            return []

    async def generate_rejection_email(
        self,
        vacancy_title: str,
        candidate_name: str = "Кандидат"
    ) -> str:
        """
        Генерация вежливого письма-отказа

        Args:
            vacancy_title: Название вакансии
            candidate_name: Имя кандидата

        Returns:
            Текст письма
        """
        prompt = f"""Напиши вежливое и профессиональное письмо-отказ кандидату.

Имя кандидата: {candidate_name}
Вакансия: {vacancy_title}

Требования к письму:
1. Поблагодари за интерес к вакансии
2. Вежливо сообщи, что выбран другой кандидат
3. НЕ указывай конкретные причины отказа
4. Пожелай успехов в поиске работы
5. Предложи подписаться на вакансии компании

Письмо должно быть на русском языке, 3-4 коротких абзаца."""

        try:
            return await self.llm.generate(prompt)
        except Exception as e:
            logger.error(f"Rejection email generation error: {e}")
            return f"""Уважаемый(ая) {candidate_name},

Благодарим вас за интерес к позиции «{vacancy_title}» в нашей компании.

К сожалению, на данном этапе мы приняли решение продолжить процесс с другими кандидатами.

Желаем вам успехов в поиске работы и профессиональном развитии!

С уважением,
HR-команда"""

    def get_match_breakdown(
        self,
        profile: Dict[str, Any],
        vacancy: Dict[str, Any]
    ) -> Dict[str, float]:
        """
        Детальная разбивка match score

        Returns:
            Dict с компонентами score
        """
        candidate_skills = set()
        for skill in profile.get('skills', []):
            if isinstance(skill, dict):
                name = skill.get('name', '')
            else:
                name = str(skill)
            candidate_skills.add(name.lower().strip())

        # Must-have
        must_have = vacancy.get('must_have_skills', [])
        if must_have:
            must_have_lower = [s.lower().strip() for s in must_have]
            must_matched = [s for s in must_have_lower if self._skill_match(s, candidate_skills)]
            must_coverage = len(must_matched) / len(must_have)
        else:
            must_coverage = 1.0
            must_matched = []

        # Nice-to-have
        nice_to_have = vacancy.get('nice_to_have_skills', [])
        if nice_to_have:
            nice_lower = [s.lower().strip() for s in nice_to_have]
            nice_matched = [s for s in nice_lower if self._skill_match(s, candidate_skills)]
            nice_coverage = len(nice_matched) / len(nice_to_have)
        else:
            nice_coverage = 1.0
            nice_matched = []

        # Experience
        min_exp = vacancy.get('min_experience_years') or 0
        candidate_exp = profile.get('years_of_experience') or 0
        if min_exp > 0:
            exp_score = min(1.0, candidate_exp / min_exp)
        else:
            exp_score = 1.0

        return {
            'must_have_coverage': round(must_coverage * 100, 1),
            'must_have_matched': must_matched,
            'must_have_total': len(must_have),
            'nice_to_have_coverage': round(nice_coverage * 100, 1),
            'nice_to_have_matched': nice_matched,
            'nice_to_have_total': len(nice_to_have),
            'experience_score': round(exp_score * 100, 1),
            'candidate_experience': candidate_exp,
            'required_experience': min_exp,
            'total_score': self.calculate_match_score(profile, vacancy)
        }
