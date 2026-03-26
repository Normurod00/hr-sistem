"""
Rule-Based HR Analyzer - УЛУЧШЕННАЯ ВЕРСИЯ
Анализатор резюме и кандидатов на основе правил (без LLM)
Быстрый, точный, без внешних API
"""

import re
import logging
from typing import Dict, Any, List, Optional, Set, Tuple, Union
from datetime import datetime
from difflib import SequenceMatcher

from .models import CandidateProfile, CandidateAnalysis, SkillItem, LanguageItem

logger = logging.getLogger(__name__)


class RuleBasedAnalyzer:
    """
    HR Анализатор на основе правил - УЛУЧШЕННЫЙ
    Не требует LLM, работает на regex и паттернах
    + Нечеткое сравнение строк
    + Расширенные словари
    + Контекстный анализ
    """

    # ============== РАСШИРЕННЫЕ СЛОВАРИ И ПАТТЕРНЫ ==============

    # Технические навыки с категориями (РАСШИРЕНО)
    TECH_SKILLS = {
        # Языки программирования
        'python': {'category': 'programming', 'aliases': ['python3', 'py', 'пайтон', 'питон'], 'weight': 1.0},
        'javascript': {'category': 'programming', 'aliases': ['js', 'ecmascript', 'джаваскрипт', 'яваскрипт'], 'weight': 1.0},
        'typescript': {'category': 'programming', 'aliases': ['ts', 'тайпскрипт'], 'weight': 1.0},
        'java': {'category': 'programming', 'aliases': ['джава'], 'weight': 1.0},
        'c#': {'category': 'programming', 'aliases': ['csharp', 'c sharp', 'шарп', 'си шарп'], 'weight': 1.0},
        'c++': {'category': 'programming', 'aliases': ['cpp', 'cplusplus', 'си++'], 'weight': 1.0},
        'go': {'category': 'programming', 'aliases': ['golang', 'го'], 'weight': 1.0},
        'rust': {'category': 'programming', 'aliases': ['раст'], 'weight': 1.0},
        'php': {'category': 'programming', 'aliases': ['пхп', 'пи-эйч-пи'], 'weight': 1.0},
        'ruby': {'category': 'programming', 'aliases': ['руби'], 'weight': 1.0},
        'swift': {'category': 'programming', 'aliases': ['свифт'], 'weight': 1.0},
        'kotlin': {'category': 'programming', 'aliases': ['котлин'], 'weight': 1.0},
        'scala': {'category': 'programming', 'aliases': ['скала'], 'weight': 1.0},
        'perl': {'category': 'programming', 'aliases': ['перл'], 'weight': 0.8},
        'r': {'category': 'programming', 'aliases': ['r language'], 'weight': 0.9},
        'dart': {'category': 'programming', 'aliases': ['дарт'], 'weight': 0.9},
        'elixir': {'category': 'programming', 'aliases': ['эликсир'], 'weight': 0.9},
        'haskell': {'category': 'programming', 'aliases': ['хаскель'], 'weight': 0.8},

        # Фреймворки Backend
        'django': {'category': 'backend', 'aliases': ['джанго'], 'weight': 1.0},
        'flask': {'category': 'backend', 'aliases': ['фласк'], 'weight': 1.0},
        'fastapi': {'category': 'backend', 'aliases': ['fast api', 'фаст апи'], 'weight': 1.0},
        'spring': {'category': 'backend', 'aliases': ['spring boot', 'springboot', 'спринг'], 'weight': 1.0},
        'express': {'category': 'backend', 'aliases': ['expressjs', 'express.js', 'экспресс'], 'weight': 1.0},
        'nestjs': {'category': 'backend', 'aliases': ['nest.js', 'nest', 'нест'], 'weight': 1.0},
        'laravel': {'category': 'backend', 'aliases': ['ларавел'], 'weight': 1.0},
        'symfony': {'category': 'backend', 'aliases': ['симфони'], 'weight': 1.0},
        'rails': {'category': 'backend', 'aliases': ['ruby on rails', 'ror', 'рельсы'], 'weight': 1.0},
        'asp.net': {'category': 'backend', 'aliases': ['aspnet', '.net core', 'dotnet', 'дотнет'], 'weight': 1.0},
        'struts': {'category': 'backend', 'aliases': ['apache struts'], 'weight': 0.8},
        'tornado': {'category': 'backend', 'aliases': ['торнадо'], 'weight': 0.8},
        'bottle': {'category': 'backend', 'aliases': ['ботл'], 'weight': 0.7},

        # Фреймворки Frontend
        'react': {'category': 'frontend', 'aliases': ['reactjs', 'react.js', 'реакт'], 'weight': 1.0},
        'vue': {'category': 'frontend', 'aliases': ['vuejs', 'vue.js', 'вью'], 'weight': 1.0},
        'angular': {'category': 'frontend', 'aliases': ['angularjs', 'angular.js', 'ангуляр'], 'weight': 1.0},
        'svelte': {'category': 'frontend', 'aliases': ['свелт'], 'weight': 0.9},
        'next.js': {'category': 'frontend', 'aliases': ['nextjs', 'next', 'некст'], 'weight': 1.0},
        'nuxt': {'category': 'frontend', 'aliases': ['nuxtjs', 'nuxt.js', 'накст'], 'weight': 0.9},
        'ember': {'category': 'frontend', 'aliases': ['emberjs', 'ember.js'], 'weight': 0.8},
        'backbone': {'category': 'frontend', 'aliases': ['backbonejs', 'backbone.js'], 'weight': 0.7},
        'jquery': {'category': 'frontend', 'aliases': ['джейквери'], 'weight': 0.7},
        'bootstrap': {'category': 'frontend', 'aliases': ['бутстрап'], 'weight': 0.8},
        'tailwind': {'category': 'frontend', 'aliases': ['tailwindcss', 'тейлвинд'], 'weight': 0.9},
        'material-ui': {'category': 'frontend', 'aliases': ['mui', 'material ui'], 'weight': 0.8},

        # Базы данных
        'postgresql': {'category': 'database', 'aliases': ['postgres', 'psql', 'pgsql', 'постгрес', 'постгресql'], 'weight': 1.0},
        'mysql': {'category': 'database', 'aliases': ['mariadb', 'мускул', 'мысql'], 'weight': 1.0},
        'mongodb': {'category': 'database', 'aliases': ['mongo', 'монго'], 'weight': 1.0},
        'redis': {'category': 'database', 'aliases': ['редис'], 'weight': 1.0},
        'elasticsearch': {'category': 'database', 'aliases': ['elastic', 'эластик', 'elastic search'], 'weight': 1.0},
        'oracle': {'category': 'database', 'aliases': ['oracle db', 'оракл'], 'weight': 1.0},
        'mssql': {'category': 'database', 'aliases': ['sql server', 'ms sql', 'microsoft sql'], 'weight': 1.0},
        'sqlite': {'category': 'database', 'aliases': ['sq lite'], 'weight': 0.8},
        'cassandra': {'category': 'database', 'aliases': ['кассандра'], 'weight': 0.9},
        'couchdb': {'category': 'database', 'aliases': ['couch db'], 'weight': 0.8},
        'dynamodb': {'category': 'database', 'aliases': ['dynamo db', 'amazon dynamodb'], 'weight': 0.9},
        'neo4j': {'category': 'database', 'aliases': ['neo 4j'], 'weight': 0.8},

        # DevOps & Cloud
        'docker': {'category': 'devops', 'aliases': ['докер', 'контейнер', 'контейнеризация'], 'weight': 1.0},
        'kubernetes': {'category': 'devops', 'aliases': ['k8s', 'кубер', 'кубернетес', 'кубернетис'], 'weight': 1.0},
        'jenkins': {'category': 'devops', 'aliases': ['дженкинс'], 'weight': 1.0},
        'gitlab ci': {'category': 'devops', 'aliases': ['gitlab-ci', 'gitlab ci/cd', 'гитлаб'], 'weight': 1.0},
        'github actions': {'category': 'devops', 'aliases': ['github action', 'гитхаб экшенс'], 'weight': 0.9},
        'ansible': {'category': 'devops', 'aliases': ['ансибл', 'ансибле'], 'weight': 1.0},
        'terraform': {'category': 'devops', 'aliases': ['терраформ'], 'weight': 1.0},
        'nginx': {'category': 'devops', 'aliases': ['нгинкс', 'энжинкс'], 'weight': 1.0},
        'apache': {'category': 'devops', 'aliases': ['апач', 'apache httpd'], 'weight': 0.9},
        'linux': {'category': 'devops', 'aliases': ['линукс', 'ubuntu', 'debian', 'centos', 'rhel'], 'weight': 1.0},
        'aws': {'category': 'cloud', 'aliases': ['amazon web services', 'амазон', 'amazon aws'], 'weight': 1.0},
        'gcp': {'category': 'cloud', 'aliases': ['google cloud', 'гугл клауд', 'google cloud platform'], 'weight': 1.0},
        'azure': {'category': 'cloud', 'aliases': ['microsoft azure', 'азур', 'azure cloud'], 'weight': 1.0},
        'heroku': {'category': 'cloud', 'aliases': ['хероку'], 'weight': 0.8},
        'digitalocean': {'category': 'cloud', 'aliases': ['digital ocean', 'диджитал оушен'], 'weight': 0.8},

        # Мобильная разработка
        'android': {'category': 'mobile', 'aliases': ['андроид'], 'weight': 1.0},
        'ios': {'category': 'mobile', 'aliases': ['айос'], 'weight': 1.0},
        'flutter': {'category': 'mobile', 'aliases': ['флаттер'], 'weight': 1.0},
        'react native': {'category': 'mobile', 'aliases': ['rn', 'реакт нейтив'], 'weight': 1.0},
        'xamarin': {'category': 'mobile', 'aliases': ['ксамарин'], 'weight': 0.8},
        'cordova': {'category': 'mobile', 'aliases': ['phonegap'], 'weight': 0.7},
        'ionic': {'category': 'mobile', 'aliases': ['ионик'], 'weight': 0.8},

        # Messaging & Queue
        'rabbitmq': {'category': 'messaging', 'aliases': ['rabbit', 'рэббит', 'rabbit mq'], 'weight': 1.0},
        'kafka': {'category': 'messaging', 'aliases': ['кафка', 'apache kafka'], 'weight': 1.0},
        'celery': {'category': 'messaging', 'aliases': ['селери'], 'weight': 0.9},
        'activemq': {'category': 'messaging', 'aliases': ['active mq'], 'weight': 0.8},
        'zeromq': {'category': 'messaging', 'aliases': ['zero mq', '0mq'], 'weight': 0.8},

        # Инструменты и технологии
        'git': {'category': 'tools', 'aliases': ['гит', 'github', 'gitlab', 'bitbucket'], 'weight': 1.0},
        'sql': {'category': 'database', 'aliases': ['скл', 'эс-ку-эль'], 'weight': 1.0},
        'rest api': {'category': 'backend', 'aliases': ['rest', 'restful', 'api', 'рест'], 'weight': 1.0},
        'graphql': {'category': 'backend', 'aliases': ['graph ql', 'графкл'], 'weight': 1.0},
        'websocket': {'category': 'backend', 'aliases': ['ws', 'вебсокет', 'web socket'], 'weight': 1.0},
        'grpc': {'category': 'backend', 'aliases': ['grpc'], 'weight': 0.9},
        'soap': {'category': 'backend', 'aliases': ['соап'], 'weight': 0.7},
        'xml': {'category': 'tools', 'aliases': ['икс-эм-эль'], 'weight': 0.8},
        'json': {'category': 'tools', 'aliases': ['джейсон'], 'weight': 0.8},
        'yaml': {'category': 'tools', 'aliases': ['ямл'], 'weight': 0.7},
        'swagger': {'category': 'tools', 'aliases': ['свагер', 'openapi'], 'weight': 0.8},
        'postman': {'category': 'tools', 'aliases': ['постман'], 'weight': 0.7},
        'jira': {'category': 'tools', 'aliases': ['джира'], 'weight': 0.8},
        'confluence': {'category': 'tools', 'aliases': ['конфлюенс'], 'weight': 0.7},
        'trello': {'category': 'tools', 'aliases': ['трелло'], 'weight': 0.7},

        # Testing
        'jest': {'category': 'testing', 'aliases': ['джест'], 'weight': 0.9},
        'pytest': {'category': 'testing', 'aliases': ['пайтест'], 'weight': 0.9},
        'junit': {'category': 'testing', 'aliases': ['джуньт'], 'weight': 0.9},
        'selenium': {'category': 'testing', 'aliases': ['селениум'], 'weight': 0.9},
        'cypress': {'category': 'testing', 'aliases': ['сайпресс'], 'weight': 0.9},
        'mocha': {'category': 'testing', 'aliases': ['мока'], 'weight': 0.8},
        'chai': {'category': 'testing', 'aliases': ['чай'], 'weight': 0.7},
        'phpunit': {'category': 'testing', 'aliases': ['php unit'], 'weight': 0.8},

        # Data Science & ML
        'pandas': {'category': 'data_science', 'aliases': ['пандас'], 'weight': 1.0},
        'numpy': {'category': 'data_science', 'aliases': ['нампай'], 'weight': 1.0},
        'tensorflow': {'category': 'ml', 'aliases': ['tensor flow', 'тензорфлоу'], 'weight': 1.0},
        'pytorch': {'category': 'ml', 'aliases': ['py torch', 'пайторч'], 'weight': 1.0},
        'scikit-learn': {'category': 'ml', 'aliases': ['sklearn', 'scikit'], 'weight': 1.0},
        'keras': {'category': 'ml', 'aliases': ['керас'], 'weight': 0.9},
        'matplotlib': {'category': 'data_science', 'aliases': ['матплотлиб'], 'weight': 0.8},
        'jupyter': {'category': 'data_science', 'aliases': ['jupyter notebook', 'юпитер'], 'weight': 0.9},
    }

    # Soft skills (РАСШИРЕНО)
    SOFT_SKILLS = [
        'лидерство', 'leadership', 'lead', 'тимлид', 'team lead',
        'коммуникация', 'communication', 'коммуникабельность',
        'командная работа', 'teamwork', 'team player', 'работа в команде',
        'управление проектами', 'project management', 'pm', 'проджект менеджмент',
        'agile', 'scrum', 'kanban', 'эджайл', 'скрам', 'канбан',
        'презентация', 'presentation', 'public speaking', 'публичные выступления',
        'аналитическое мышление', 'analytical', 'problem solving', 'решение проблем',
        'тайм-менеджмент', 'time management', 'управление временем',
        'ответственность', 'responsibility', 'ответственный',
        'обучаемость', 'fast learner', 'quick learner', 'быстро обучаюсь',
        'стрессоустойчивость', 'stress resistance', 'стресс',
        'инициативность', 'proactive', 'initiative', 'инициатива',
        'креативность', 'creativity', 'creative thinking', 'творческое мышление',
        'адаптивность', 'adaptability', 'гибкость', 'flexibility',
        'внимание к деталям', 'attention to detail', 'детальность',
        'многозадачность', 'multitasking', 'multi-tasking',
        'клиентоориентированность', 'customer focus', 'работа с клиентами',
        'наставничество', 'mentoring', 'менторство',
    ]

    # Языки (РАСШИРЕНО)
    LANGUAGES = {
        'русский': {'aliases': ['russian', 'рус', 'ru'], 'native_for': ['russia', 'belarus']},
        'узбекский': {'aliases': ['uzbek', "o'zbek", 'узб', 'uz', 'ўзбек'], 'native_for': ['uzbekistan']},
        'английский': {'aliases': ['english', 'англ', 'eng', 'en'], 'native_for': ['usa', 'uk']},
        'немецкий': {'aliases': ['german', 'deutsch', 'нем', 'de'], 'native_for': ['germany']},
        'французский': {'aliases': ['french', 'français', 'франц', 'fr'], 'native_for': ['france']},
        'китайский': {'aliases': ['chinese', 'mandarin', 'кит', 'zh'], 'native_for': ['china']},
        'японский': {'aliases': ['japanese', 'яп', 'jp', '日本語'], 'native_for': ['japan']},
        'корейский': {'aliases': ['korean', 'кор', 'ko', '한국어'], 'native_for': ['korea']},
        'турецкий': {'aliases': ['turkish', 'türkçe', 'тур', 'tr'], 'native_for': ['turkey']},
        'испанский': {'aliases': ['spanish', 'español', 'исп', 'es'], 'native_for': ['spain']},
        'итальянский': {'aliases': ['italian', 'italiano', 'ит', 'it'], 'native_for': ['italy']},
        'казахский': {'aliases': ['kazakh', 'қазақ', 'каз', 'kk'], 'native_for': ['kazakhstan']},
        'таджикский': {'aliases': ['tajik', 'тоҷикӣ', 'тадж', 'tg'], 'native_for': ['tajikistan']},
        'киргизский': {'aliases': ['kyrgyz', 'кырг', 'ky'], 'native_for': ['kyrgyzstan']},
        'туркменский': {'aliases': ['turkmen', 'түрк', 'tm'], 'native_for': ['turkmenistan']},
    }

    # Уровни языка (стандартизировано)
    LANGUAGE_LEVELS = ['a1', 'a2', 'b1', 'b2', 'c1', 'c2', 'native', 'fluent', 'beginner', 'intermediate', 'advanced']

    # Образование (РАСШИРЕНО)
    EDUCATION_KEYWORDS = [
        'университет', 'university', 'институт', 'institute',
        'академия', 'academy', 'колледж', 'college',
        'бакалавр', 'bachelor', 'магистр', 'master', 'phd', 'кандидат наук', 'доктор наук',
        'диплом', 'degree', 'образование', 'education',
        'ташкентский', 'tashkent', 'самаркандский', 'бухарский',
        'тгу', 'туит', 'тату', 'ттеу', 'мирзо улугбек',
    ]

    # Паттерны для опыта (УЛУЧШЕНО - исключаем возраст)
    EXPERIENCE_PATTERNS = [
        # Явное указание опыта
        r'опыт\s*(?:работы)?\s*[:\-–—]?\s*(\d+(?:\.\d+)?)\s*(?:лет|года|год)',
        r'experience\s*[:\-–—]?\s*(\d+(?:\.\d+)?)\s*(?:years?|yrs?)?',
        r'(\d+(?:\.\d+)?)\s*(?:\+\s*)?(?:лет|года|год|years?|yrs?)\s+(?:опыт|experience|работы|стаж)',
        r'(\d+(?:\.\d+)?)\s*(?:лет|года|год|years?)\s+в\s+(?:сфере|области|разработке|it|программировании)',
        r'стаж\s*[:\-–—]?\s*(\d+(?:\.\d+)?)',
        r'работаю\s*(?:уже)?\s*(\d+(?:\.\d+)?)\s*(?:лет|года|год)',
    ]

    # Паттерны которые НЕ являются опытом (возраст и т.д.)
    AGE_CONTEXT_WORDS = ['возраст', 'лет от роду', 'родился', 'родилась', 'мужчина', 'женщина', 'полных']

    # Паттерны для компаний
    COMPANY_PATTERNS = [
        r'(?:работал|work(?:ed)?|опыт)\s+(?:в|at|in)\s+([\w\s\-\.]+(?:corp|inc|ltd|llc|group|team)?)',
        r'(?:компания|company)[:\s]+([\w\s\-\.]+)',
    ]

    def __init__(self, config: dict = None):
        """
        Инициализация анализатора
        """
        self.config = config or {}

        # Веса для match score
        weights = self.config.get('match_weights', {})
        self.weight_must_have = weights.get('must_have_skills', 0.5)
        self.weight_nice_to_have = weights.get('nice_to_have_skills', 0.3)
        self.weight_experience = weights.get('experience', 0.2)

        # Порог для нечеткого сравнения
        self.fuzzy_threshold = self.config.get('fuzzy_threshold', 0.8)

    def parse_resume(self, resume_text: str) -> CandidateProfile:
        """
        Парсинг резюме в структурированный профиль (УЛУЧШЕННЫЙ)

        Args:
            resume_text: Текст резюме

        Returns:
            CandidateProfile
        """
        if not resume_text or len(resume_text.strip()) < 50:
            raise ValueError("Текст резюме слишком короткий")

        text_lower = resume_text.lower()

        # Извлекаем данные с улучшенными алгоритмами
        skills = self._extract_skills(text_lower, resume_text)
        languages = self._extract_languages(text_lower)
        experience_years = self._extract_experience(text_lower)
        education = self._extract_education(resume_text)
        contact_info = self._extract_contacts(resume_text)
        position = self._extract_position(resume_text)
        domains = self._extract_domains(text_lower)
        companies = self._extract_companies(resume_text)

        # Определяем флаги
        has_management = self._has_management_experience(text_lower)
        has_remote = self._has_remote_experience(text_lower)

        # Генерируем summary
        summary = self._generate_summary(skills, experience_years, position, companies)

        return CandidateProfile(
            position_title=position,
            years_of_experience=experience_years,
            skills=skills,
            languages=languages,
            domains=domains,
            education=education,
            management_experience=has_management,
            remote_experience=has_remote,
            summary=summary,
            contact_info=contact_info
        )

    def analyze_candidate(
        self,
        profile: Dict[str, Any],
        vacancy: Dict[str, Any]
    ) -> CandidateAnalysis:
        """
        Анализ кандидата под вакансию (УЛУЧШЕННЫЙ)

        Args:
            profile: Профиль кандидата
            vacancy: Данные вакансии

        Returns:
            CandidateAnalysis
        """
        # Рассчитываем match score и breakdown
        match_result = self.get_match_breakdown(profile, vacancy)
        match_score = match_result['total_score']

        # Анализируем сильные стороны
        strengths = self._analyze_strengths(profile, vacancy, match_result)

        # Анализируем слабые стороны
        weaknesses = self._analyze_weaknesses(profile, vacancy, match_result)

        # Выявляем риски
        risks = self._analyze_risks(profile, vacancy, match_score)

        # Генерируем вопросы для интервью (УЛУЧШЕНО)
        questions = self._generate_questions(profile, vacancy, weaknesses, match_result)

        # Формируем рекомендацию
        recommendation = self._generate_recommendation(match_score, strengths, weaknesses, risks)

        return CandidateAnalysis(
            strengths=strengths,
            weaknesses=weaknesses,
            risks=risks,
            suggested_questions=questions,
            recommendation=recommendation,
            match_score=match_score
        )

    def calculate_match_score(
        self,
        profile: Dict[str, Any],
        vacancy: Dict[str, Any]
    ) -> int:
        """
        Расчёт match score (0-100)
        """
        result = self.get_match_breakdown(profile, vacancy)
        return result['total_score']

    def _sanitize_skill_list(self, skills: Any) -> List[str]:
        """Безопасное преобразование списка навыков"""
        if not skills:
            return []
        if not isinstance(skills, list):
            return []

        result = []
        for skill in skills:
            if skill is None:
                continue
            if isinstance(skill, str):
                cleaned = skill.strip()
                if cleaned:
                    result.append(cleaned)
            elif isinstance(skill, dict):
                name = skill.get('name', '')
                if isinstance(name, str) and name.strip():
                    result.append(name.strip())
        return result

    def get_match_breakdown(
        self,
        profile: Dict[str, Any],
        vacancy: Dict[str, Any]
    ) -> Dict[str, Any]:
        """
        Детальная разбивка match score (УЛУЧШЕНО с нечетким сравнением и валидацией)
        """
        # Валидация входных данных
        if not isinstance(profile, dict):
            profile = {}
        if not isinstance(vacancy, dict):
            vacancy = {}

        # Извлекаем навыки кандидата
        candidate_skills = self._normalize_skills(profile.get('skills', []))

        # Must-have (с нечетким сравнением) - с валидацией
        must_have = self._sanitize_skill_list(vacancy.get('must_have_skills', []))
        must_have_lower = [s.lower() for s in must_have]
        must_matched = [s for s in must_have_lower if self._skill_match_fuzzy(s, candidate_skills)]
        must_coverage = len(must_matched) / len(must_have) if must_have else 1.0

        # Nice-to-have (с нечетким сравнением) - с валидацией
        nice_to_have = self._sanitize_skill_list(vacancy.get('nice_to_have_skills', []))
        nice_lower = [s.lower() for s in nice_to_have]
        nice_matched = [s for s in nice_lower if self._skill_match_fuzzy(s, candidate_skills)]
        nice_coverage = len(nice_matched) / len(nice_to_have) if nice_to_have else 1.0

        # Experience - с валидацией типов
        min_exp = vacancy.get('min_experience_years')
        min_exp = float(min_exp) if min_exp is not None and str(min_exp).replace('.', '').isdigit() else 0.0

        candidate_exp = profile.get('years_of_experience')
        candidate_exp = float(candidate_exp) if candidate_exp is not None and str(candidate_exp).replace('.', '').isdigit() else 0.0

        exp_score = min(1.0, candidate_exp / min_exp) if min_exp > 0 else 1.0

        # Итоговый score
        total = (
            self.weight_must_have * must_coverage +
            self.weight_nice_to_have * nice_coverage +
            self.weight_experience * exp_score
        ) * 100

        return {
            'must_have_coverage': round(must_coverage * 100, 1),
            'must_have_matched': must_matched,
            'must_have_missing': [s for s in must_have_lower if s not in must_matched],
            'must_have_total': len(must_have),
            'nice_to_have_coverage': round(nice_coverage * 100, 1),
            'nice_to_have_matched': nice_matched,
            'nice_to_have_missing': [s for s in nice_lower if s not in nice_matched],
            'nice_to_have_total': len(nice_to_have),
            'experience_score': round(exp_score * 100, 1),
            'candidate_experience': candidate_exp,
            'required_experience': min_exp,
            'total_score': int(round(min(100, max(0, total))))
        }

    # ============== ПРИВАТНЫЕ МЕТОДЫ (УЛУЧШЕННЫЕ) ==============

    def _extract_skills(self, text: str, original_text: str = None) -> List[SkillItem]:
        """Извлечение навыков из текста (УЛУЧШЕНО с контекстным анализом)"""
        found_skills = []
        text_words = set(re.findall(r'\b[\w\+\#\.]+\b', text))

        for skill_name, skill_info in self.TECH_SKILLS.items():
            # Проверяем основное название и алиасы
            if self._skill_present_in_text(skill_name, skill_info.get('aliases', []), text):
                level = self._guess_skill_level(skill_name, text, original_text or text)
                found_skills.append(SkillItem(name=skill_name.title(), level=level))

        # Сортируем по важности (weight)
        found_skills.sort(key=lambda s: self.TECH_SKILLS.get(s.name.lower(), {}).get('weight', 0.5), reverse=True)

        # Убираем дубликаты
        seen = set()
        unique_skills = []
        for skill in found_skills:
            if skill.name.lower() not in seen:
                seen.add(skill.name.lower())
                unique_skills.append(skill)

        return unique_skills

    def _skill_present_in_text(self, skill: str, aliases: List[str], text: str) -> bool:
        """Проверка наличия навыка в тексте (с защитой от ложных срабатываний)"""

        def is_word_boundary_match(pattern: str, text: str) -> bool:
            """Проверяет что pattern является отдельным словом в тексте"""
            # Для коротких навыков (<=3 символов) требуем границы слова
            if len(pattern) <= 3:
                # Ищем как отдельное слово с границами
                regex = r'(?<![a-zA-Zа-яА-ЯёЁ0-9])' + re.escape(pattern) + r'(?![a-zA-Zа-яА-ЯёЁ0-9])'
                return bool(re.search(regex, text, re.IGNORECASE))
            else:
                # Для длинных навыков простое вхождение
                return pattern.lower() in text.lower()

        # Точное совпадение (с проверкой границ для коротких слов)
        if is_word_boundary_match(skill, text):
            return True

        # Проверяем алиасы
        for alias in aliases:
            if is_word_boundary_match(alias, text):
                return True

        # Нечеткое сравнение для длинных навыков
        if len(skill) > 4:
            words = text.split()
            for word in words:
                if self._fuzzy_match(skill, word, threshold=0.85):
                    return True

        return False

    def _safe_float(self, value: Any, default: float = 0.0) -> float:
        """Безопасное преобразование в float"""
        if value is None:
            return default
        if isinstance(value, (int, float)):
            return float(value)
        if isinstance(value, str):
            try:
                # Удаляем лишние символы и пробуем преобразовать
                cleaned = value.strip().replace(',', '.')
                return float(cleaned) if cleaned else default
            except (ValueError, TypeError):
                return default
        return default

    def _get_skill_name(self, skill: Any) -> str:
        """Безопасное извлечение имени навыка из разных форматов"""
        if skill is None:
            return ''
        if isinstance(skill, dict):
            return str(skill.get('name', ''))
        elif hasattr(skill, 'name'):
            return str(skill.name)
        else:
            return str(skill)

    def _fuzzy_match(self, str1: str, str2: str, threshold: float = 0.8) -> bool:
        """Нечеткое сравнение строк с защитой от ложных срабатываний"""
        if not str1 or not str2:
            return False

        s1 = str1.lower().strip()
        s2 = str2.lower().strip()

        # Для коротких строк требуем точное совпадение
        if len(s1) <= 3 or len(s2) <= 3:
            return s1 == s2

        # Для очень разных по длине строк - не сравниваем fuzzy
        len_ratio = min(len(s1), len(s2)) / max(len(s1), len(s2))
        if len_ratio < 0.5:
            return False

        ratio = SequenceMatcher(None, s1, s2).ratio()
        return ratio >= threshold

    def _guess_skill_level(self, skill: str, text: str, original_text: str = None) -> str:
        """Определение уровня навыка по контексту (УЛУЧШЕНО)"""
        # Используем оригинальный текст для сохранения регистра
        search_text = original_text if original_text else text

        # Ищем упоминания уровня рядом с навыком (контекст ±100 символов)
        skill_pos = search_text.lower().find(skill.lower())
        if skill_pos == -1:
            return 'middle'

        context_start = max(0, skill_pos - 100)
        context_end = min(len(search_text), skill_pos + len(skill) + 100)
        context = search_text[context_start:context_end].lower()

        # Паттерны для определения уровня
        strong_patterns = [
            'expert', 'эксперт', 'senior', 'сеньор', 'lead', 'ведущий',
            'advanced', 'продвинутый', 'опытный', 'профессионал',
            'глубокие знания', 'отличное знание', 'excellent', 'mastery'
        ]

        middle_patterns = [
            'middle', 'средний', 'уверенный', 'intermediate',
            'хорошее знание', 'good knowledge', 'confident'
        ]

        basic_patterns = [
            'junior', 'начинающий', 'базовый', 'basic',
            'beginner', 'знаком', 'familiar', 'базовые знания'
        ]

        for pattern in strong_patterns:
            if pattern in context:
                return 'strong'

        for pattern in middle_patterns:
            if pattern in context:
                return 'middle'

        for pattern in basic_patterns:
            if pattern in context:
                return 'basic'

        # Если упоминается опыт в годах
        exp_match = re.search(r'(\d+(?:\.\d+)?)\s*(?:лет|года|год|years?)', context)
        if exp_match:
            years = float(exp_match.group(1))
            if years >= 5:
                return 'strong'
            elif years >= 2:
                return 'middle'
            else:
                return 'basic'

        return 'middle'  # По умолчанию

    def _extract_languages(self, text: str) -> List[LanguageItem]:
        """Извлечение языков (УЛУЧШЕНО)"""
        found_languages = []

        for lang_name, lang_info in self.LANGUAGES.items():
            if self._language_present_in_text(lang_name, lang_info.get('aliases', []), text):
                level = self._guess_language_level(lang_name, text)
                found_languages.append(LanguageItem(name=lang_name.title(), level=level))

        return found_languages

    def _language_present_in_text(self, language: str, aliases: List[str], text: str) -> bool:
        """Проверка наличия языка в тексте (с защитой от ложных срабатываний)"""

        def is_word_match(pattern: str, text: str) -> bool:
            """Проверяет что pattern является отдельным словом"""
            # Для коротких строк (<=3 символов) требуем границы слова
            if len(pattern) <= 3:
                regex = r'(?<![a-zA-Zа-яА-ЯёЁ0-9])' + re.escape(pattern) + r'(?![a-zA-Zа-яА-ЯёЁ0-9])'
                return bool(re.search(regex, text, re.IGNORECASE))
            else:
                return pattern.lower() in text.lower()

        # Проверяем основное название языка
        if is_word_match(language, text):
            return True

        # Проверяем алиасы
        return any(is_word_match(alias, text) for alias in aliases)

    def _guess_language_level(self, language: str, text: str) -> str:
        """Определение уровня языка (УЛУЧШЕНО)"""
        text_lower = text.lower()
        language_lower = language.lower()

        # Ищем контекст вокруг языка (узкий - только строка с языком)
        lang_pos = text_lower.find(language_lower)
        if lang_pos == -1:
            return 'B1'

        # Ищем границы строки (от начала до конца строки с языком)
        line_start = text_lower.rfind('\n', 0, lang_pos) + 1
        line_end = text_lower.find('\n', lang_pos)
        if line_end == -1:
            line_end = len(text_lower)

        # Берём только эту строку (без захвата следующей)
        context = text_lower[line_start:line_end]

        # Сначала проверяем стандартные уровни (a1, a2, b1, b2, c1, c2) - они приоритетнее
        for level in ['c2', 'c1', 'b2', 'b1', 'a2', 'a1']:
            if level in context:
                return level.upper()

        # Потом проверяем ключевые слова
        if any(word in context for word in ['native', 'родной', 'носитель', 'родная']):
            return 'Native'
        if any(word in context for word in ['fluent', 'свободно', 'свободный', 'профессиональный']):
            return 'C1'
        if any(word in context for word in ['advanced', 'продвинутый']):
            return 'B2'
        if any(word in context for word in ['intermediate', 'средний']):
            return 'B1'
        if any(word in context for word in ['beginner', 'начальный', 'базовый', 'начинающий']):
            return 'A1'

        return 'B1'  # По умолчанию

    def _extract_experience(self, text: str) -> float:
        """Извлечение лет опыта (УЛУЧШЕНО с учётом перекрывающихся периодов и исключением возраста)"""
        max_years = 0.0

        # Паттерны для опыта
        for pattern in self.EXPERIENCE_PATTERNS:
            for match in re.finditer(pattern, text, re.IGNORECASE):
                try:
                    # Получаем число из группы
                    years = float(match.group(1))

                    # Проверяем что это не возраст
                    start_pos = max(0, match.start() - 50)
                    context_before = text[start_pos:match.start()].lower()

                    # Если в контексте слова про возраст - пропускаем
                    is_age_context = any(word in context_before for word in self.AGE_CONTEXT_WORDS)
                    if is_age_context:
                        continue

                    if 0 < years < 40:  # Разумные пределы для опыта (не больше 40 лет)
                        max_years = max(max_years, years)
                except (ValueError, IndexError):
                    continue

        # Если не нашли явное указание, пытаемся посчитать по датам работы
        if max_years == 0:
            # Ищем даты в формате "2020-2023" или "2020 - 2023"
            date_ranges = re.findall(r'(20\d{2})\s*[-–—]\s*(20\d{2}|present|now|настоящее|н\.в\.)', text, re.IGNORECASE)

            if date_ranges:
                # Собираем все периоды
                periods = []
                current_year = datetime.now().year

                for start, end in date_ranges:
                    start_year = int(start)
                    end_year = current_year if end.lower() in ['present', 'now', 'настоящее', 'н.в.'] else int(end)

                    if 0 <= end_year - start_year <= 30:
                        periods.append((start_year, end_year))

                # Объединяем перекрывающиеся периоды
                if periods:
                    max_years = self._calculate_merged_experience(periods)

        return round(max_years, 1)

    def _calculate_merged_experience(self, periods: List[Tuple[int, int]]) -> float:
        """Объединяет перекрывающиеся периоды и считает общий опыт"""
        if not periods:
            return 0.0

        # Сортируем по началу периода
        sorted_periods = sorted(periods, key=lambda x: x[0])

        merged = [sorted_periods[0]]

        for current_start, current_end in sorted_periods[1:]:
            last_start, last_end = merged[-1]

            # Если периоды перекрываются или смежные
            if current_start <= last_end + 1:
                # Объединяем периоды
                merged[-1] = (last_start, max(last_end, current_end))
            else:
                # Новый отдельный период
                merged.append((current_start, current_end))

        # Считаем общий опыт
        total_years = sum(end - start for start, end in merged)

        return float(total_years)

    def _extract_education(self, text: str) -> List[Dict]:
        """Извлечение образования (УЛУЧШЕНО)"""
        education = []
        text_lower = text.lower()

        # Ищем упоминания образовательных учреждений
        for keyword in self.EDUCATION_KEYWORDS:
            if keyword in text_lower:
                # Пытаемся найти название учреждения
                pattern = rf'([^\n]*{re.escape(keyword)}[^\n]*)'
                matches = re.findall(pattern, text, re.IGNORECASE)

                for match in matches[:3]:  # Максимум 3 образования
                    # Извлекаем год
                    years = re.findall(r'\b(19\d{2}|20[0-2]\d)\b', match)
                    year = int(max(years)) if years else None

                    # Определяем степень
                    degree = 'Высшее образование'
                    if any(w in match.lower() for w in ['магистр', 'master', "master's"]):
                        degree = 'Магистр'
                    elif any(w in match.lower() for w in ['бакалавр', 'bachelor', "bachelor's"]):
                        degree = 'Бакалавр'
                    elif any(w in match.lower() for w in ['phd', 'кандидат наук', 'доктор']):
                        degree = 'PhD / Кандидат наук'

                    education.append({
                        'degree': degree,
                        'institution': match.strip()[:100],  # Ограничиваем длину
                        'year': year
                    })

                break  # Нашли, прекращаем поиск

        return education

    def _extract_contacts(self, text: str) -> Optional[Dict]:
        """Извлечение контактной информации (УЛУЧШЕНО)"""
        contacts = {}

        # Email (улучшенный паттерн)
        email_match = re.search(r'[\w\.-]+@[\w\.-]+\.[\w]{2,}', text)
        if email_match:
            contacts['email'] = email_match.group()

        # Телефон (улучшенный паттерн для UZ, RU, международных номеров)
        phone_patterns = [
            r'\+998[\s\-]?\d{2}[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}',  # Узбекистан
            r'\+7[\s\-]?\d{3}[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}',    # Россия/Казахстан
            r'\+\d{1,3}[\s\-]?\(?\d{1,4}\)?[\s\-]?\d{1,4}[\s\-]?\d{1,4}[\s\-]?\d{1,9}',  # Международный
        ]

        for pattern in phone_patterns:
            phone_match = re.search(pattern, text)
            if phone_match:
                contacts['phone'] = phone_match.group()
                break

        # Telegram
        tg_patterns = [
            r'@[\w]{5,32}',  # @username
            r't\.me/[\w]{5,32}',  # t.me/username
            r'telegram[:\s]+@?[\w]{5,32}',  # telegram: username
        ]

        for pattern in tg_patterns:
            tg_match = re.search(pattern, text, re.IGNORECASE)
            if tg_match:
                tg = tg_match.group()
                if not tg.startswith('@'):
                    tg = '@' + tg.split('/')[-1]
                contacts['telegram'] = tg
                break

        # LinkedIn
        linkedin_match = re.search(r'linkedin\.com/in/([\w\-]+)', text, re.IGNORECASE)
        if linkedin_match:
            contacts['linkedin'] = f"linkedin.com/in/{linkedin_match.group(1)}"

        return contacts if contacts else None

    def _extract_position(self, text: str) -> Optional[str]:
        """Извлечение желаемой должности (УЛУЧШЕНО)"""
        # Ищем в начале резюме (первые 15 строк)
        lines = text.split('\n')[:15]

        position_patterns = [
            r'(?:должность|позиция|position|vacancy|роль|role)[:\s]+(.+)',
            r'^([\w\s]+(?:developer|разработчик|engineer|инженер|manager|менеджер|analyst|аналитик|designer|дизайнер|архитектор|architect))',
            r'(?:ищу работу|looking for|seeking)\s+(?:as\s+)?(.+)',
            r'(?:резюме|cv|resume)\s*[:\-–—]\s*(.+)',
        ]

        for line in lines:
            line = line.strip()
            if not line or len(line) < 5:
                continue

            for pattern in position_patterns:
                match = re.search(pattern, line, re.IGNORECASE)
                if match:
                    position = match.group(1).strip()
                    # Очистка от лишних символов
                    position = re.sub(r'[|\[\]{}]', '', position)
                    if 5 < len(position) < 100:
                        return position

        return None

    def _extract_domains(self, text: str) -> List[str]:
        """Извлечение доменов/отраслей (РАСШИРЕНО)"""
        domains = []
        domain_keywords = {
            'Fintech': ['fintech', 'финтех', 'банк', 'bank', 'payment', 'платеж', 'финанс', 'finance'],
            'E-commerce': ['ecommerce', 'e-commerce', 'магазин', 'shop', 'retail', 'маркетплейс', 'marketplace'],
            'Healthcare': ['health', 'медицин', 'клиник', 'hospital', 'healthtech', 'фарм'],
            'Education': ['education', 'образовани', 'обучени', 'edtech', 'школ', 'университет'],
            'Logistics': ['logistics', 'логистик', 'доставк', 'delivery', 'транспорт', 'склад'],
            'Telecom': ['telecom', 'телеком', 'связь', 'communication', 'оператор'],
            'GameDev': ['game', 'игр', 'gaming', 'unity', 'unreal', 'геймдев'],
            'AdTech': ['adtech', 'advertising', 'реклам', 'marketing', 'маркетинг'],
            'HRTech': ['hrtech', 'hr tech', 'recruitment', 'рекрутинг', 'подбор персонала'],
            'PropTech': ['proptech', 'real estate', 'недвижим', 'строительств'],
            'LegalTech': ['legaltech', 'legal', 'юридич', 'право'],
            'TravelTech': ['travel', 'tourism', 'туризм', 'booking', 'бронирование'],
        }

        for domain, keywords in domain_keywords.items():
            if any(kw in text for kw in keywords):
                domains.append(domain)

        return domains

    def _extract_companies(self, text: str) -> List[str]:
        """Извлечение названий компаний где работал (НОВОЕ)"""
        companies = []

        for pattern in self.COMPANY_PATTERNS:
            matches = re.findall(pattern, text, re.IGNORECASE)
            for match in matches:
                company = match.strip()
                if 2 < len(company) < 50:
                    companies.append(company)

        # Уникальные компании
        return list(set(companies))[:5]  # Максимум 5

    def _has_management_experience(self, text: str) -> bool:
        """Проверка управленческого опыта"""
        keywords = [
            'lead', 'лид', 'руководител', 'manager', 'менеджер',
            'team lead', 'тимлид', 'head of', 'director', 'директор',
            'управлени', 'cto', 'ceo', 'вице-президент', 'начальник'
        ]
        return any(kw in text for kw in keywords)

    def _has_remote_experience(self, text: str) -> bool:
        """Проверка опыта удалённой работы"""
        keywords = [
            'remote', 'удалённ', 'дистанционн', 'home office',
            'work from home', 'wfh', 'на дому'
        ]
        return any(kw in text for kw in keywords)

    def _generate_summary(self, skills: List[SkillItem], experience: float, position: str, companies: List[str] = None) -> str:
        """Генерация краткого описания (УЛУЧШЕНО)"""
        if not skills:
            return "Кандидат без указанных технических навыков."

        top_skills = [self._get_skill_name(s) for s in skills[:5]]
        skills_str = ', '.join(top_skills)

        exp_str = f"{int(experience)} лет опыта" if experience > 0 else "без указания опыта"

        position_str = position if position else "Специалист"

        summary = f"{position_str} с {exp_str}. Основные навыки: {skills_str}."

        # Добавляем компании если есть
        if companies and len(companies) > 0:
            summary += f" Работал в: {', '.join(companies[:3])}."

        return summary

    def _normalize_skills(self, skills: List) -> Set[str]:
        """Нормализация списка навыков в set"""
        result = set()
        for skill in skills:
            if isinstance(skill, dict):
                name = skill.get('name', '')
            elif hasattr(skill, 'name'):
                name = skill.name
            else:
                name = str(skill)
            result.add(name.lower().strip())
        return result

    def _skill_match_fuzzy(self, required_skill: str, candidate_skills: Set[str]) -> bool:
        """Проверка соответствия навыка с нечетким сравнением (УЛУЧШЕНО)"""
        required_skill = required_skill.lower().strip()

        # 1. Прямое совпадение
        if required_skill in candidate_skills:
            return True

        # 2. Частичное совпадение (с защитой от ложных срабатываний)
        for skill in candidate_skills:
            # Требуем минимальную длину для частичного совпадения
            min_len = min(len(required_skill), len(skill))
            max_len = max(len(required_skill), len(skill))

            # Короткие строки (<=3 символов) должны совпадать точно
            if min_len <= 3:
                continue

            # Частичное совпадение только если короткая строка >= 50% длинной
            if min_len / max_len >= 0.5:
                if required_skill in skill or skill in required_skill:
                    return True

        # 3. Проверка синонимов
        if required_skill in self.TECH_SKILLS:
            aliases = self.TECH_SKILLS[required_skill].get('aliases', [])
            for alias in aliases:
                if alias in candidate_skills:
                    return True

        # 4. Нечеткое сравнение (fuzzy matching)
        for skill in candidate_skills:
            if self._fuzzy_match(required_skill, skill, self.fuzzy_threshold):
                return True

        return False

    def _analyze_strengths(self, profile: Dict, vacancy: Dict, match_result: Dict) -> List[str]:
        """Анализ сильных сторон (УЛУЧШЕНО)"""
        strengths = []

        # 1. Покрытие обязательных навыков
        must_coverage = match_result['must_have_coverage']
        if must_coverage >= 90:
            strengths.append(f"Превосходное покрытие ключевых навыков ({int(must_coverage)}%)")
        elif must_coverage >= 70:
            strengths.append(f"Отличное покрытие ключевых навыков ({int(must_coverage)}%)")
        elif must_coverage >= 50:
            matched = ', '.join(match_result['must_have_matched'][:3])
            strengths.append(f"Владеет ключевыми технологиями: {matched}")

        # 2. Опыт работы
        candidate_exp = self._safe_float(profile.get('years_of_experience', 0))
        required_exp = self._safe_float(vacancy.get('min_experience_years', 0))
        if candidate_exp >= required_exp and candidate_exp > 0:
            if candidate_exp >= required_exp * 2:
                strengths.append(f"Очень богатый опыт работы ({candidate_exp:.1f} лет)")
            elif candidate_exp >= required_exp * 1.5:
                strengths.append(f"Богатый опыт работы ({candidate_exp:.1f} лет)")
            else:
                strengths.append(f"Достаточный опыт работы ({candidate_exp:.1f} лет)")

        # 3. Дополнительные навыки
        nice_matched = match_result.get('nice_to_have_matched', [])
        if len(nice_matched) >= 3:
            nice = ', '.join(nice_matched[:3])
            strengths.append(f"Богатый набор дополнительных навыков: {nice}")
        elif nice_matched:
            nice = ', '.join(nice_matched)
            strengths.append(f"Дополнительные навыки: {nice}")

        # 4. Управленческий опыт
        if profile.get('management_experience'):
            strengths.append("Опыт управления командой и руководства проектами")

        # 5. Удалённая работа
        if profile.get('remote_experience') and 'remote' in vacancy.get('employment_type', '').lower():
            strengths.append("Опыт эффективной удалённой работы")

        # 6. Образование
        education = profile.get('education', [])
        if education:
            for edu in education:
                if isinstance(edu, dict):
                    degree = edu.get('degree', '')
                    if any(w in degree.lower() for w in ['магистр', 'master', 'phd', 'доктор', 'кандидат']):
                        strengths.append(f"Высокий уровень образования: {degree}")
                        break

        # 7. Количество навыков
        skills_count = len(profile.get('skills', []))
        if skills_count >= 15:
            strengths.append(f"Обширный технический стек ({skills_count} навыков)")
        elif skills_count >= 10:
            strengths.append(f"Широкий технический стек ({skills_count} навыков)")

        return strengths[:6]  # Максимум 6 сильных сторон

    def _analyze_weaknesses(self, profile: Dict, vacancy: Dict, match_result: Dict) -> List[str]:
        """Анализ слабых сторон (УЛУЧШЕНО)"""
        weaknesses = []

        # 1. Недостающие обязательные навыки
        missing_must = match_result.get('must_have_missing', [])
        if missing_must:
            if len(missing_must) >= 5:
                missing = ', '.join(missing_must[:3])
                weaknesses.append(f"Отсутствуют важные навыки: {missing} и другие")
            else:
                missing = ', '.join(missing_must)
                weaknesses.append(f"Отсутствуют ключевые навыки: {missing}")

        # 2. Недостаток опыта
        candidate_exp = self._safe_float(profile.get('years_of_experience', 0))
        required_exp = self._safe_float(vacancy.get('min_experience_years', 0))
        if required_exp > 0 and candidate_exp < required_exp:
            gap = required_exp - candidate_exp
            if gap >= 3:
                weaknesses.append(f"Опыт значительно ниже требуемого (не хватает {gap:.1f} лет)")
            else:
                weaknesses.append(f"Опыт ниже требуемого на {gap:.1f} лет")

        # 3. Недостающие дополнительные навыки
        missing_nice = match_result.get('nice_to_have_missing', [])
        if len(missing_nice) >= 3:
            missing = ', '.join(missing_nice[:2])
            weaknesses.append(f"Желательно развить навыки: {missing}")

        # 4. Мало навыков
        skills_count = len(profile.get('skills', []))
        if skills_count < 5:
            weaknesses.append("Ограниченный технический стек")

        # 5. Нет образования
        education = profile.get('education', [])
        if not education and required_exp >= 3:
            weaknesses.append("Информация об образовании не указана")

        return weaknesses[:4]  # Максимум 4 слабости

    def _analyze_risks(self, profile: Dict, vacancy: Dict, match_score: int) -> List[str]:
        """Выявление рисков (УЛУЧШЕНО)"""
        risks = []

        # 1. Очень низкий match score
        if match_score < 30:
            risks.append("Очень низкое соответствие требованиям вакансии")

        # 2. Мало информации
        skills = profile.get('skills', [])
        if len(skills) < 3:
            risks.append("Недостаточно информации о технических навыках в резюме")

        # 3. Нет опыта
        candidate_exp = self._safe_float(profile.get('years_of_experience', 0))
        required_exp = self._safe_float(vacancy.get('min_experience_years', 0))
        if candidate_exp == 0 and required_exp > 0:
            risks.append("Опыт работы не указан")

        # 4. Overqualified
        if candidate_exp > required_exp * 2.5 and required_exp > 0:
            risks.append("Возможно overqualified - кандидат может быть переквалифицирован для данной позиции")

        # 5. Отсутствие ключевых навыков
        must_coverage = profile.get('must_have_coverage', 0)
        if must_coverage < 30:
            risks.append("Критическое отсутствие обязательных навыков")

        # 6. Нет контактов
        contacts = profile.get('contact_info', {})
        if not contacts or not contacts.get('email'):
            risks.append("Контактная информация не указана или неполная")

        return risks[:4]  # Максимум 4 риска

    def _generate_questions(self, profile: Dict, vacancy: Dict, weaknesses: List[str], match_result: Dict) -> List[str]:
        """Генерация вопросов для интервью (ЗНАЧИТЕЛЬНО УЛУЧШЕНО)"""
        questions = []

        # 1. Вопросы по основным навыкам кандидата
        skills = profile.get('skills', [])
        must_matched = match_result.get('must_have_matched', [])

        # Вопросы по совпавшим ключевым навыкам
        for skill in must_matched[:2]:
            questions.append(f"Расскажите о вашем опыте работы с {skill}. Какие проекты реализовали с использованием этой технологии?")

        # 2. Вопросы по недостающим навыкам
        missing_must = match_result.get('must_have_missing', [])
        if missing_must:
            for skill in missing_must[:2]:
                questions.append(f"У нас в проекте используется {skill}. Насколько быстро вы готовы освоить эту технологию? Есть ли опыт изучения похожих инструментов?")

        # 3. Вопросы по опыту
        candidate_exp = self._safe_float(profile.get('years_of_experience', 0))
        if candidate_exp > 0:
            questions.append(f"За {int(candidate_exp)} лет работы, какой проект был для вас самым сложным и почему? Как вы справились?")
            questions.append("Расскажите о ситуации, когда вам приходилось работать в сжатые сроки. Как организовали работу?")
        else:
            questions.append("Расскажите о ваших учебных или pet-проектах. Какие технологии использовали?")

        # 4. Вопросы по типу позиции
        vacancy_title = vacancy.get('title', '')
        if any(word in vacancy_title.lower() for word in ['senior', 'lead', 'сеньор', 'лид']):
            questions.append("Опишите ситуацию, когда вам приходилось принимать архитектурные решения. Как выбирали подход?")
            questions.append("Расскажите о вашем опыте менторства или обучения junior-разработчиков.")

        # 5. Ситуационные вопросы (всегда актуальны)
        questions.extend([
            "Опишите случай, когда вы не согласились с техническим решением команды. Как разрешили ситуацию?",
            "Расскажите о баге в продакшене, который вы исправляли. Как действовали?",
        ])

        # 6. Вопросы по soft skills
        if profile.get('management_experience'):
            questions.append("Какой размер команды вы возглавляли? Как распределяли задачи и мотивировали участников?")

        # 7. Вопросы на мотивацию
        questions.append(f"Почему вас заинтересовала позиция {vacancy_title}? Что привлекает в нашей компании?")
        questions.append("Какие задачи и технологии вам наиболее интересны на новом месте работы?")

        # 8. Технические вопросы в зависимости от стека
        top_skill = self._get_skill_name(skills[0]) if skills else None
        if top_skill:
            skill_lower = top_skill.lower()

            tech_questions = {
                'python': "Объясните разницу между list comprehension и generator expression в Python. Когда что использовать?",
                'javascript': "Объясните разницу между var, let и const. Что такое hoisting?",
                'react': "Что такое Virtual DOM? Как React оптимизирует рендеринг?",
                'django': "Как работает ORM в Django? Объясните разницу между select_related и prefetch_related.",
                'docker': "Объясните разницу между Docker image и container. Что такое Docker volumes?",
                'kubernetes': "Что такое Pod в Kubernetes? Чем отличается Deployment от StatefulSet?",
                'postgresql': "Объясните разницу между INNER JOIN и LEFT JOIN. Что такое индексы и как они работают?",
            }

            for tech, question in tech_questions.items():
                if tech in skill_lower:
                    questions.append(question)
                    break

        # Уникализация и ограничение
        unique_questions = []
        seen = set()
        for q in questions:
            if q.lower() not in seen:
                seen.add(q.lower())
                unique_questions.append(q)

        return unique_questions[:12]  # Максимум 12 вопросов

    def _generate_recommendation(
        self,
        match_score: int,
        strengths: List[str],
        weaknesses: List[str],
        risks: List[str]
    ) -> str:
        """Генерация рекомендации (УЛУЧШЕНО)"""

        # Определяем статус
        if match_score >= 80:
            status = "Настоятельно рекомендуется"
            if len(weaknesses) == 0 and len(risks) == 0:
                detail = "Кандидат идеально соответствует требованиям вакансии. Рекомендуется приоритетное рассмотрение."
            elif len(weaknesses) <= 1:
                detail = "Кандидат отлично соответствует требованиям с минимальными пробелами."
            else:
                detail = "Кандидат очень хорошо соответствует требованиям, несмотря на небольшие недочеты."

        elif match_score >= 60:
            status = "Рекомендуется"
            if len(weaknesses) <= 2:
                detail = "Кандидат хорошо соответствует требованиям. Рекомендуется провести собеседование для детальной оценки."
            else:
                detail = "Кандидат соответствует большинству требований. Некоторые пробелы могут быть компенсированы обучением."

        elif match_score >= 40:
            status = "Условно рекомендуется"
            if len(risks) <= 1:
                detail = "Кандидат частично соответствует требованиям. Рекомендуется провести техническое собеседование для оценки потенциала."
            else:
                detail = "Есть потенциал, но требуется тщательная оценка навыков и готовности к обучению."

        else:
            status = "Не рекомендуется"
            if risks:
                detail = f"Существенное несоответствие требованиям. {risks[0]}"
            elif weaknesses:
                detail = f"Навыки кандидата недостаточно соответствуют требованиям вакансии. {weaknesses[0]}"
            else:
                detail = "Профиль кандидата не соответствует требованиям вакансии."

        return f"{status}. {detail}"

    async def generate_interview_questions(
        self,
        profile: Dict[str, Any],
        vacancy: Dict[str, Any],
        count: int = 10,
        focus_areas: List[str] = None
    ) -> List[str]:
        """
        Генерация вопросов для интервью
        """
        # Создаем временный match_result
        match_result = self.get_match_breakdown(profile, vacancy)

        questions = self._generate_questions(profile, vacancy, [], match_result)

        # Добавляем вопросы по focus areas
        if focus_areas:
            for area in focus_areas[:3]:
                questions.append(f"Расскажите подробнее о вашем опыте в области {area}. Какие задачи решали?")

        return questions[:count]

    # ============== ГЕНЕРАЦИЯ ТЕСТОВ ДЛЯ КАНДИДАТОВ ==============

    def generate_test_questions(
        self,
        vacancy_title: str,
        vacancy_description: str = "",
        required_skills: List[str] = None,
        department: str = "",
        difficulty_distribution: Dict[str, int] = None
    ) -> List[Dict]:
        """
        Генерация тестовых вопросов на основе вакансии

        Умный генератор создаёт вопросы по навыкам и требованиям вакансии.
        Работает быстро без внешних API.
        """
        import random
        import hashlib

        if difficulty_distribution is None:
            difficulty_distribution = {"easy": 5, "medium": 5, "hard": 5}

        if required_skills is None:
            required_skills = []

        # Извлекаем навыки из описания вакансии
        all_text = f"{vacancy_title} {vacancy_description}".lower()
        detected_skills = self._extract_skills_for_test(all_text, required_skills)

        questions = []

        # Генерируем вопросы по навыкам
        for difficulty, count in difficulty_distribution.items():
            skill_questions = self._generate_skill_questions(detected_skills, difficulty, count, vacancy_title)
            questions.extend(skill_questions)

        # Добавляем общие вопросы если не хватает
        total_needed = sum(difficulty_distribution.values())
        if len(questions) < total_needed:
            general_questions = self._generate_general_questions(
                vacancy_title,
                total_needed - len(questions)
            )
            questions.extend(general_questions)

        # Перемешиваем
        random.shuffle(questions)

        # Назначаем ID
        for i, q in enumerate(questions):
            q['id'] = i + 1

        return questions[:total_needed]

    def _extract_skills_for_test(self, text: str, required_skills: List[str]) -> List[str]:
        """Извлекает навыки для генерации теста"""
        skills = set()

        # Добавляем из required_skills
        for skill in required_skills:
            skills.add(skill.lower().strip())

        # Извлекаем из текста
        for skill_name, skill_data in self.TECH_SKILLS.items():
            if skill_name in text:
                skills.add(skill_name)
            for alias in skill_data.get('aliases', []):
                if alias in text:
                    skills.add(skill_name)

        return list(skills)[:10]  # Максимум 10 навыков

    def _generate_skill_questions(
        self,
        skills: List[str],
        difficulty: str,
        count: int,
        vacancy_title: str
    ) -> List[Dict]:
        """Генерирует вопросы по конкретным навыкам"""
        import random

        questions = []

        # База шаблонов вопросов по навыкам
        SKILL_QUESTIONS = {
            # Python
            'python': {
                'easy': [
                    ("Какой тип данных в Python используется для хранения упорядоченной коллекции?",
                     ["list", "dict", "set", "tuple"], 0),
                    ("Как называется функция для вывода текста в Python?",
                     ["print()", "echo()", "console.log()", "output()"], 0),
                    ("Какое ключевое слово используется для определения функции в Python?",
                     ["def", "function", "func", "define"], 0),
                ],
                'medium': [
                    ("Что такое list comprehension в Python?",
                     ["Сокращённый синтаксис создания списка", "Тип списка", "Метод сортировки", "Функция фильтрации"], 0),
                    ("Чем отличается tuple от list в Python?",
                     ["tuple неизменяемый", "list неизменяемый", "Ничем", "tuple быстрее"], 0),
                    ("Что такое декоратор в Python?",
                     ["Функция, модифицирующая другую функцию", "Тип переменной", "Класс", "Модуль"], 0),
                ],
                'hard': [
                    ("Что такое GIL в Python?",
                     ["Global Interpreter Lock", "Global Import Library", "General Input Loop", "Generic Interface Layer"], 0),
                    ("Как работает garbage collector в Python?",
                     ["Подсчёт ссылок + циклический сборщик", "Только подсчёт ссылок", "Mark and sweep", "Ручное управление"], 0),
                    ("Что такое metaclass в Python?",
                     ["Класс, создающий классы", "Абстрактный класс", "Интерфейс", "Миксин"], 0),
                ]
            },
            # JavaScript
            'javascript': {
                'easy': [
                    ("Какой метод добавляет элемент в конец массива?",
                     ["push()", "append()", "add()", "insert()"], 0),
                    ("Как объявить переменную в современном JavaScript?",
                     ["let / const", "var только", "define", "declare"], 0),
                    ("Что возвращает typeof null?",
                     ["object", "null", "undefined", "boolean"], 0),
                ],
                'medium': [
                    ("Что такое Promise в JavaScript?",
                     ["Объект для асинхронных операций", "Тип функции", "Массив", "Событие"], 0),
                    ("Чем отличается == от ===?",
                     ["=== сравнивает с типом", "== сравнивает с типом", "Ничем", "=== медленнее"], 0),
                    ("Что такое closure?",
                     ["Функция с доступом к внешней области", "Цикл", "Класс", "Модуль"], 0),
                ],
                'hard': [
                    ("Что такое Event Loop?",
                     ["Механизм обработки асинхронных задач", "Цикл событий DOM", "Тип цикла", "Событие мыши"], 0),
                    ("Что такое prototype chain?",
                     ["Цепочка наследования объектов", "Тип массива", "Метод строки", "Событие"], 0),
                    ("Как работает WeakMap?",
                     ["Map со слабыми ссылками на ключи", "Обычный Map", "Массив", "Set"], 0),
                ]
            },
            # React
            'react': {
                'easy': [
                    ("Что такое JSX?",
                     ["Расширение синтаксиса JavaScript", "Фреймворк", "База данных", "Язык программирования"], 0),
                    ("Как передать данные дочернему компоненту?",
                     ["Через props", "Через state", "Через context только", "Через DOM"], 0),
                    ("Что такое компонент в React?",
                     ["Функция или класс, возвращающий UI", "HTML тег", "CSS стиль", "База данных"], 0),
                ],
                'medium': [
                    ("Что такое useState?",
                     ["Хук для управления состоянием", "Метод жизненного цикла", "Событие", "Компонент"], 0),
                    ("Чем функциональные компоненты отличаются от классовых?",
                     ["Функциональные используют хуки", "Классовые быстрее", "Ничем", "Функциональные устарели"], 0),
                    ("Что такое Virtual DOM?",
                     ["Виртуальное представление DOM", "Реальный DOM", "CSS фреймворк", "База данных"], 0),
                ],
                'hard': [
                    ("Что такое React Fiber?",
                     ["Архитектура согласования React", "Библиотека", "Хук", "Компонент"], 0),
                    ("Как работает useEffect?",
                     ["Выполняет побочные эффекты после рендера", "Управляет состоянием", "Создаёт контекст", "Мемоизирует"], 0),
                    ("Что такое code splitting в React?",
                     ["Разделение кода на chunks", "Разделение компонентов", "Оптимизация CSS", "Минификация"], 0),
                ]
            },
            # SQL / Базы данных
            'sql': {
                'easy': [
                    ("Какая команда используется для выборки данных?",
                     ["SELECT", "GET", "FETCH", "READ"], 0),
                    ("Какая команда добавляет новую запись?",
                     ["INSERT", "ADD", "CREATE", "PUT"], 0),
                    ("Что такое первичный ключ?",
                     ["Уникальный идентификатор записи", "Внешний ключ", "Индекс", "Столбец"], 0),
                ],
                'medium': [
                    ("Что такое JOIN?",
                     ["Объединение данных из таблиц", "Создание таблицы", "Удаление данных", "Сортировка"], 0),
                    ("Чем отличается INNER JOIN от LEFT JOIN?",
                     ["LEFT возвращает все записи левой таблицы", "INNER возвращает все", "Ничем", "LEFT быстрее"], 0),
                    ("Что такое индекс в базе данных?",
                     ["Структура для ускорения поиска", "Тип таблицы", "Первичный ключ", "Ограничение"], 0),
                ],
                'hard': [
                    ("Что такое нормализация БД?",
                     ["Устранение избыточности данных", "Оптимизация запросов", "Создание индексов", "Резервное копирование"], 0),
                    ("Что такое транзакция?",
                     ["Атомарная единица работы с БД", "Запрос", "Таблица", "Индекс"], 0),
                    ("Что такое ACID?",
                     ["Свойства транзакций", "Тип БД", "Язык запросов", "Индекс"], 0),
                ]
            },
            # Git
            'git': {
                'easy': [
                    ("Какая команда клонирует репозиторий?",
                     ["git clone", "git copy", "git download", "git get"], 0),
                    ("Какая команда сохраняет изменения?",
                     ["git commit", "git save", "git push", "git store"], 0),
                    ("Что такое ветка (branch)?",
                     ["Параллельная версия кода", "Файл", "Коммит", "Репозиторий"], 0),
                ],
                'medium': [
                    ("Что такое merge?",
                     ["Слияние веток", "Удаление ветки", "Создание коммита", "Откат изменений"], 0),
                    ("Что делает git pull?",
                     ["Получает и сливает изменения", "Только получает", "Только отправляет", "Создаёт ветку"], 0),
                    ("Что такое git stash?",
                     ["Временное сохранение изменений", "Удаление файлов", "Создание ветки", "Коммит"], 0),
                ],
                'hard': [
                    ("Чем отличается rebase от merge?",
                     ["Rebase переписывает историю", "Merge переписывает", "Ничем", "Rebase быстрее"], 0),
                    ("Что такое cherry-pick?",
                     ["Применение отдельного коммита", "Удаление коммита", "Слияние веток", "Создание ветки"], 0),
                    ("Что такое git hooks?",
                     ["Скрипты, выполняемые при событиях", "Ветки", "Теги", "Коммиты"], 0),
                ]
            },
            # Docker
            'docker': {
                'easy': [
                    ("Что такое Docker контейнер?",
                     ["Изолированная среда выполнения", "Виртуальная машина", "Образ", "Том"], 0),
                    ("Что такое Docker образ?",
                     ["Шаблон для создания контейнера", "Контейнер", "Файл", "Сервис"], 0),
                    ("Какая команда запускает контейнер?",
                     ["docker run", "docker start", "docker exec", "docker create"], 0),
                ],
                'medium': [
                    ("Что такое Dockerfile?",
                     ["Инструкции для создания образа", "Контейнер", "Конфигурация", "Лог"], 0),
                    ("Что такое docker-compose?",
                     ["Инструмент для многоконтейнерных приложений", "Образ", "Контейнер", "Сеть"], 0),
                    ("Что такое volume в Docker?",
                     ["Постоянное хранилище данных", "Сеть", "Образ", "Контейнер"], 0),
                ],
                'hard': [
                    ("Что такое Docker Swarm?",
                     ["Инструмент оркестрации контейнеров", "Образ", "Сеть", "Том"], 0),
                    ("Чем отличается CMD от ENTRYPOINT?",
                     ["ENTRYPOINT не переопределяется аргументами", "CMD не переопределяется", "Ничем", "ENTRYPOINT устарел"], 0),
                    ("Что такое multi-stage build?",
                     ["Сборка образа в несколько этапов", "Параллельная сборка", "Сборка нескольких образов", "Кеширование"], 0),
                ]
            },
        }

        # Общие шаблоны для неизвестных навыков
        SKILL_TEMPLATES = {
            'easy': [
                ("Что такое {skill}?",
                 ["Технология/инструмент для {category}", "Язык программирования", "База данных", "Операционная система"], 0),
                ("Для чего используется {skill}?",
                 ["Для {purpose}", "Для игр", "Для музыки", "Для видео"], 0),
            ],
            'medium': [
                ("Какие преимущества даёт использование {skill}?",
                 ["Повышение эффективности и качества", "Снижение безопасности", "Усложнение кода", "Замедление работы"], 0),
                ("Как {skill} интегрируется в рабочий процесс?",
                 ["Как часть CI/CD или разработки", "Никак не интегрируется", "Только вручную", "Требует перезагрузки"], 0),
            ],
            'hard': [
                ("Какие best practices существуют для {skill}?",
                 ["Документирование, тестирование, оптимизация", "Игнорирование документации", "Избегание тестов", "Минимум комментариев"], 0),
                ("Как масштабировать решение на {skill}?",
                 ["Горизонтальное/вертикальное масштабирование", "Нельзя масштабировать", "Только вертикально", "Только удалением"], 0),
            ]
        }

        for skill in skills:
            skill_lower = skill.lower()

            # Ищем вопросы в базе
            skill_qs = None
            for key in SKILL_QUESTIONS:
                if key in skill_lower or skill_lower in key:
                    skill_qs = SKILL_QUESTIONS[key]
                    break

            if skill_qs and difficulty in skill_qs:
                available = skill_qs[difficulty]
                for q_text, options, correct in available:
                    if len(questions) >= count:
                        break
                    questions.append({
                        'question': q_text,
                        'options': options.copy(),
                        'correct_answer': correct,
                        'difficulty': difficulty
                    })

        return questions[:count]

    def _generate_general_questions(self, vacancy_title: str, count: int) -> List[Dict]:
        """Генерирует общие вопросы на логику и знания"""
        import random

        GENERAL_QUESTIONS = {
            'easy': [
                ("Сколько будет 15% от 200?", ["30", "20", "25", "35"], 0),
                ("Если A > B и B > C, то:", ["A > C", "C > A", "A = C", "Нельзя определить"], 0),
                ("Какое число следующее: 2, 4, 8, 16, ?", ["32", "24", "20", "28"], 0),
                ("Что такое deadline?", ["Крайний срок выполнения", "Начало проекта", "Перерыв", "Встреча"], 0),
                ("Что означает KPI?", ["Ключевые показатели эффективности", "Код проекта", "Тип документа", "Отдел"], 0),
            ],
            'medium': [
                ("Цена выросла на 20%, затем упала на 20%. Итоговая цена:", ["96% от начальной", "100%", "80%", "104%"], 0),
                ("Что такое приоритизация задач?", ["Определение порядка важности", "Удаление задач", "Делегирование", "Откладывание"], 0),
                ("Что важнее: скорость или качество?", ["Баланс зависит от контекста", "Всегда скорость", "Всегда качество", "Ни то, ни другое"], 0),
                ("Как справиться с конфликтом в команде?", ["Обсуждение и поиск компромисса", "Игнорирование", "Увольнение", "Эскалация"], 0),
            ],
            'hard': [
                ("Два поезда едут навстречу (60 и 80 км/ч). За сколько сблизятся на 280 км?", ["2 часа", "3 часа", "4 часа", "1.5 часа"], 0),
                ("Что такое agile методология?", ["Гибкий итеративный подход", "Жёсткое планирование", "Водопадная модель", "Отсутствие планирования"], 0),
                ("Как измерить эффективность процесса?", ["Метрики, KPI, анализ", "Субъективная оценка", "Не измерять", "Спросить начальника"], 0),
            ]
        }

        questions = []
        for difficulty, qs in GENERAL_QUESTIONS.items():
            available = qs.copy()
            random.shuffle(available)
            for q_text, options, correct in available[:2]:
                if len(questions) >= count:
                    break
                questions.append({
                    'question': q_text,
                    'options': options.copy(),
                    'correct_answer': correct,
                    'difficulty': difficulty
                })

        return questions[:count]
