"""
Employee AI Module - Conversational HR Assistant

Модуль обеспечивает:
- Естественный разговор с сотрудниками (RU/UZ)
- Объяснение KPI
- Генерацию рекомендаций
- Поиск по политикам
"""

import re
import logging
from typing import Optional, List, Dict, Any
from dataclasses import dataclass
from enum import Enum

logger = logging.getLogger(__name__)


# ============== Language Detection ==============

def detect_language(text: str) -> str:
    """Detect user language: 'ru' or 'uz'"""
    lower = text.lower()

    # Uzbek-specific Cyrillic: ў, қ, ғ, ҳ
    if any(c in lower for c in "ўқғҳ"):
        return "uz"

    # Common Uzbek Cyrillic words
    if re.search(
        r"\b(салом|ассалому|раҳмат|ёрдам|қандай|менинг|нима|керак|бўлим|таътил|мукофот)\b",
        lower,
    ):
        return "uz"

    # Uzbek Latin keywords
    if re.search(
        r"\b(salom|rahmat|yordam|qanday|kerak|nima|menin)\b",
        lower,
    ):
        return "uz"

    return "ru"


# ============== Intents ==============

class EmployeeIntent(str, Enum):
    GREETING = "greeting"
    SMALLTALK = "smalltalk"
    CAPABILITIES = "capabilities"
    KPI_SCORE = "kpi_score"
    KPI_EXPLAIN = "kpi_explain"
    KPI_IMPROVE = "kpi_improve"
    BONUS_INQUIRY = "bonus_inquiry"
    SALARY_QUESTION = "salary_question"
    LEAVE_BALANCE = "leave_balance"
    LEAVE_REQUEST = "leave_request"
    DISCIPLINE_QUESTION = "discipline_question"
    RECOGNITION_QUESTION = "recognition_question"
    TRAINING_QUESTION = "training_question"
    SCHEDULE_QUESTION = "schedule_question"
    BENEFITS_QUESTION = "benefits_question"
    POLICY_SEARCH = "policy_search"
    GENERAL = "general"


@dataclass
class IntentPattern:
    intent: EmployeeIntent
    patterns: List[str]
    priority: int = 0


