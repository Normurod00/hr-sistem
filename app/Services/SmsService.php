<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\SmsNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Отправить SMS уведомление о смене статуса заявки
     */
    public function sendStatusNotification(Application $application, ApplicationStatus $newStatus): ?SmsNotification
    {
        $phone = $application->candidate->phone;

        if (!$phone) {
            Log::warning('Cannot send SMS: candidate has no phone', [
                'application_id' => $application->id,
                'candidate_id' => $application->user_id,
            ]);
            return null;
        }

        // Формируем сообщение в зависимости от статуса
        $message = $this->buildStatusMessage($application, $newStatus);

        if (!$message) {
            return null; // Не отправляем SMS для некоторых статусов
        }

        return $this->send($phone, $message, 'status_change', $application->id);
    }

    /**
     * Отправить напоминание о тесте
     */
    public function sendTestReminder(Application $application): ?SmsNotification
    {
        $phone = $application->candidate->phone;

        if (!$phone) {
            return null;
        }

        $message = "Напоминание: у вас есть незавершённый тест для вакансии \"{$application->vacancy->title}\". Пройдите его в личном кабинете.";

        return $this->send($phone, $message, 'test_reminder', $application->id);
    }

    /**
     * Отправить приглашение на собеседование
     */
    public function sendInterviewInvite(Application $application, string $datetime, ?string $meetingLink = null): ?SmsNotification
    {
        $phone = $application->candidate->phone;

        if (!$phone) {
            return null;
        }

        $message = "Приглашаем вас на собеседование по вакансии \"{$application->vacancy->title}\" на {$datetime}.";

        if ($meetingLink) {
            $message .= " Ссылка: {$meetingLink}";
        }

        return $this->send($phone, $message, 'interview_invite', $application->id);
    }

    /**
     * Основной метод отправки SMS
     */
    public function send(string $phone, string $message, string $type = 'general', ?int $applicationId = null): SmsNotification
    {
        // Нормализуем номер телефона
        $phone = $this->normalizePhone($phone);

        // Создаём запись в БД
        $notification = SmsNotification::create([
            'application_id' => $applicationId,
            'phone' => $phone,
            'message' => $message,
            'type' => $type,
            'status' => 'pending',
        ]);

        try {
            // Пытаемся отправить SMS через провайдера
            $result = $this->sendViaProvider($phone, $message);

            if ($result['success']) {
                $notification->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'provider_response' => $result['response'] ?? null,
                ]);
            } else {
                $notification->update([
                    'status' => 'failed',
                    'error_message' => $result['error'] ?? 'Unknown error',
                    'provider_response' => $result['response'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('SMS send failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            $notification->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $notification;
    }

    /**
     * Формирует сообщение в зависимости от нового статуса
     */
    private function buildStatusMessage(Application $application, ApplicationStatus $status): ?string
    {
        $vacancyTitle = $application->vacancy->title;
        $companyName = config('app.name', 'BRB');

        return match ($status) {
            ApplicationStatus::InReview => "Ваша заявка на вакансию \"{$vacancyTitle}\" в {$companyName} принята на рассмотрение.",

            ApplicationStatus::Invited => "Поздравляем! Вы приглашены на следующий этап отбора по вакансии \"{$vacancyTitle}\" в {$companyName}. Проверьте личный кабинет для деталей.",

            ApplicationStatus::Rejected => "К сожалению, ваша заявка на вакансию \"{$vacancyTitle}\" в {$companyName} отклонена. Спасибо за интерес к нашей компании.",

            ApplicationStatus::Hired => "Поздравляем! Вы приняты на должность \"{$vacancyTitle}\" в {$companyName}! Свяжитесь с HR для оформления.",

            default => null, // Для других статусов не отправляем SMS
        };
    }

    /**
     * Нормализует номер телефона
     */
    private function normalizePhone(string $phone): string
    {
        // Удаляем все символы кроме цифр и +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Если начинается с 8, заменяем на +7 (для России/СНГ)
        if (str_starts_with($phone, '8') && strlen($phone) === 11) {
            $phone = '+7' . substr($phone, 1);
        }

        // Если нет +, добавляем + в начало для международного формата
        if (!str_starts_with($phone, '+')) {
            // Для Узбекистана
            if (strlen($phone) === 9 && (str_starts_with($phone, '9') || str_starts_with($phone, '7'))) {
                $phone = '+998' . $phone;
            } elseif (strlen($phone) === 12 && str_starts_with($phone, '998')) {
                $phone = '+' . $phone;
            }
        }

        return $phone;
    }

    /**
     * Отправка через SMS провайдера
     *
     * Это абстрактный метод, который можно настроить под конкретного провайдера:
     * - Eskiz.uz (для Узбекистана)
     * - SMS.ru (для России)
     * - Twilio (международный)
     * - и др.
     */
    private function sendViaProvider(string $phone, string $message): array
    {
        $provider = config('services.sms.provider', 'log');

        return match ($provider) {
            'eskiz' => $this->sendViaEskiz($phone, $message),
            'playmobile' => $this->sendViaPlayMobile($phone, $message),
            default => $this->logSms($phone, $message),
        };
    }

    /**
     * Eskiz.uz provider (популярный в Узбекистане)
     */
    private function sendViaEskiz(string $phone, string $message): array
    {
        $token = config('services.sms.eskiz.token');
        $from = config('services.sms.eskiz.from', '4546');

        if (!$token) {
            return $this->logSms($phone, $message);
        }

        try {
            $response = Http::timeout(10)
                ->withToken($token)
                ->post('https://notify.eskiz.uz/api/message/sms/send', [
                    'mobile_phone' => ltrim($phone, '+'),
                    'message' => $message,
                    'from' => $from,
                ]);

            return [
                'success' => $response->successful() && $response->json('status') === 'success',
                'response' => $response->json(),
                'error' => $response->json('message'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * PlayMobile provider (для Узбекистана)
     */
    private function sendViaPlayMobile(string $phone, string $message): array
    {
        $login = config('services.sms.playmobile.login');
        $password = config('services.sms.playmobile.password');
        $originator = config('services.sms.playmobile.originator', 'BRB');

        if (!$login || !$password) {
            return $this->logSms($phone, $message);
        }

        try {
            $response = Http::timeout(10)
                ->withBasicAuth($login, $password)
                ->post('https://send.smsxabar.uz/broker-api/send', [
                    'messages' => [
                        [
                            'recipient' => ltrim($phone, '+'),
                            'message-id' => uniqid('sms_'),
                            'sms' => [
                                'originator' => $originator,
                                'content' => [
                                    'text' => $message,
                                ],
                            ],
                        ],
                    ],
                ]);

            return [
                'success' => $response->successful(),
                'response' => $response->json(),
                'error' => !$response->successful() ? 'HTTP ' . $response->status() : null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fallback: просто логируем SMS (для тестирования)
     */
    private function logSms(string $phone, string $message): array
    {
        Log::info('SMS (mock)', [
            'phone' => $phone,
            'message' => $message,
            'length' => mb_strlen($message),
        ]);

        return [
            'success' => true,
            'response' => ['provider' => 'log', 'logged' => true],
        ];
    }
}
