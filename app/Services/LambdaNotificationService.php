<?php

namespace App\Services;

use Aws\Lambda\LambdaClient;

class LambdaNotificationService
{
    private LambdaClient $client;
    private string $functionName;
    private bool $debug;

    public function __construct()
    {
        // Use getenv() so this works reliably even when config is cached
        $region = getenv('LAMBDA_REGION') ?: (getenv('AWS_DEFAULT_REGION') ?: 'us-east-1');
        $config = [
            'version' => 'latest',
            'region' => $region,
        ];
        $key = getenv('AWS_ACCESS_KEY_ID') ?: null;
        $secret = getenv('AWS_SECRET_ACCESS_KEY') ?: null;
        $token = getenv('AWS_SESSION_TOKEN') ?: null;
        if ($key && $secret) {
            $config['credentials'] = [
                'key' => $key,
                'secret' => $secret,
            ];
            if (!empty($token)) {
                $config['credentials']['token'] = $token;
            }
            \Log::info('LambdaNotificationService using explicit AWS credentials from env', [
                'region' => $region,
                'has_session_token' => !empty($token),
            ]);
        } else {
            \Log::info('LambdaNotificationService relying on instance profile/SDK default credentials', [
                'region' => $region,
            ]);
        }
        $this->client = new LambdaClient($config);
        $this->functionName = getenv('LAMBDA_PROFILE_LIKED_FUNCTION') ?: 'emailNotificationFinal';
        $this->debug = filter_var(getenv('LAMBDA_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);
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
            $params = [
                'FunctionName' => $this->functionName,
                'Payload' => $payload,
            ];
            if ($this->debug) {
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