INTENT_PATTERNS = [
    # === Greeting ===
    IntentPattern(
        intent=EmployeeIntent.GREETING,
        patterns=[
            r"^привет", r"^здравствуй", r"^добр\w*\s*(утр|день|вечер)",
            r"^салом", r"^ассалому", r"^хайр", r"^hello", r"^hi\b",
        ],
        priority=20,
    ),
    # === Smalltalk ===
    IntentPattern(
        intent=EmployeeIntent.SMALLTALK,
        patterns=[
            r"как\s+(ты|у тебя|дела|сам)", r"как\s+жизнь", r"как\s+настроен",
            r"что\s+нового", r"чё\s+как",
            r"қандайсиз", r"яхшимисиз", r"ишлар\s+қандай",
        ],
        priority=18,
    ),
    # === Capabilities ===
    IntentPattern(
        intent=EmployeeIntent.CAPABILITIES,
        patterns=[
            r"что.*(умеешь|можешь|знаешь)", r"^помощь$", r"^помоги$",
            r"чем.*помо[гж]", r"возможности", r"функци",
            r"нима\s+қила\s+олас", r"^ёрдам$", r"^yordam$", r"^help\b",
        ],
        priority=17,
    ),
    # === KPI improve (before explain & score) ===
    IntentPattern(
        intent=EmployeeIntent.KPI_IMPROVE,
        patterns=[
            r"как.*(увеличить|улучшить|повысить|поднять).*(kpi|кпи|показател|эффективн|качеств|результат)",
            r"(увеличить|улучшить|повысить|поднять).*(kpi|кпи|показател|качеств|результат)",
            r"что\s+делать.*(kpi|кпи|показател)", r"план.*улучшен",
            r"как.*(улучшить|повысить)\s+(качеств|выполнен|посещаем|продаж)",
            r"kpi.*яхшилаш", r"қандай.*яхшилаш", r"кўтариш.*(kpi|кпи)",
        ],
        priority=15,
    ),
    # === KPI explain ===
    IntentPattern(
        intent=EmployeeIntent.KPI_EXPLAIN,
        patterns=[
            r"почему.*(kpi|кпи|показател).*(низк|ниже|упал|снизил|плох|хуже)",
            r"почему.*(низк|ниже|упал|снизил).*(kpi|кпи|показател)",
            r"объясни.*(kpi|кпи)", r"разъясни.*(kpi|кпи)",
            r"из.за\s+чего.*(kpi|кпи)", r"причин.*(kpi|кпи|сниж|паден)",
            r"нега.*(kpi|кпи).*паст", r"тушунтир.*(kpi|кпи)",
        ],
        priority=13,
    ),
    # === KPI score (catch-all for KPI mentions) ===
    IntentPattern(
        intent=EmployeeIntent.KPI_SCORE,
        patterns=[
            r"(сколько|какой|каков).*(kpi|кпи)", r"мой\s+(kpi|кпи)",
            r"текущ.*(kpi|кпи)", r"\bkpi\b", r"\bкпи\b",
            r"показател.*эффективн", r"результат.*работ",
            r"менинг.*(kpi|натижа)", r"самарадорлик",
        ],
        priority=5,
    ),
    # === Leave ===
    IntentPattern(
        intent=EmployeeIntent.LEAVE_BALANCE,
        patterns=[
            r"сколько.*дней.*отпуск", r"остаток.*отпуск", r"дней\s+отпуска.*осталось",
            r"отпуск.*сколько.*остал", r"сколько.*остал.*отпуск",
            r"сколько.*отгул", r"неча\s+кун.*таътил", r"таътил.*қолди",
        ],
        priority=12,
    ),
    IntentPattern(
        intent=EmployeeIntent.LEAVE_REQUEST,
        patterns=[
            r"как.*оформить.*отпуск", r"хочу.*отпуск", r"заявление.*отпуск",
            r"отпуск", r"больничн", r"отсутств", r"выходн",
            r"таътил.*олмоқ", r"таътил.*расмий", r"таътил",
        ],
        priority=8,
    ),
    # === Finance ===
    IntentPattern(
        intent=EmployeeIntent.BONUS_INQUIRY,
        patterns=[
            r"бонус", r"премия", r"премии", r"когда.*выплат",
            r"размер.*преми", r"мукофот", r"bonus",
        ],
        priority=8,
    ),
    IntentPattern(
        intent=EmployeeIntent.SALARY_QUESTION,
        patterns=[
            r"зарплат", r"оклад", r"маош", r"ойлик", r"salary",
            r"когда.*получ", r"қачон.*олам",
        ],
        priority=7,
    ),
    # === Discipline ===
    IntentPattern(
        intent=EmployeeIntent.DISCIPLINE_QUESTION,
        patterns=[
            r"дисциплин", r"выговор", r"взыскан", r"штраф", r"нарушен",
            r"интизом", r"жарима", r"огоҳлантириш",
        ],
        priority=8,
    ),
    # === Recognition ===
    IntentPattern(
        intent=EmployeeIntent.RECOGNITION_QUESTION,
        patterns=[
            r"признан", r"награ", r"достижен", r"благодарн", r"поощрен",
            r"эътироф", r"ютуқ", r"миннатдорлик",
        ],
        priority=7,
    ),
    # === Training ===
    IntentPattern(
        intent=EmployeeIntent.TRAINING_QUESTION,
        patterns=[
            r"обучен", r"курс", r"тренинг", r"сертификат", r"экзамен",
            r"ўқиш", r"ўқув",
        ],
        priority=6,
    ),
    # === Schedule ===
    IntentPattern(
        intent=EmployeeIntent.SCHEDULE_QUESTION,
        patterns=[
            r"график.*работ", r"расписан", r"смен", r"рабоч.*врем",
            r"иш.*вақт", r"жадвал",
        ],
        priority=6,
    ),
    # === Benefits ===
    IntentPattern(
        intent=EmployeeIntent.BENEFITS_QUESTION,
        patterns=[
            r"льгот", r"соцпакет", r"медицин.*страхов", r"дмс", r"корпоратив",
            r"имтиёз", r"тиббий\s+суғурта",
        ],
        priority=6,
    ),
    # === Policy ===
    IntentPattern(
        intent=EmployeeIntent.POLICY_SEARCH,
        patterns=[
            r"политик", r"регламент", r"правил", r"порядок.*оформлен",
            r"процедур", r"документ.*нужн", r"сиёсат", r"қоида",
        ],
        priority=5,
    ),
]


class EmployeeIntentDetector:
    """Детектор интентов с приоритетами"""

    def detect(self, message: str) -> EmployeeIntent:
        message_lower = message.lower().strip()

        best_intent = EmployeeIntent.GENERAL
        best_priority = -1

        for pattern_config in INTENT_PATTERNS:
            for pattern in pattern_config.patterns:
                if re.search(pattern, message_lower):
                    if pattern_config.priority > best_priority:
                        best_intent = pattern_config.intent
                        best_priority = pattern_config.priority
                    break

        logger.debug(f"Detected intent: {best_intent.value} for: {message[:50]}...")
        return best_intent


# ============== KPI Explainer (used by /ai/explain endpoint) ==============

