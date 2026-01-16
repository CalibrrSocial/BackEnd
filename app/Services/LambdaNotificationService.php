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
            $debug = filter_var(env('LAMBDA_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
            $params = [
                'FunctionName' => $this->functionName,
                'Payload' => $payload,
            ];
            if ($debug) {
                // Synchronous call with logs for troubleshooting
                $params['InvocationType'] = 'RequestResponse';
                $params['LogType'] = 'Tail';
                $result = $this->client->invoke($params);
                $status = $result['StatusCode'] ?? null;
                $funcErr = $result['FunctionError'] ?? null;
                $log = isset($result['LogResult']) ? base64_decode($result['LogResult']) : null;
                \Log::info('Lambda notifyProfileLiked debug', [
                    'status' => $status,
                    'functionError' => $funcErr,
                    'log' => $log,
                    'function' => $this->functionName,
                ]);
            } else {
                // Async fire-and-forget in production
                $params['InvocationType'] = 'Event';
                $this->client->invoke($params);
            }
        } catch (\Throwable $e) {
            \Log::warning('Lambda notifyProfileLiked failed: '.$e->getMessage());
        }
    }
}


