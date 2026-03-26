"""
Employee AI Module - Обработка запросов сотрудников

Модуль обеспечивает:
- Обработку чата с сотрудниками
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


class EmployeeIntent(str, Enum):
    """Интенты сообщений сотрудников"""
    LEAVE_REQUEST = "leave_request"
    LEAVE_BALANCE = "leave_balance"
    KPI_QUESTION = "kpi_question"
    KPI_EXPLAIN = "kpi_explain"
    BONUS_INQUIRY = "bonus_inquiry"
    SALARY_QUESTION = "salary_question"
    POLICY_SEARCH = "policy_search"
    DISCIPLINE_QUESTION = "discipline_question"
    RECOGNITION_QUESTION = "recognition_question"
    TRAINING_QUESTION = "training_question"
    SCHEDULE_QUESTION = "schedule_question"
    BENEFITS_QUESTION = "benefits_question"
    GREETING = "greeting"
    HELP = "help"
    GENERAL = "general"


@dataclass
class IntentPattern:
    intent: EmployeeIntent
    patterns: List[str]
    priority: int = 0


# Паттерны для определения интентов (русский + узбекский языки)
INTENT_PATTERNS = [
    # === Приветствия ===
    IntentPattern(
        intent=EmployeeIntent.GREETING,
        patterns=[
            r"^привет", r"^здравствуй", r"^добр.*утр", r"^добр.*день", r"^добр.*вечер",
            r"^салом", r"^ассалому", r"^хайр", r"^hello", r"^hi$",
        ],
        priority=15,
    ),
    IntentPattern(
        intent=EmployeeIntent.HELP,
        patterns=[
            r"^помощь", r"^помоги", r"что.*умеешь", r"что.*можешь", r"как.*работ",
            r"ёрдам", r"yordam", r"нима қила олас", r"help",
        ],
        priority=15,
    ),
    # === Отпуска ===
    IntentPattern(
        intent=EmployeeIntent.LEAVE_BALANCE,
        patterns=[
            r"сколько.*дней.*отпуск", r"остаток.*отпуск", r"дней отпуска.*осталось",
            r"сколько.*отгул", r"неча кун.*таътил", r"таътил.*қолди",
        ],
        priority=10,
    ),
    IntentPattern(
        intent=EmployeeIntent.LEAVE_REQUEST,
        patterns=[
            r"как.*оформить.*отпуск", r"хочу.*отпуск", r"заявление на отпуск",
            r"больничн", r"отсутств", r"выходн", r"таътил.*олмоқ", r"таътил.*расмий",
        ],
        priority=5,
    ),
    # === KPI ===
    IntentPattern(
        intent=EmployeeIntent.KPI_EXPLAIN,
        patterns=[
            r"почему.*kpi.*низк", r"почему.*кпи.*низк", r"почему.*показател.*упал",
            r"объясни.*kpi", r"разъясни.*kpi", r"почему.*снизил",
            r"нега.*kpi.*паст", r"тушунтир.*kpi",
        ],
        priority=10,
    ),
    IntentPattern(
        intent=EmployeeIntent.KPI_QUESTION,
        patterns=[
            r"kpi", r"кпи", r"показател.*эффективн", r"результат.*работ", r"мой.*рейтинг",
            r"менинг.*натижа", r"самарадорлик",
        ],
        priority=3,
    ),
    # === Финансы ===
    IntentPattern(
        intent=EmployeeIntent.BONUS_INQUIRY,
        patterns=[
            r"бонус", r"премия", r"премии", r"когда.*выплат", r"размер.*премии",
            r"мукофот", r"bonus",
        ],
        priority=5,
    ),
    IntentPattern(
        intent=EmployeeIntent.SALARY_QUESTION,
        patterns=[
            r"зарплат", r"оклад", r"выплат", r"маош", r"ойлик", r"salary",
            r"когда.*получ", r"қачон.*олам",
        ],
        priority=5,
    ),
    # === Дисциплина ===
    IntentPattern(
        intent=EmployeeIntent.DISCIPLINE_QUESTION,
        patterns=[
            r"дисциплин", r"выговор", r"взыскан", r"штраф", r"нарушен",
            r"интизом", r"жарима", r"огоҳлантириш", r"ҳайфсан",
        ],
        priority=8,
    ),
    # === Признание ===
    IntentPattern(
        intent=EmployeeIntent.RECOGNITION_QUESTION,
        patterns=[
            r"признан", r"награ", r"достижен", r"благодарн", r"поощрен",
            r"эътироф", r"мукофот", r"ютуқ", r"миннатдорлик",
        ],
        priority=7,
    ),
    # === Обучение ===
    IntentPattern(
        intent=EmployeeIntent.TRAINING_QUESTION,
        patterns=[
            r"обучен", r"курс", r"тренинг", r"сертификат", r"экзамен",
            r"ўқиш", r"курс", r"тренинг", r"сертификат",
        ],
        priority=6,
    ),
    # === График работы ===
    IntentPattern(
        intent=EmployeeIntent.SCHEDULE_QUESTION,
        patterns=[
            r"график.*работ", r"расписан", r"смен", r"рабоч.*врем",
            r"иш.*вақт", r"жадвал", r"schedule",
        ],
        priority=6,
    ),
    # === Льготы ===
    IntentPattern(
        intent=EmployeeIntent.BENEFITS_QUESTION,
        patterns=[
            r"льгот", r"соцпакет", r"медицин.*страхов", r"дмс", r"корпоратив",
            r"имтиёз", r"тиббий суғурта",
        ],
        priority=6,
    ),
    # === Политики ===
    IntentPattern(
        intent=EmployeeIntent.POLICY_SEARCH,
        patterns=[
            r"политик", r"регламент", r"правил.*банк", r"порядок.*оформлен",
            r"процедур", r"документ.*нужн", r"сиёсат", r"қоида",
        ],
        priority=5,
    ),
]


class EmployeeIntentDetector:
    """Детектор интентов для сообщений сотрудников"""

    def detect(self, message: str) -> EmployeeIntent:
        """Определить интент сообщения"""
        message_lower = message.lower()

        best_intent = EmployeeIntent.GENERAL
        best_priority = -1

        for pattern_config in INTENT_PATTERNS:
            for pattern in pattern_config.patterns:
                if re.search(pattern, message_lower):
                    if pattern_config.priority > best_priority:
                        best_intent = pattern_config.intent
                        best_priority = pattern_config.priority
                        break

        logger.debug(f"Detected intent: {best_intent} for message: {message[:50]}...")
        return best_intent


class EmployeeKpiExplainer:
    """Объяснение KPI показателей"""

    # Шаблоны объяснений для низких показателей
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
        """Сгенерировать объяснение KPI"""
        total_score = kpi_data.get("total_score", 0)
        metrics = kpi_data.get("metrics", {})
        low_metrics = kpi_data.get("low_metrics", {})

        # Общее объяснение
        if total_score >= 90:
            general_explanation = (
                "Ваши показатели KPI отличные! Вы превышаете план по большинству метрик. "
                "Продолжайте в том же духе и делитесь опытом с коллегами."
            )
        elif total_score >= 70:
            general_explanation = (
                "Ваши показатели KPI хорошие, но есть возможности для улучшения. "
                "Обратите внимание на метрики, которые ниже плана."
            )
        elif total_score >= 50:
            general_explanation = (
                "Ваши показатели KPI требуют внимания. "
                "Рекомендуется обсудить с руководителем план улучшения."
            )
        else:
            general_explanation = (
                "Ваши показатели KPI критически низкие и требуют срочных мер. "
                "Необходимо составить план действий совместно с руководителем."
            )

        # Объяснения по метрикам
        metric_explanations = {}
        for metric_key, metric_data in low_metrics.items():
            completion = metric_data.get("completion", 0)
            if completion < 70:
                explanations = self.LOW_METRIC_EXPLANATIONS.get(metric_key, [
                    f"Показатель '{metric_data.get('name', metric_key)}' ниже плана ({completion:.1f}%).",
                ])
                metric_explanations[metric_data.get("name", metric_key)] = " ".join(explanations)

        # Рекомендации по улучшению
        improvement_suggestions = self._generate_improvement_suggestions(low_metrics, total_score)

        return {
            "explanation": general_explanation,
            "metric_explanations": metric_explanations,
            "improvement_suggestions": improvement_suggestions,
            "risk_assessment": self._assess_risk(total_score, low_metrics),
        }

    def _generate_improvement_suggestions(
        self, low_metrics: Dict, total_score: float
    ) -> List[str]:
        """Сгенерировать рекомендации по улучшению"""
        suggestions = []

        if total_score < 50:
            suggestions.append("Срочно назначьте встречу с руководителем для обсуждения плана улучшения")

        for metric_key, metric_data in low_metrics.items():
            completion = metric_data.get("completion", 0)
            if completion < 50:
                suggestions.append(f"Приоритетно улучшить показатель '{metric_data.get('name', metric_key)}'")
            elif completion < 70:
                suggestions.append(f"Обратить внимание на показатель '{metric_data.get('name', metric_key)}'")

        if not suggestions:
            suggestions.append("Поддерживайте текущий уровень показателей")
            suggestions.append("Рассмотрите возможность помощи коллегам с низкими показателями")

        return suggestions[:5]  # Максимум 5 рекомендаций

    def _assess_risk(self, total_score: float, low_metrics: Dict) -> Dict[str, Any]:
        """Оценить риски"""
        critical_metrics = sum(1 for m in low_metrics.values() if m.get("completion", 0) < 30)

        if total_score < 50 or critical_metrics >= 2:
            level = "high"
            message = "Высокий риск: требуются срочные меры"
        elif total_score < 70 or critical_metrics >= 1:
            level = "medium"
            message = "Средний риск: рекомендуется план улучшения"
        else:
            level = "low"
            message = "Низкий риск: показатели в пределах нормы"

        return {
            "level": level,
            "message": message,
            "critical_metrics_count": critical_metrics,
        }


class EmployeeRecommendationEngine:
    """Генератор рекомендаций для улучшения KPI"""

    # База рекомендаций по типам метрик
    RECOMMENDATION_BASE = {
        "sales": [
            {
                "type": "quick",
                "action": "Проведите follow-up звонки клиентам, которые проявляли интерес",
                "effect": "Быстрое увеличение конверсии",
                "impact": 3.0,
            },
            {
                "type": "medium",
                "action": "Пройдите тренинг по продажам и техникам закрытия сделок",
                "effect": "Улучшение навыков продаж",
                "impact": 5.0,
            },
            {
                "type": "long",
                "action": "Развивайте личный бренд и сеть контактов в отрасли",
                "effect": "Долгосрочный рост клиентской базы",
                "impact": 10.0,
            },
        ],
        "customer_satisfaction": [
            {
                "type": "quick",
                "action": "Увеличьте скорость ответа на запросы клиентов до 2 часов",
                "effect": "Повышение удовлетворённости",
                "impact": 2.5,
            },
            {
                "type": "medium",
                "action": "Внедрите практику регулярной обратной связи с клиентами",
                "effect": "Улучшение качества сервиса",
                "impact": 4.0,
            },
        ],
        "task_completion": [
            {
                "type": "quick",
                "action": "Используйте технику Pomodoro для повышения продуктивности",
                "effect": "Увеличение выполненных задач",
                "impact": 3.0,
            },
            {
                "type": "medium",
                "action": "Освойте методологию GTD (Getting Things Done)",
                "effect": "Системный подход к задачам",
                "impact": 5.0,
            },
        ],
        "training": [
            {
                "type": "quick",
                "action": "Запланируйте 2 часа в неделю на обучение",
                "effect": "Выполнение плана обучения",
                "impact": 4.0,
            },
        ],
    }

    def generate_recommendations(
        self, kpi_data: Dict[str, Any], employee_data: Dict[str, Any]
    ) -> Dict[str, Any]:
        """Сгенерировать персонализированные рекомендации"""
        low_metrics = kpi_data.get("low_metrics", kpi_data.get("metrics", {}))
        total_score = kpi_data.get("total_score", 0)

        recommendations = []
        priority = 1

        # Сортируем метрики по важности (низкие сначала)
        sorted_metrics = sorted(
            low_metrics.items(),
            key=lambda x: x[1].get("completion", 100)
        )

        for metric_key, metric_data in sorted_metrics:
            completion = metric_data.get("completion", 100)
            if completion >= 90:
                continue

            metric_recs = self.RECOMMENDATION_BASE.get(metric_key, [])

            for rec in metric_recs:
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

        # Добавляем общие рекомендации если мало
        if len(recommendations) < 3:
            recommendations.extend([
                {
                    "type": "quick",
                    "action": "Обсудите ваши показатели с руководителем",
                    "effect": "Получение обратной связи и поддержки",
                    "priority": priority,
                    "expected_impact": 2.0,
                },
                {
                    "type": "medium",
                    "action": "Изучите практики топ-перформеров вашего отдела",
                    "effect": "Перенятие лучших практик",
                    "priority": priority + 1,
                    "expected_impact": 3.0,
                },
            ])

        # Рассчитываем ожидаемое улучшение
        total_impact = sum(r.get("expected_impact", 0) for r in recommendations[:5])

        return {
            "recommendations": recommendations[:7],
            "priority_actions": [r for r in recommendations if r.get("type") == "quick"][:3],
            "expected_improvement": {
                "potential_score_increase": min(total_impact, 100 - total_score),
                "message": f"При выполнении рекомендаций возможен рост KPI на {min(total_impact, 20):.1f}%",
            },
        }


class EmployeeChatHandler:
    """Обработчик чата с сотрудниками"""

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
        """Обработать сообщение чата"""
        intent = self.intent_detector.detect(message)

        response = self._generate_response(
            message=message,
            intent=intent,
            context=context,
            facts=facts,
            policies=policies,
        )

        return {
            "response": response,
            "intent": intent.value,
            "confidence": 0.85,
            "sources": self._extract_sources(policies),
        }

    def _generate_response(
        self,
        message: str,
        intent: EmployeeIntent,
        context: Dict[str, Any],
        facts: Dict[str, Any],
        policies: List[Dict[str, Any]],
    ) -> str:
        """Сгенерировать ответ на основе интента"""

        handlers = {
            EmployeeIntent.GREETING: lambda: self._handle_greeting(context),
            EmployeeIntent.HELP: lambda: self._handle_help(),
            EmployeeIntent.KPI_QUESTION: lambda: self._handle_kpi_question(facts),
            EmployeeIntent.KPI_EXPLAIN: lambda: self._handle_kpi_explain(facts),
            EmployeeIntent.BONUS_INQUIRY: lambda: self._handle_bonus_inquiry(facts),
            EmployeeIntent.SALARY_QUESTION: lambda: self._handle_salary_question(context),
            EmployeeIntent.LEAVE_BALANCE: lambda: self._handle_leave_balance(context),
            EmployeeIntent.LEAVE_REQUEST: lambda: self._handle_leave_request(policies),
            EmployeeIntent.DISCIPLINE_QUESTION: lambda: self._handle_discipline_question(context),
            EmployeeIntent.RECOGNITION_QUESTION: lambda: self._handle_recognition_question(context),
            EmployeeIntent.TRAINING_QUESTION: lambda: self._handle_training_question(context),
            EmployeeIntent.SCHEDULE_QUESTION: lambda: self._handle_schedule_question(context),
            EmployeeIntent.BENEFITS_QUESTION: lambda: self._handle_benefits_question(context),
            EmployeeIntent.POLICY_SEARCH: lambda: self._handle_policy_search(message, policies),
        }

        handler = handlers.get(intent)
        if handler:
            return handler()
        return self._handle_general_question(message, context, policies)

    def _handle_kpi_question(self, facts: Dict) -> str:
        """Ответ на вопрос о KPI"""
        kpi = facts.get("current_kpi", {})

        if not kpi:
            return (
                "К сожалению, у меня нет данных о вашем текущем KPI. "
                "Пожалуйста, обратитесь в HR отдел или проверьте раздел 'Мои KPI' в портале."
            )

        score = kpi.get("total_score", 0)
        period = kpi.get("period", "текущий месяц")

        status = "отлично" if score >= 90 else "хорошо" if score >= 70 else "требует улучшения" if score >= 50 else "критически низкий"

        response = f"Ваш KPI за {period} составляет {score:.1f}% - это {status}.\n\n"

        metrics = kpi.get("metrics", {})
        if metrics:
            response += "Основные показатели:\n"
            for key, metric in metrics.items():
                completion = metric.get("completion", 0)
                icon = "✅" if completion >= 90 else "⚠️" if completion >= 70 else "❌"
                response += f"{icon} {metric.get('name', key)}: {completion:.1f}%\n"

        if kpi.get("bonus_eligible"):
            response += f"\n💰 Вы имеете право на бонус!"

        return response

    def _handle_kpi_explain(self, facts: Dict) -> str:
        """Объяснение KPI"""
        kpi = facts.get("current_kpi", {})

        if not kpi:
            return "Для объяснения KPI необходимы данные о ваших показателях. Пожалуйста, откройте раздел 'Мои KPI'."

        explanation = self.kpi_explainer.explain_kpi(kpi)

        response = explanation.get("explanation", "") + "\n\n"

        metric_explanations = explanation.get("metric_explanations", {})
        if metric_explanations:
            response += "📊 По отдельным показателям:\n"
            for metric_name, explanation_text in metric_explanations.items():
                response += f"\n• {metric_name}: {explanation_text}\n"

        suggestions = explanation.get("improvement_suggestions", [])
        if suggestions:
            response += "\n💡 Рекомендации:\n"
            for suggestion in suggestions:
                response += f"• {suggestion}\n"

        return response

    def _handle_bonus_inquiry(self, facts: Dict) -> str:
        """Ответ на вопрос о бонусе"""
        kpi = facts.get("current_kpi", {})

        if not kpi:
            return (
                "Для информации о бонусе необходимы данные о вашем KPI. "
                "Бонус обычно начисляется при KPI от 50% и зависит от достигнутых показателей."
            )

        score = kpi.get("total_score", 0)
        bonus_eligible = kpi.get("bonus_eligible", False)

        if bonus_eligible:
            return (
                f"✅ При текущем KPI {score:.1f}% вы имеете право на бонус!\n\n"
                f"Размер бонуса зависит от коэффициента:\n"
                f"• KPI 100%+ → коэффициент 1.5\n"
                f"• KPI 90-99% → коэффициент 1.2\n"
                f"• KPI 70-89% → коэффициент 1.0\n"
                f"• KPI 50-69% → коэффициент 0.7\n\n"
                f"Бонусы обычно выплачиваются в следующем месяце после закрытия периода."
            )
        else:
            return (
                f"❌ При текущем KPI {score:.1f}% бонус не начисляется.\n\n"
                f"Минимальный порог для получения бонуса - 50%.\n"
                f"Рекомендую обратить внимание на показатели с низким выполнением."
            )

    def _handle_leave_balance(self, context: Dict) -> str:
        """Остаток отпуска"""
        return (
            "Информация об остатке отпуска доступна в системе управления персоналом.\n\n"
            "По стандартным правилам:\n"
            "• Базовый отпуск: 28 календарных дней в год\n"
            "• Дополнительный отпуск может зависеть от стажа и условий труда\n\n"
            "Для точной информации обратитесь в HR отдел или проверьте личный кабинет."
        )

    def _handle_leave_request(self, policies: List[Dict]) -> str:
        """Оформление отпуска"""
        response = (
            "Порядок оформления отпуска:\n\n"
            "1️⃣ Согласуйте даты отпуска с руководителем\n"
            "2️⃣ Подайте заявление не менее чем за 2 недели\n"
            "3️⃣ Дождитесь утверждения HR отделом\n"
            "4️⃣ Получите приказ об отпуске\n\n"
        )

        # Добавляем ссылки на политики если есть
        leave_policies = [p for p in policies if "отпуск" in p.get("title", "").lower()]
        if leave_policies:
            response += "📄 Связанные документы:\n"
            for policy in leave_policies[:3]:
                response += f"• {policy.get('title')} ({policy.get('code', '')})\n"

        return response

    def _handle_policy_search(self, message: str, policies: List[Dict]) -> str:
        """Поиск политик"""
        if not policies:
            return (
                "По вашему запросу политики не найдены.\n"
                "Попробуйте использовать другие ключевые слова или "
                "обратитесь в раздел 'Политики' для полного списка документов."
            )

        response = f"📚 Найдено документов: {len(policies)}\n\n"

        for policy in policies[:5]:
            response += (
                f"📄 **{policy.get('title')}**\n"
                f"   Код: {policy.get('code', 'N/A')} | "
                f"Категория: {policy.get('category', 'N/A')}\n"
                f"   {policy.get('summary', '')[:100]}...\n\n"
            )

        return response

    def _handle_greeting(self, context: Dict) -> str:
        """Приветствие"""
        department = context.get("department", "")
        position = context.get("position", "")

        greeting = "Ассалому алайкум! 👋\n\n"
        greeting += "Мен BRB Bank ходимлар порталининг AI-ёрдамчисиман.\n\n"

        if position:
            greeting += f"Сиз: {position}"
            if department:
                greeting += f" ({department})"
            greeting += "\n\n"

        greeting += "Сизга қандай ёрдам бера оламан?"
        return greeting

    def _handle_help(self) -> str:
        """Помощь по возможностям"""
        return (
            "Мен сизга қуйидагилар бўйича ёрдам бера оламан:\n\n"
            "📊 **KPI ва самарадорлик**\n"
            "   • Менинг KPI қанча?\n"
            "   • Нега KPI паст?\n"
            "   • KPI ни қандай яхшилаш мумкин?\n\n"
            "💰 **Молиявий саволлар**\n"
            "   • Бонус қачон?\n"
            "   • Премия миқдори\n"
            "   • Маош тўғрисида\n\n"
            "🏖️ **Таътил ва бўшлиқлар**\n"
            "   • Таътил қолдиғи\n"
            "   • Таътилни қандай расмийлаштириш\n\n"
            "📋 **Интизом**\n"
            "   • Интизомий чоралар\n"
            "   • Жарималар тўғрисида\n\n"
            "🏆 **Эътироф**\n"
            "   • Мукофотлар тизими\n"
            "   • Миннатдорлик билдириш\n\n"
            "📚 **Ўқув курслар**\n"
            "   • Мажбурий курслар\n"
            "   • Сертификатлар\n\n"
            "📄 **Сиёсат ва қоидалар**\n"
            "   • Банк сиёсатлари\n"
            "   • Регламентлар\n\n"
            "Саволингизни ёзинг!"
        )

    def _handle_salary_question(self, context: Dict) -> str:
        """Вопросы о зарплате"""
        return (
            "💵 **Маош тўғрисида маълумот**\n\n"
            "Маош тўловлари:\n"
            "• Аванс: ҳар ойнинг 15-санасида\n"
            "• Асосий маош: ҳар ойнинг охирида\n\n"
            "Маош миқдори ва тафсилотлари учун:\n"
            "• HR бўлимига мурожаат қилинг\n"
            "• Ёки 'Менинг маълумотларим' бўлимини текширинг\n\n"
            "Маош карта орқали ўтказилади."
        )

    def _handle_discipline_question(self, context: Dict) -> str:
        """Вопросы о дисциплине"""
        return (
            "📋 **Интизомий чоралар тўғрисида**\n\n"
            "Интизомий чоралар турлари:\n"
            "• ⚠️ Огоҳлантириш - енгил бузилишлар\n"
            "• 📝 Ҳайфсан - ўртача бузилишлар\n"
            "• 💰 Жарима - молиявий жазо\n"
            "• 🚫 Ишдан четлатиш - жиддий бузилишлар\n\n"
            "Сизга тегишли интизомий чораларни кўриш учун:\n"
            "👉 Портал → 'Интизом' бўлими\n\n"
            "Шикоят қилиш:\n"
            "• Шикоят муддати - 10 иш куни\n"
            "• Шикоят матни камида 50 та белги\n\n"
            "Саволлар бўлса, HR бўлимига мурожаат қилинг."
        )

    def _handle_recognition_question(self, context: Dict) -> str:
        """Вопросы о признании"""
        return (
            "🏆 **Эътироф тизими**\n\n"
            "BRB Bank ходимларни қўллаб-қувватлайди!\n\n"
            "Эътироф турлари:\n"
            "• ⭐ Ой ходими\n"
            "• 🌟 Чорак ходими\n"
            "• 👑 Йил ходими\n"
            "• 🎯 Махсус мукофотлар\n\n"
            "Номзод кўрсатиш:\n"
            "👉 Портал → 'Эътироф' → 'Номзод кўрсатиш'\n\n"
            "Ўз ютуқларингиз ва балларингизни кўриш:\n"
            "👉 'Менинг балларим' бўлими\n\n"
            "Ҳамкасбларингизни мукофотланг!"
        )

    def _handle_training_question(self, context: Dict) -> str:
        """Вопросы об обучении"""
        return (
            "📚 **Ўқув курслар**\n\n"
            "Мажбурий курслар:\n"
            "• AML/KYC асослари\n"
            "• Маълумотлар хавфсизлиги\n"
            "• Банк этикаси\n\n"
            "Курсларни кўриш:\n"
            "👉 Ўқув порталига киринг\n\n"
            "⚠️ Эслатма:\n"
            "• Мажбурий курслар ўз вақтида ўтилиши керак\n"
            "• Курслар KPI га таъсир қилади\n"
            "• Сертификатлар HR бўлимида\n\n"
            "Саволлар бўлса, HR бўлимига мурожаат қилинг."
        )

    def _handle_schedule_question(self, context: Dict) -> str:
        """Вопросы о графике работы"""
        return (
            "🕐 **Иш вақти**\n\n"
            "Стандарт иш графиги:\n"
            "• Душанба-Жума: 09:00 - 18:00\n"
            "• Тушлик: 13:00 - 14:00\n"
            "• Шанба-Якшанба: дам олиш\n\n"
            "Иш графигини ўзгартириш:\n"
            "• Раҳбар билан келишинг\n"
            "• HR бўлимига ариза юборинг\n\n"
            "Масофавий иш:\n"
            "• Раҳбар рухсати билан\n"
            "• Ҳафтада 2 кунгача\n\n"
            "Тафсилотлар учун HR га мурожаат қилинг."
        )

    def _handle_benefits_question(self, context: Dict) -> str:
        """Вопросы о льготах"""
        return (
            "🎁 **Имтиёзлар ва соцпакет**\n\n"
            "BRB Bank ходимларига:\n\n"
            "🏥 **Тиббий суғурта**\n"
            "• ДМС полиси\n"
            "• Оила аъзолари учун чегирма\n\n"
            "💰 **Молиявий имтиёзлар**\n"
            "• Имтиёзли кредитлар\n"
            "• Депозитларга юқори фоиз\n\n"
            "🎓 **Ривожланиш**\n"
            "• Бепул ўқув курслар\n"
            "• Сертификатлаш учун тўлов\n\n"
            "🏋️ **Соғлиқ**\n"
            "• Корпоратив спорт залига чегирма\n\n"
            "Тўлиқ рўйхат учун HR бўлимига мурожаат қилинг."
        )

    def _handle_general_question(
        self, message: str, context: Dict, policies: List[Dict]
    ) -> str:
        """Общий вопрос"""
        return (
            "Мен - BRB Bank ходимлар порталининг AI-ёрдамчисиман.\n\n"
            "Сизга қуйидагилар бўйича ёрдам бера оламан:\n"
            "• 📊 KPI ва самарадорлик кўрсаткичлари\n"
            "• 💰 Бонус ва мукофотлар\n"
            "• 🏖️ Таътил ва бўшлиқлар\n"
            "• 📋 Интизомий чоралар\n"
            "• 🏆 Эътироф тизими\n"
            "• 📚 Ўқув курслар\n"
            "• 📄 Сиёсат ва регламентлар\n\n"
            "Аниқ саволингизни ёзинг!"
        )

    def _extract_sources(self, policies: List[Dict]) -> List[Dict]:
        """Извлечь источники для ответа"""
        return [
            {"title": p.get("title"), "code": p.get("code")}
            for p in policies[:3]
        ]


# Экземпляры для использования в main.py
intent_detector = EmployeeIntentDetector()
kpi_explainer = EmployeeKpiExplainer()
recommendation_engine = EmployeeRecommendationEngine()
chat_handler = EmployeeChatHandler()
