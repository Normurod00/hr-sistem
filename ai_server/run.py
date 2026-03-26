#!/usr/bin/env python3
"""
HR AI Server - Quick Launcher
Быстрый запуск сервера (Rule-Based версия)
"""

import os
import sys


def check_dependencies():
    """Проверка установленных зависимостей"""
    required = {
        'fastapi': 'fastapi',
        'uvicorn': 'uvicorn',
        'pydantic': 'pydantic',
        'yaml': 'pyyaml',
    }
    missing = []

    for module, package in required.items():
        try:
            __import__(module)
        except ImportError:
            missing.append(package)

    if missing:
        print(f"Отсутствуют пакеты: {', '.join(missing)}")
        print("Установите их командой:")
        print("  pip install -r requirements.txt")
        return False

    return True


def main():
    """Главная функция запуска"""
    print("""
    ╔═══════════════════════════════════════════════╗
    ║        HR AI SERVER v2.0 (Rule-Based)         ║
    ╠═══════════════════════════════════════════════╣
    ║  Анализ резюме без внешних LLM-моделей       ║
    ╚═══════════════════════════════════════════════╝
    """)

    # Проверяем зависимости
    if not check_dependencies():
        sys.exit(1)

    # Запускаем сервер
    os.chdir(os.path.dirname(os.path.abspath(__file__)))

    try:
        import uvicorn
        import yaml

        # Загружаем конфиг
        config = {}
        if os.path.exists('config.yaml'):
            with open('config.yaml', 'r', encoding='utf-8') as f:
                config = yaml.safe_load(f) or {}

        host = config.get('server', {}).get('host', '127.0.0.1')
        port = config.get('server', {}).get('port', 8080)
        debug = config.get('server', {}).get('debug', True)

        print(f"""
    Сервер:        http://{host}:{port}
    API Docs:      http://{host}:{port}/docs
    Health Check:  http://{host}:{port}/health
    ─────────────────────────────────────────────────
    Функции:
    • Парсинг резюме (PDF, DOCX, TXT)
    • Анализ кандидата под вакансию
    • Match Score (соответствие %)
    • Сильные/слабые стороны
    • Рекомендация: подходит/не подходит
    • Вопросы для интервью
    ─────────────────────────────────────────────────
        """)

        uvicorn.run(
            "main:app",
            host=host,
            port=port,
            reload=debug
        )

    except KeyboardInterrupt:
        print("\nСервер остановлен")
    except Exception as e:
        print(f"Ошибка запуска: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)


if __name__ == "__main__":
    main()