class EmployeeKpiExplainer:
    """Объяснение KPI показателей"""

    LOW_METRIC_EXPLANATIONS = {
        "sales": [
            "Показатель продаж ниже плана. Возможные причины: сезонность, изменение рынка, недостаток клиентской базы.",
            "Рекомендуется: усилить работу с существующими клиентами, изучить успешные практики коллег.",
        ],
        "customer_satisfaction": [
            "Удовлетворённость клиентов снизилась. Это может быть связано с качеством обслуживания или скоростью обработки запросов.",
            "Рекомендуется: проанализировать обратную связь клиентов, пройти тренинг по клиентскому сервису.",
        ],
        "task_completion": [
            "Процент выполнения задач ниже нормы. Возможно, много задач с высоким приоритетом или недостаточно времени.",
            "Рекомендуется: расставить приоритеты, обсудить загрузку с руководителем.",
        ],
        "quality": [
            "Качество работы требует улучшения. Обратите внимание на детали и соблюдение стандартов.",
            "Рекомендуется: запросить обратную связь от руководителя, изучить best practices.",
        ],
        "attendance": [
            "Показатель посещаемости снижен. Это влияет на общую оценку эффективности.",
            "Рекомендуется: планировать отпуска заранее, минимизировать незапланированные отсутствия.",
        ],
        "training": [
            "Не пройдены обязательные курсы обучения. Это влияет на KPI и может быть требованием регулятора.",
            "Рекомендуется: запланировать и пройти обязательные курсы в ближайшее время.",
        ],
    }

    def explain_kpi(self, kpi_data: Dict[str, Any]) -> Dict[str, Any]:
        total_score = kpi_data.get("total_score", 0)
        if isinstance(total_score, str):
            total_score = float(total_score)
        metrics = kpi_data.get("metrics", {})
        low_metrics = kpi_data.get("low_metrics", {})

        if total_score >= 90:
            explanation = (
                "Ваши показатели KPI отличные! Вы превышаете план по большинству метрик. "
                "Продолжайте в том же духе и делитесь опытом с коллегами."
            )
        elif total_score >= 70:
            explanation = (
                "Ваши показатели KPI хорошие, но есть возможности для улучшения. "
                "Обратите внимание на метрики ниже плана."
            )
        elif total_score >= 50:
            explanation = (
                "Ваши показатели KPI требуют внимания. "
                "Рекомендуется обсудить с руководителем план улучшения."
            )
        else:
            explanation = (
                "Ваши показатели KPI критически низкие и требуют срочных мер. "
                "Необходимо составить план действий совместно с руководителем."
            )

        metric_explanations = {}
        for metric_key, metric_data in low_metrics.items():
            completion = metric_data.get("completion", 0)
            if isinstance(completion, str):
                completion = float(completion)
            if completion < 70:
                explanations = self.LOW_METRIC_EXPLANATIONS.get(metric_key, [
                    f"Показатель '{metric_data.get('name', metric_key)}' ниже плана ({completion:.1f}%).",
                ])
                metric_explanations[metric_data.get("name", metric_key)] = " ".join(explanations)

        suggestions = self._generate_improvement_suggestions(low_metrics, total_score)

        return {
            "explanation": explanation,
            "metric_explanations": metric_explanations,
            "improvement_suggestions": suggestions,
            "risk_assessment": self._assess_risk(total_score, low_metrics),
        }

    def _generate_improvement_suggestions(self, low_metrics: Dict, total_score: float) -> List[str]:
        suggestions = []
        if total_score < 50:
            suggestions.append("Срочно назначьте встречу с руководителем для обсуждения плана улучшения")
        for metric_key, metric_data in low_metrics.items():
            completion = metric_data.get("completion", 0)
            if isinstance(completion, str):
                completion = float(completion)
            if completion < 50:
                suggestions.append(f"Приоритетно улучшить показатель '{metric_data.get('name', metric_key)}'")
            elif completion < 70:
                suggestions.append(f"Обратить внимание на показатель '{metric_data.get('name', metric_key)}'")
        if not suggestions:
            suggestions.append("Поддерживайте текущий уровень показателей")
        return suggestions[:5]

    def _assess_risk(self, total_score: float, low_metrics: Dict) -> Dict[str, Any]:
        critical = sum(
            1 for m in low_metrics.values()
            if (float(m.get("completion", 0)) if isinstance(m.get("completion", 0), str) else m.get("completion", 0)) < 30
        )
        if total_score < 50 or critical >= 2:
            return {"level": "high", "message": "Высокий риск: требуются срочные меры", "critical_metrics_count": critical}
        elif total_score < 70 or critical >= 1:
            return {"level": "medium", "message": "Средний риск: рекомендуется план улучшения", "critical_metrics_count": critical}
        return {"level": "low", "message": "Низкий риск: показатели в пределах нормы", "critical_metrics_count": critical}


# ============== Recommendation Engine (used by /ai/analyze endpoint) ==============

