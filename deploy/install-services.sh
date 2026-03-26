#!/bin/bash

# HR Robot - Установка systemd сервисов
# Запускать с правами root: sudo bash install-services.sh

set -e

echo "==================================="
echo "HR Robot - Установка сервисов"
echo "==================================="

# Создаём директорию для логов
mkdir -p /var/log/hr-robot
chown www-data:www-data /var/log/hr-robot

# Копируем сервисы
cp hr-ai-server.service /etc/systemd/system/
cp hr-queue-worker.service /etc/systemd/system/
cp hr-scheduler.service /etc/systemd/system/

# Перезагружаем systemd
systemctl daemon-reload

# Включаем автозапуск
systemctl enable hr-ai-server
systemctl enable hr-queue-worker
systemctl enable hr-scheduler

# Запускаем сервисы
systemctl start hr-ai-server
systemctl start hr-queue-worker
systemctl start hr-scheduler

echo ""
echo "==================================="
echo "Сервисы установлены и запущены!"
echo "==================================="
echo ""
echo "Проверка статуса:"
echo "  systemctl status hr-ai-server"
echo "  systemctl status hr-queue-worker"
echo "  systemctl status hr-scheduler"
echo ""
echo "Просмотр логов:"
echo "  tail -f /var/log/hr-robot/ai-server.log"
echo "  tail -f /var/log/hr-robot/queue-worker.log"
echo "  tail -f /var/log/hr-robot/scheduler.log"
echo ""
echo "Перезапуск:"
echo "  systemctl restart hr-ai-server"
echo "  systemctl restart hr-queue-worker"
echo "  systemctl restart hr-scheduler"
