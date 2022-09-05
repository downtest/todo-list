<?php

namespace App\Services;

use Framework\Services\Config;
use Framework\Services\Interfaces\Service;
use Framework\Services\Logger;
use Kreait\Firebase\Messaging\CloudMessage;
// Библиотека для запросов в firebase
use Kreait\Firebase\Factory;

/**
 * Сервис по отправке web-пушей через FireBase
 */
class FireBase extends Service
{
    protected static $instance;

    protected ?\Kreait\Firebase\Messaging\MulticastSendReport $lastResult = null;

    /**
     * Отправляем одно сообщение разным токенам
     *
     * @return mixed
     * @throws \Exception
     */
    public function sendMulticast(string $title, string $body, array $tokens, ?string $link = null): int
    {
        $factory = (new Factory)
            ->withServiceAccount('/www/config/firebase.json');

        $cloudMessaging = $factory->createMessaging();

        try {
            $webPushConfig = [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'icon' => Config::get('app.host').'/listodo-logo.svg',
                ],
            ];

            if ($link) {
                $webPushConfig['fcm_options'] = ['link' => $link];
            }

            $FBMessage = (CloudMessage::new())->withWebPushConfig($webPushConfig);

            $result = $cloudMessaging->sendMulticast($FBMessage, $tokens);

            if ($result->hasFailures()) {
                Logger::getInstance()->error("При отправке сообщений в firebase возникли ошибки!", [$result->hasFailures()[0]?->error()]);
            }

            $this->lastResult = $result;

            return count($result->successes());
        } catch (\Throwable $e) {
            throw new \Exception($e);
        }
    }
}