class EmployeeRecommendationEngine:
    RECOMMENDATION_BASE = {
        "sales": [
            {"type": "quick", "action": "Проведите follow-up звонки клиентам, которые проявляли интерес", "effect": "Быстрое увеличение конверсии", "impact": 3.0},
            {"type": "medium", "action": "Пройдите тренинг по продажам и техникам закрытия сделок", "effect": "Улучшение навыков продаж", "impact": 5.0},
            {"type": "long", "action": "Развивайте личный бренд и сеть контактов в отрасли", "effect": "Долгосрочный рост клиентской базы", "impact": 10.0},
        ],
        "customer_satisfaction": [
            {"type": "quick", "action": "Увеличьте скорость ответа на запросы клиентов до 2 часов", "effect": "Повышение удовлетворённости", "impact": 2.5},
            {"type": "medium", "action": "Внедрите практику регулярной обратной связи с клиентами", "effect": "Улучшение качества сервиса", "impact": 4.0},
        ],
        "task_completion": [
            {"type": "quick", "action": "Используйте технику Pomodoro для повышения продуктивности", "effect": "Увеличение выполненных задач", "impact": 3.0},
            {"type": "medium", "action": "Освойте методологию GTD (Getting Things Done)", "effect": "Системный подход к задачам", "impact": 5.0},
        ],
        "quality": [
            {"type": "quick", "action": "Делайте самопроверку перед сдачей работы", "effect": "Снижение ошибок", "impact": 3.0},
            {"type": "medium", "action": "Запросите обратную связь от руководителя и составьте чек-лист", "effect": "Системное улучшение качества", "impact": 4.0},
        ],
        "attendance": [
            {"type": "quick", "action": "Планируйте отсутствия заранее и минимизируйте незапланированные пропуски", "effect": "Стабильная посещаемость", "impact": 2.0},
        ],
        "training": [
            {"type": "quick", "action": "Запланируйте 2 часа в неделю на обучение", "effect": "Выполнение плана обучения", "impact": 4.0},
        ],
    }

    def generate_recommendations(self, kpi_data: Dict[str, Any], employee_data: Dict[str, Any]) -> Dict[str, Any]:
        low_metrics = kpi_data.get("low_metrics", kpi_data.get("metrics", {}))
        total_score = kpi_data.get("total_score", 0)
        if isinstance(total_score, str):
            total_score = float(total_score)

        recommendations = []
        priority = 1

        sorted_metrics = sorted(
            low_metrics.items(),
            key=lambda x: float(x[1].get("completion", 100)) if isinstance(x[1].get("completion", 100), (int, float, str)) else 100
        )

        for metric_key, metric_data in sorted_metrics:
            completion = metric_data.get("completion", 100)
            if isinstance(completion, str):
                completion = float(completion)
            if completion >= 90:
                continue
            for rec in self.RECOMMENDATION_BASE.get(metric_key, []):
                recommendations.append({
                    **rec,
                    "priority": priority,
                    "metric": metric_data.get("name", metric_key),
                    "expected_impact": rec.get("impact", 0),
                })
                priority += 1
                if len(recommendations) >= 7:
                    break
            if len(recommendations) >= 7:
                break

        if len(recommendations) < 3:
            recommendations.extend([
                {"type": "quick", "action": "Обсудите ваши показатели с руководителем", "effect": "Получение обратной связи и поддержки", "priority": priority, "expected_impact": 2.0},
                {"type": "medium", "action": "Изучите практики топ-перформеров вашего отдела", "effect": "Перенятие лучших практик", "priority": priority + 1, "expected_impact": 3.0},
            ])

        total_impact = sum(r.get("expected_impact", 0) for r in recommendations[:5])

        return {
            "recommendations": recommendations[:7],
            "priority_actions": [r for r in recommendations if r.get("type") == "quick"][:3],
            "expected_improvement": {
                "potential_score_increase": min(total_impact, 100 - total_score),
                "message": f"При выполнении рекомендаций возможен рост KPI на {min(total_impact, 20):.1f}%",
            },
        }


# ============== Conversational Chat Handler ==============

IMPROVEMENT_TIPS = {
    "sales": {
        "ru": "усильте follow-up с клиентами и расширьте воронку",
        "uz": "мижозлар билан алоқани кучайтиринг ва воронкани кенгайтиринг",
    },
    "customer_satisfaction": {
        "ru": "ускорьте ответы на запросы и собирайте обратную связь",
        "uz": "сўровларга тезроқ жавоб беринг ва фикр-мулоҳаза йиғинг",
    },
    "task_completion": {
        "ru": "расставьте приоритеты и закрывайте задачи последовательно",
        "uz": "вазифаларни муҳимлик бўйича тартиблаб, кетма-кет бажаринг",
    },
    "quality": {
        "ru": "проверяйте работу перед сдачей и запросите feedback у руководителя",
        "uz": "ишни топширишдан олдин текширинг ва раҳбардан фикр олинг",
    },
    "attendance": {
        "ru": "минимизируйте незапланированные пропуски",
        "uz": "режалаштирилмаган қатнашмасликни камайтиринг",
    },
    "training": {
        "ru": "запланируйте 2 часа в неделю на обязательные курсы",
        "uz": "ҳафтада 2 соат мажбурий курсларга ажратинг",
    },
}
DEFAULT_TIP = {
    "ru": "обсудите с руководителем план действий",
    "uz": "раҳбар билан ҳаракат режасини муҳокама қилинг",
}


