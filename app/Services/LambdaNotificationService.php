<?php

namespace App\Services;

use Aws\Lambda\LambdaClient;

class LambdaNotificationService
{
    private LambdaClient $client;
    private string $functionName;

    public function __construct()
    {
        $this->client = new LambdaClient([
            'version' => 'latest',
            'region' => env('LAMBDA_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
        ]);
        $this->functionName = env('LAMBDA_PROFILE_LIKED_FUNCTION', 'emailNotificationFinal');
    }

    public function notifyProfileLiked(int $recipientUserId, int $senderUserId, array $additionalData = []): void
    {
        $payload = json_encode([
            'notificationType' => 'profile_liked',
            'recipientUserId' => $recipientUserId,
            'senderUserId' => $senderUserId,
            'additionalData' => (object)$additionalData,
        ]);

        try {
            $this->client->invoke([
                'FunctionName' => $this->functionName,
                'InvocationType' => 'Event', // async
                'Payload' => $payload,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Lambda notifyProfileLiked failed: '.$e->getMessage());
        }
    }
}


