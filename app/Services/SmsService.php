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
        $application->loadMissing(['candidate', 'vacancy']);

        $phone = $application->candidate?->phone;
        $userId = $application->user_id; // ВАЖНО: sms_notifications.user_id -> users.id

        if (!$phone) {
            Log::warning('Cannot send SMS: candidate has no phone', [
                'application_id' => $application->id,
                'user_id' => $application->user_id,
            ]);
            return null;
        }

        if (!$userId) {
            Log::warning('Cannot send SMS: application user_id is null', [
                'application_id' => $application->id,
            ]);
            return null;
        }

        $message = $this->buildStatusMessage($application, $newStatus);

        if (!$message) {
            return null;
        }

        return $this->send($phone, $message, 'status_change', $application->id, $userId);
    }

    /**
     * Отправить напоминание о тесте
     */
    public function sendTestReminder(Application $application): ?SmsNotification
    {
        $application->loadMissing(['candidate', 'vacancy']);

        $phone = $application->candidate?->phone;
        $userId = $application->user_id;

        if (!$phone) {
            Log::warning('Cannot send test reminder: candidate has no phone', [
                'application_id' => $application->id,
                'user_id' => $application->user_id,
            ]);
            return null;
        }

        if (!$userId) {
            Log::warning('Cannot send test reminder: application user_id is null', [
                'application_id' => $application->id,
            ]);
            return null;
        }

        $message = "Напоминание: у вас есть незавершённый тест для вакансии \"{$application->vacancy->title}\". Пройдите его в личном кабинете.";

        return $this->send($phone, $message, 'test_reminder', $application->id, $userId);
    }

    /**
     * Отправить приглашение на собеседование
     */
    public function sendInterviewInvite(Application $application, string $datetime, ?string $meetingLink = null): ?SmsNotification
    {
        $application->loadMissing(['candidate', 'vacancy']);

        $phone = $application->candidate?->phone;
        $userId = $application->user_id;

        if (!$phone) {
            Log::warning('Cannot send interview invite: candidate has no phone', [
                'application_id' => $application->id,
                'user_id' => $application->user_id,
            ]);
            return null;
        }

        if (!$userId) {
            Log::warning('Cannot send interview invite: application user_id is null', [
                'application_id' => $application->id,
            ]);
            return null;
        }

        $message = "Приглашаем вас на собеседование по вакансии \"{$application->vacancy->title}\" на {$datetime}.";

        if ($meetingLink) {
            $message .= " Ссылка: {$meetingLink}";
        }

        return $this->send($phone, $message, 'interview_invite', $application->id, $userId);
    }

    /**
     * Основной метод отправки SMS
     */
    public function send(
        string $phone,
        string $message,
        string $type = 'general',
        ?int $applicationId = null,
        ?int $userId = null
    ): ?SmsNotification {
        $phone = $this->normalizePhone($phone);

        if (!$userId) {
            Log::error('SMS create skipped: user_id is null', [
                'application_id' => $applicationId,
                'phone' => $phone,
                'type' => $type,
            ]);
            return null;
        }

        $notification = SmsNotification::create([
            'user_id' => $userId,
            'application_id' => $applicationId,
            'phone' => $phone,
            'message' => $message,
            'type' => $type,
            'status' => SmsNotification::STATUS_PENDING,
        ]);

        try {
            $result = $this->sendViaProvider($phone, $message);

            if (($result['success'] ?? false) === true) {
                $notification->update([
                    'status' => SmsNotification::STATUS_SENT,
                    'sent_at' => now(),
                ]);
            } else {
                $notification->update([
                    'status' => SmsNotification::STATUS_FAILED,
                    'error_message' => $result['error'] ?? 'Unknown error',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('SMS send failed', [
                'application_id' => $applicationId,
                'user_id' => $userId,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            $notification->update([
                'status' => SmsNotification::STATUS_FAILED,
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
        $companyName = config('app.name', 'HR Robot');

        return match ($status) {
            ApplicationStatus::InReview => "Ваша заявка на вакансию \"{$vacancyTitle}\" в {$companyName} принята на рассмотрение.",
            ApplicationStatus::Invited => "Поздравляем! Вы приглашены на следующий этап отбора по вакансии \"{$vacancyTitle}\" в {$companyName}. Проверьте личный кабинет для деталей.",
            ApplicationStatus::Rejected => "К сожалению, ваша заявка на вакансию \"{$vacancyTitle}\" в {$companyName} отклонена. Спасибо за интерес к нашей компании.",
            ApplicationStatus::Hired => "Поздравляем! Вы приняты на должность \"{$vacancyTitle}\" в {$companyName}! Свяжитесь с HR для оформления.",
            default => null,
        };
    }

    /**
     * Нормализует номер телефона
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($phone, '8') && strlen($phone) === 11) {
            $phone = '+7' . substr($phone, 1);
        }

        if (!str_starts_with($phone, '+')) {
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
     * Eskiz.uz provider
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
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * PlayMobile provider
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
                            'message-id' => uniqid('sms_', true),
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
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fallback: логируем SMS
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