class EmployeeChatHandler:
    """Conversational HR assistant — краткие, вежливые, на языке пользователя"""

    def __init__(self):
        self.intent_detector = EmployeeIntentDetector()
        self.kpi_explainer = EmployeeKpiExplainer()
        self.recommendation_engine = EmployeeRecommendationEngine()

    def handle_chat(
        self,
        message: str,
        context: Dict[str, Any],
        history: List[Dict[str, str]],
        facts: Dict[str, Any],
        policies: List[Dict[str, Any]],
    ) -> Dict[str, Any]:
        lang = detect_language(message)
        intent = self.intent_detector.detect(message)

        # Follow-up detection: short message after existing conversation
        if intent == EmployeeIntent.GENERAL and self._is_followup(message) and history:
            prev = self._infer_topic_from_history(history, facts)
            if prev:
                intent = prev

        response = self._route(message, intent, context, facts, policies, history, lang)

        return {
            "response": response,
            "intent": intent.value,
            "confidence": 0.85,
            "sources": self._extract_sources(policies),
        }

    # ===================== Routing =====================

    def _route(self, message, intent, context, facts, policies, history, lang):
        handlers = {
            EmployeeIntent.GREETING: lambda: self._greeting(context, lang),
            EmployeeIntent.SMALLTALK: lambda: self._smalltalk(lang),
            EmployeeIntent.CAPABILITIES: lambda: self._capabilities(lang),
            EmployeeIntent.KPI_SCORE: lambda: self._kpi_score(facts, lang),
            EmployeeIntent.KPI_EXPLAIN: lambda: self._kpi_explain(facts, lang),
            EmployeeIntent.KPI_IMPROVE: lambda: self._kpi_improve(facts, lang),
            EmployeeIntent.BONUS_INQUIRY: lambda: self._bonus(facts, lang),
            EmployeeIntent.SALARY_QUESTION: lambda: self._salary(lang),
            EmployeeIntent.LEAVE_BALANCE: lambda: self._leave_balance(lang),
            EmployeeIntent.LEAVE_REQUEST: lambda: self._leave_request(policies, lang),
            EmployeeIntent.DISCIPLINE_QUESTION: lambda: self._discipline(facts, lang),
            EmployeeIntent.RECOGNITION_QUESTION: lambda: self._recognition(facts, lang),
            EmployeeIntent.TRAINING_QUESTION: lambda: self._training(lang),
            EmployeeIntent.SCHEDULE_QUESTION: lambda: self._schedule(lang),
            EmployeeIntent.BENEFITS_QUESTION: lambda: self._benefits(lang),
            EmployeeIntent.POLICY_SEARCH: lambda: self._policy_search(message, policies, lang),
        }
        handler = handlers.get(intent)
        if handler:
            return handler()
        return self._general(message, policies, lang)

    # ===================== Follow-up =====================

    def _is_followup(self, message: str) -> bool:
        msg = message.lower().strip()
        patterns = [
            r"^да$", r"^ага$", r"^ок$", r"^хорошо$", r"^давай$", r"^ладно$",
            r"^конечно$", r"^расскажи$", r"подробн", r"расскажи.*ещё", r"продолж",
            r"а\s+(как|что|почему)", r"что\s+ещё",
            r"^ҳа$", r"^хўп$", r"^майли$", r"^яна$", r"батафсил",
        ]
        return any(re.search(p, msg) for p in patterns)

    def _infer_topic_from_history(self, history, facts):
        for msg in reversed(history):
            if msg.get("role") != "assistant":
                continue
            text = msg.get("content", "").lower()
            if any(w in text for w in ["kpi", "кпи", "показател"]):
                return EmployeeIntent.KPI_IMPROVE if facts.get("current_kpi") else EmployeeIntent.KPI_SCORE
            if any(w in text for w in ["бонус", "премия", "мукофот"]):
                return EmployeeIntent.BONUS_INQUIRY
            if any(w in text for w in ["отпуск", "таътил"]):
                return EmployeeIntent.LEAVE_REQUEST
            if any(w in text for w in ["обучен", "курс", "ўқув"]):
                return EmployeeIntent.TRAINING_QUESTION
            break
        return None

    # ===================== Handlers =====================

    def _greeting(self, context, lang):
        if lang == "uz":
            return "Ассалому алайкум! Мен ходимлар порталининг AI-ёрдамчисиман. Қандай ёрдам бера оламан?"
        return "Здравствуйте! Я AI-помощник портала сотрудников. Чем могу помочь?"

    def _smalltalk(self, lang):
        if lang == "uz":
            return "Раҳмат, яхши! Иш масалалари бўйича ёрдам бера оламан — KPI, бонуслар, таътил, сиёсатлар. Нимани билмоқчисиз?"
        return "Всё хорошо, спасибо! Могу помочь по рабочим вопросам — KPI, бонусы, отпуск, внутренние правила. Что вас интересует?"

    def _capabilities(self, lang):
        if lang == "uz":
            return (
                "Мен қуйидагилар бўйича ёрдам бера оламан:\n\n"
                "• KPI кўрсаткичлари ва тавсиялар\n"
                "• Бонуслар ва маош\n"
                "• Таътил ва бўшлиқлар\n"
                "• Ўқув курслар\n"
                "• Ички сиёсат ва регламентлар\n\n"
                "Саволингизни ёзинг!"
            )
        return (
            "Я могу помочь с:\n\n"
            "• KPI — текущие показатели, объяснение, план улучшения\n"
            "• Бонусы и зарплата\n"
            "• Отпуск и отсутствия\n"
            "• Обучение и курсы\n"
            "• Внутренние политики и регламенты\n\n"
            "Задайте вопрос!"
        )

    # ---------- KPI ----------

    def _kpi_score(self, facts, lang):
        kpi = facts.get("current_kpi", {})
        if not kpi:
            if lang == "uz":
                return "Ҳозирча KPI маълумотлари мавжуд эмас. Порталдаги «Менинг KPI» бўлимини текширинг ёки HR га мурожаат қилинг."
            return "Данных о вашем KPI пока нет. Проверьте раздел «Мои KPI» на портале или обратитесь в HR."

        score = float(kpi.get("total_score", 0))
        period = kpi.get("period", "текущий период" if lang == "ru" else "жорий давр")

        if score >= 90:
            label = ("отличный результат", "аъло натижа")
        elif score >= 70:
            label = ("хороший результат", "яхши натижа")
        elif score >= 50:
            label = ("есть над чем поработать", "яхшилаш мумкин")
        else:
            label = ("требует внимания", "диққат керак")

        idx = 0 if lang == "ru" else 1
        if lang == "uz":
            response = f"Сизнинг KPI ({period}): {score:.1f}% — {label[idx]}."
        else:
            response = f"Ваш KPI за {period}: {score:.1f}% — {label[idx]}."

        # One key insight — lowest metric
        metrics = kpi.get("metrics", {})
        low = [(k, v) for k, v in metrics.items()
               if float(v.get("completion", 100)) < 70]
        if low:
            worst = min(low, key=lambda x: float(x[1].get("completion", 0)))
            name = worst[1].get("name", worst[0])
            comp = float(worst[1].get("completion", 0))
            if lang == "uz":
                response += f"\n\nЭнг паст кўрсаткич: {name} ({comp:.0f}%). Яхшилаш бўйича маслаҳат берайми?"
            else:
                response += f"\n\nСамый низкий показатель: {name} ({comp:.0f}%). Хотите советы по улучшению?"
        elif kpi.get("bonus_eligible"):
            if lang == "uz":
                response += "\n\nСиз бонусга ҳақлисиз! 🎉"
            else:
                response += "\n\nВы имеете право на бонус! 🎉"

        return response

    def _kpi_explain(self, facts, lang):
        kpi = facts.get("current_kpi", {})
        if not kpi:
            if lang == "uz":
                return "KPI маълумотлари йўқ. «Менинг KPI» бўлимини текширинг."
            return "Нет данных о KPI. Проверьте раздел «Мои KPI» на портале."

        score = float(kpi.get("total_score", 0))
        metrics = kpi.get("metrics", {})
        low = {k: v for k, v in metrics.items()
               if float(v.get("completion", 100)) < 70}

        if not low:
            if lang == "uz":
                return f"Сизнинг KPI {score:.1f}% — барча кўрсаткичлар нормада. Давом этинг! 💪"
            return f"Ваш KPI {score:.1f}% — все показатели в норме. Продолжайте в том же духе! 💪"

        sorted_low = sorted(low.items(), key=lambda x: float(x[1].get("completion", 0)))[:3]

        if lang == "uz":
            response = f"KPI {score:.1f}%. Қуйидаги кўрсаткичлар пастга тортмоқда:\n\n"
            for key, m in sorted_low:
                response += f"• {m.get('name', key)}: {float(m.get('completion', 0)):.0f}%\n"
            response += "\nЯхшилаш режасини тузиб берайми?"
        else:
            response = f"KPI {score:.1f}%. Вот что тянет вниз:\n\n"
            for key, m in sorted_low:
                response += f"• {m.get('name', key)}: {float(m.get('completion', 0)):.0f}%\n"
            response += "\nХотите, подготовлю план улучшения?"

        return response

    def _kpi_improve(self, facts, lang):
        kpi = facts.get("current_kpi", {})
        if not kpi:
            if lang == "uz":
                return "KPI маълумотлари бўлмаса, аниқ тавсия бериш қийин. Аввал «Менинг KPI» бўлимини текширинг."
            return "Без данных о KPI сложно дать конкретные советы. Сначала проверьте раздел «Мои KPI»."

        metrics = kpi.get("metrics", {})
        low = {k: v for k, v in metrics.items()
               if float(v.get("completion", 100)) < 90}

        if not low:
            if lang == "uz":
                return "Барча кўрсаткичларингиз юқори! Натижани сақлаб қолинг ва ҳамкасбларга тажриба улашинг."
            return "Все показатели на высоком уровне! Поддерживайте результат и делитесь опытом с коллегами."

        sorted_low = sorted(low.items(), key=lambda x: float(x[1].get("completion", 0)))[:3]

        if lang == "uz":
            response = "KPI ни яхшилаш учун қуйидагиларга эътибор беринг:\n\n"
            for i, (key, m) in enumerate(sorted_low, 1):
                tip = IMPROVEMENT_TIPS.get(key, DEFAULT_TIP).get(lang, DEFAULT_TIP["uz"])
                response += f"{i}. **{m.get('name', key)}** ({float(m.get('completion', 0)):.0f}%) — {tip}\n"
            response += "\nБатафсил режа керакми?"
        else:
            response = "Чтобы поднять KPI, сфокусируйтесь на:\n\n"
            for i, (key, m) in enumerate(sorted_low, 1):
                tip = IMPROVEMENT_TIPS.get(key, DEFAULT_TIP).get(lang, DEFAULT_TIP["ru"])
                response += f"{i}. **{m.get('name', key)}** ({float(m.get('completion', 0)):.0f}%) — {tip}\n"
            response += "\nНужен подробный план?"

        return response

    # ---------- Finance ----------

    def _bonus(self, facts, lang):
        kpi = facts.get("current_kpi", {})
        if not kpi:
            if lang == "uz":
                return "Бонус маълумотлари учун KPI кўрсаткичларингиз керак. «Менинг KPI» бўлимини текширинг ёки HR га мурожаат қилинг."
            return "Для информации о бонусе нужны данные KPI. Проверьте раздел «Мои KPI» или обратитесь в HR."

        score = float(kpi.get("total_score", 0))
        eligible = kpi.get("bonus_eligible", False)

        if eligible:
            if lang == "uz":
                return f"KPI {score:.1f}% — сиз бонусга ҳақлисиз! Бонуслар одатда давр ёпилгандан кейинги ойда ҳисобланади."
            return f"При KPI {score:.1f}% вы имеете право на бонус! Бонусы обычно начисляются в следующем месяце после закрытия периода."
        else:
            if lang == "uz":
                return f"KPI {score:.1f}% — ҳозирча бонус ҳисобланмайди. Минимал чегара — 50%. KPI ни яхшилаш бўйича маслаҳат берайми?"
            return f"При KPI {score:.1f}% бонус пока не начисляется. Минимальный порог — 50%. Хотите советы по улучшению?"

    def _salary(self, lang):
        if lang == "uz":
            return (
                "Маош тўловлари: аванс ҳар ойнинг 15-санасида, асосий маош ой охирида.\n"
                "Аниқ миқдор учун HR бўлимига мурожаат қилинг ёки порталдаги «Менинг маълумотларим» бўлимини текширинг."
            )
        return (
            "Зарплата выплачивается: аванс — 15 числа, основная часть — в конце месяца.\n"
            "Для уточнения суммы обратитесь в HR или проверьте раздел «Мои данные» на портале."
        )

    # ---------- Leave ----------

    def _leave_balance(self, lang):
        if lang == "uz":
            return (
                "Таътил қолдиғини порталдаги «Менинг маълумотларим» бўлимидан кўришингиз мумкин.\n"
                "Стандарт: йилига 28 календар кун. Аниқ маълумот учун HR га мурожаат қилинг."
            )
        return (
            "Остаток отпуска можно посмотреть в разделе «Мои данные» на портале.\n"
            "Стандарт: 28 календарных дней в год. Для уточнения обратитесь в HR."
        )

    def _leave_request(self, policies, lang):
        if lang == "uz":
            response = (
                "Таътилни расмийлаштириш:\n\n"
                "1. Раҳбар билан саналарни келишинг\n"
                "2. Камида 2 ҳафта олдин ариза беринг\n"
                "3. HR тасдиғини кутинг\n"
            )
        else:
            response = (
                "Порядок оформления отпуска:\n\n"
                "1. Согласуйте даты с руководителем\n"
                "2. Подайте заявление минимум за 2 недели\n"
                "3. Дождитесь утверждения HR\n"
            )

        leave_policies = [p for p in policies
                          if any(w in p.get("title", "").lower() for w in ["отпуск", "таътил", "leave"])]
        if leave_policies:
            if lang == "uz":
                response += "\nТегишли ҳужжатлар:\n"
            else:
                response += "\nСвязанные документы:\n"
            for p in leave_policies[:2]:
                response += f"• {p.get('title')} ({p.get('code', '')})\n"

        return response

    # ---------- Discipline ----------

    def _discipline(self, facts, lang):
        discipline = facts.get("discipline", {})
        active_count = discipline.get("active_count", 0)

        if active_count > 0:
            if lang == "uz":
                response = f"Сизда {active_count} та фаол интизомий чора мавжуд:\n\n"
                for a in discipline.get("actions", []):
                    response += f"• {a.get('type', '—')} — {a.get('date', '—')} ({a.get('status', '—')})\n"
                response += "\nШикоят қилиш муддати — 10 иш куни. Тафсилотлар учун HR га мурожаат қилинг."
            else:
                response = f"У вас {active_count} активных дисциплинарных мер:\n\n"
                for a in discipline.get("actions", []):
                    response += f"• {a.get('type', '—')} — {a.get('date', '—')} ({a.get('status', '—')})\n"
                response += "\nСрок обжалования — 10 рабочих дней. Подробности в HR."
            return response

        if discipline:
            if lang == "uz":
                return "Сизда фаол интизомий чоралар йўқ. ✅"
            return "Активных дисциплинарных мер нет. ✅"

        if lang == "uz":
            return "Интизомий чоралар тўғрисида маълумот олиш учун HR бўлимига мурожаат қилинг ёки порталдаги «Интизом» бўлимини текширинг."
        return "Информацию о дисциплинарных мерах можно посмотреть в разделе «Дисциплина» на портале или уточнить в HR."

    # ---------- Recognition ----------

    def _recognition(self, facts, lang):
        recognition = facts.get("recognition", {})
        total_points = recognition.get("total_points", 0)

        if lang == "uz":
            response = "🏆 Эътироф тизими: ой/чорак/йил ходими номинациялари мавжуд.\n"
            if total_points > 0:
                response += f"Сизнинг баллингиз: {total_points}.\n"
            response += "Номзод кўрсатиш: Портал → «Эътироф» → «Номзод кўрсатиш»."
        else:
            response = "🏆 Система признания: номинации «Сотрудник месяца/квартала/года».\n"
            if total_points > 0:
                response += f"Ваши баллы: {total_points}.\n"
            response += "Номинировать коллегу: Портал → «Признание» → «Номинировать»."

        return response

    # ---------- Training ----------

    def _training(self, lang):
        if lang == "uz":
            return (
                "Мажбурий курслар: AML/KYC, маълумотлар хавфсизлиги, банк этикаси.\n"
                "Курслар ўз вақтида ўтилиши KPI га таъсир қилади.\n"
                "Рўйхат ва жадвал — ўқув порталида. Саволлар бўлса, HR га мурожаат қилинг."
            )
        return (
            "Обязательные курсы: AML/KYC, информационная безопасность, банковская этика.\n"
            "Своевременное прохождение влияет на KPI.\n"
            "Список и расписание — на учебном портале. Вопросы — в HR."
        )

    # ---------- Schedule ----------

    def _schedule(self, lang):
        if lang == "uz":
            return (
                "Стандарт иш графиги: Душанба–Жума, 09:00–18:00, тушлик 13:00–14:00.\n"
                "Масофавий иш — раҳбар рухсати билан, ҳафтада 2 кунгача.\n"
                "Ўзгартиришлар учун HR га ариза юборинг."
            )
        return (
            "Стандартный график: Пн–Пт, 09:00–18:00, обед 13:00–14:00.\n"
            "Удалённая работа — с разрешения руководителя, до 2 дней в неделю.\n"
            "Для изменений подайте заявку в HR."
        )

    # ---------- Benefits ----------

    def _benefits(self, lang):
        if lang == "uz":
            return (
                "Ходимларга: ДМС полиси, имтиёзли кредитлар, депозитларга юқори фоиз, "
                "бепул ўқув курслар, корпоратив спорт залига чегирма.\n"
                "Тўлиқ рўйхат учун HR бўлимига мурожаат қилинг."
            )
        return (
            "Сотрудникам доступны: ДМС, льготные кредиты, повышенные ставки по депозитам, "
            "бесплатное обучение, скидки на корпоративный спортзал.\n"
            "Полный перечень — в HR."
        )

    # ---------- Policy ----------

    def _policy_search(self, message, policies, lang):
        if not policies:
            if lang == "uz":
                return "Сўровингиз бўйича ҳужжат топилмади. Бошқа калит сўзларни ишлатиб кўринг ёки HR бўлимига мурожаат қилинг."
            return "По вашему запросу документов не найдено. Попробуйте другие ключевые слова или обратитесь в HR."

        if lang == "uz":
            response = f"Топилган ҳужжатлар ({len(policies)}):\n\n"
            for p in policies[:3]:
                response += f"• **{p.get('title')}** ({p.get('code', '')})\n"
                summary = p.get("summary", "")
                if summary:
                    response += f"  {summary[:80]}\n"
            response += "\nБатафсилроқ маълумот керакми?"
        else:
            response = f"Найдено документов: {len(policies)}\n\n"
            for p in policies[:3]:
                response += f"• **{p.get('title')}** ({p.get('code', '')})\n"
                summary = p.get("summary", "")
                if summary:
                    response += f"  {summary[:80]}\n"
            response += "\nНужна более подробная информация?"

        return response

    # ---------- General / Out of scope ----------

    def _general(self, message, policies, lang):
        if policies:
            return self._policy_search(message, policies, lang)

        if lang == "uz":
            return "Мен HR масалалари бўйича ёрдам бераман: KPI, бонуслар, таътил, ўқув, сиёсатлар. Аниқроқ савол берсангиз, ёрдам берарман!"
        return "Я помогаю по рабочим вопросам: KPI, бонусы, отпуск, обучение, внутренние правила. Задайте конкретный вопрос, и я постараюсь помочь!"

    # ===================== Utils =====================

    def _extract_sources(self, policies: List[Dict]) -> List[Dict]:
        return [
            {"title": p.get("title"), "code": p.get("code")}
            for p in policies[:3]
        ]


# ============== Module-level instances ==============
intent_detector = EmployeeIntentDetector()
kpi_explainer = EmployeeKpiExplainer()
recommendation_engine = EmployeeRecommendationEngine()
chat_handler = EmployeeChatHandler()
