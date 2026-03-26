# HR Robot - Деплой на сервер

## Автозапуск AI системы 24/7

### Linux (Ubuntu/Debian) - systemd

```bash
# 1. Копируем файлы на сервер
cd /var/www/hr-brb/deploy

# 2. Устанавливаем сервисы (от root)
sudo bash install-services.sh
```

### Управление сервисами

```bash
# Статус
systemctl status hr-ai-server
systemctl status hr-queue-worker
systemctl status hr-scheduler

# Перезапуск
systemctl restart hr-ai-server
systemctl restart hr-queue-worker
systemctl restart hr-scheduler

# Остановка
systemctl stop hr-ai-server

# Логи в реальном времени
journalctl -u hr-ai-server -f
tail -f /var/log/hr-robot/ai-server.log
```

### Windows (разработка)

```batch
:: Запуск всего одним кликом
START-ALL.bat
```

Или по отдельности:
- `start-ai-server.bat` - AI сервер (Python)
- `start-ai-worker.bat` - Queue Worker (Laravel)
- `start-scheduler.bat` - Scheduler (Laravel)

---

## Что работает автоматически

| Сервис | Функция | Интервал |
|--------|---------|----------|
| hr-ai-server | AI анализ резюме | Постоянно |
| hr-queue-worker | Выполнение задач | Постоянно |
| hr-scheduler | Проверка новых заявок | Каждые 2 мин |

## Логика обработки

1. **Новая заявка** → Observer создаёт Job
2. **Scheduler** каждые 2 мин проверяет необработанные
3. **Queue Worker** выполняет парсинг и анализ
4. Уже проверенные заявки **НЕ перепроверяются**
5. Если загружен **новый файл** → старый анализ сбрасывается
