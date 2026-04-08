# HR-System AI Output Specification v2.0
# Enterprise-Grade Structured AI Responses

---

## 1. CANDIDATE ANALYSIS RESPONSE

```json
{
  "meta": {
    "model": "hybrid-pipeline-v2.0",
    "method": "rule_based+llm_fallback",
    "processed_at": "2026-04-07T14:32:11Z",
    "processing_time_ms": 1847,
    "confidence": 0.87,
    "data_quality": "high",
    "skills_analyzed": 52,
    "resume_pages": 2
  },

  "verdict": {
    "decision": "recommend",
    "label": "Рекомендован к интервью",
    "confidence": 0.87,
    "risk_level": "low",
    "color": "green"
  },

  "match_score": {
    "total": 73,
    "breakdown": {
      "must_have_skills": { "score": 67, "weight": 0.50, "matched": 2, "total": 3, "matched_list": ["Python", "Django"], "missing_list": ["Kubernetes"] },
      "nice_to_have_skills": { "score": 50, "weight": 0.30, "matched": 1, "total": 2, "matched_list": ["Docker"], "missing_list": ["K8s"] },
      "experience": { "score": 100, "weight": 0.20, "candidate_years": 5.0, "required_years": 3.0 }
    },
    "explanation": "Кандидат покрывает 67% обязательных навыков. Основной пробел — Kubernetes, который можно компенсировать Docker-опытом."
  },

  "strengths": [
    { "text": "5 лет коммерческого опыта с Python и Django", "category": "experience", "impact": "high" },
    { "text": "Опыт работы с PostgreSQL и Redis (ключевые технологии вакансии)", "category": "skills", "impact": "high" },
    { "text": "Опыт в банковской сфере (Fintech домен)", "category": "domain", "impact": "medium" }
  ],

  "weaknesses": [
    { "text": "Отсутствует опыт работы с Kubernetes", "category": "skills", "severity": "medium", "mitigable": true, "mitigation": "Имеет Docker-опыт — обучение K8s займёт 2-3 недели" },
    { "text": "Нет опыта управления командой", "category": "leadership", "severity": "low", "mitigable": true, "mitigation": "Позиция не требует управления — не критично" }
  ],

  "risks": [
    { "text": "Частая смена работы: 3 компании за 2.5 года", "severity": "medium", "type": "retention", "recommendation": "Уточнить причины на интервью" }
  ],

  "interview_questions": [
    { "question": "Расскажите о вашем опыте контейнеризации. Как вы организуете CI/CD без Kubernetes?", "target": "skills_gap", "priority": "high" },
    { "question": "Что стало причиной ухода из последних двух компаний?", "target": "retention_risk", "priority": "high" },
    { "question": "Опишите самый сложный проект в банковской сфере", "target": "domain_depth", "priority": "medium" }
  ],

  "recommendation": {
    "text": "Рекомендуется пригласить на техническое интервью. Кандидат имеет сильный бэкенд-стек и релевантный домен (Fintech). Основной пробел — Kubernetes — компенсируется Docker-опытом и может быть закрыт за 2-3 недели обучения.",
    "next_steps": [
      "Провести техническое интервью (фокус: системный дизайн, PostgreSQL оптимизация)",
      "Уточнить причины смены работы",
      "Оценить потенциал к освоению Kubernetes"
    ],
    "estimated_onboarding": "2-3 недели",
    "salary_fit": "within_range"
  }
}
```

---

## 2. KPI EXPLANATION RESPONSE

```json
{
  "meta": {
    "model": "hybrid-pipeline-v2.0",
    "period": "Март 2026",
    "processed_at": "2026-04-07T14:32:11Z"
  },

  "summary": {
    "overall_score": 78.5,
    "trend": "improving",
    "trend_change": +3.2,
    "risk_level": "low",
    "explanation": "KPI за март показывает положительную динамику (+3.2% к февралю). Основной рост за счёт показателя 'Качество обслуживания'. Показатель 'Скорость обработки' требует внимания."
  },

  "metrics": [
    {
      "name": "Качество обслуживания",
      "score": 95.0,
      "weight": 0.30,
      "status": "exceeded",
      "trend": "+5.0 vs прошлый месяц",
      "explanation": "Превышение плана на 15%. Клиентская оценка стабильно растёт 3 месяца подряд.",
      "recommendation": null
    },
    {
      "name": "Скорость обработки заявок",
      "score": 62.0,
      "weight": 0.25,
      "status": "below_target",
      "trend": "-8.0 vs прошлый месяц",
      "explanation": "Снижение связано с увеличением объёма входящих заявок на 40% в марте.",
      "recommendation": "Рекомендуется перераспределение нагрузки или временное привлечение дополнительного сотрудника."
    }
  ],

  "improvement_suggestions": [
    { "priority": "high", "area": "Скорость обработки", "action": "Внедрить шаблоны быстрых ответов для типовых заявок", "expected_impact": "+15-20% к скорости" },
    { "priority": "medium", "area": "Общий KPI", "action": "Пройти тренинг по приоритизации задач", "expected_impact": "+5-10% общего KPI" }
  ],

  "forecast": {
    "next_period_estimate": 81.2,
    "confidence": 0.75,
    "explanation": "При сохранении текущей динамики и выполнении рекомендаций, ожидаемый KPI за апрель — 81.2%"
  }
}
```

---

## 3. AI CHAT RESPONSE

```json
{
  "meta": {
    "intent": "kpi_explain",
    "confidence": 0.92,
    "context_used": ["kpi_snapshot", "policy_db"],
    "response_time_ms": 340
  },

  "message": "Ваш текущий KPI за март составляет 78.5%. Это на 3.2% выше, чем в феврале. Основной вклад в рост — показатель 'Качество обслуживания' (95%). Показатель 'Скорость обработки' снизился на 8% — рекомендую обратить на него внимание.",

  "structured_data": {
    "type": "kpi_summary",
    "score": 78.5,
    "trend": "+3.2%",
    "top_metric": "Качество обслуживания",
    "attention_metric": "Скорость обработки"
  },

  "actions": [
    { "label": "Подробнее о KPI", "route": "/employee/kpi/current" },
    { "label": "Получить рекомендации", "action": "get_recommendations" }
  ],

  "sources": [
    { "type": "kpi_snapshot", "period": "Март 2026" },
    { "type": "policy", "name": "Положение о KPI", "section": "Раздел 3.2" }
  ]
}
```
