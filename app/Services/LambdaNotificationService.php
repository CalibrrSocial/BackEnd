<?php

namespace App\Services;

use Aws\Lambda\LambdaClient;
use Aws\Credentials\Credentials;

class LambdaNotificationService
{
    private LambdaClient $client;
    private string $functionName;
    private bool $debug;

    public function __construct()
    {
        // Prefer Laravel config (reads from .env) with getenv() fallbacks
        $region = config('lambda.region') ?: (getenv('LAMBDA_REGION') ?: (getenv('AWS_DEFAULT_REGION') ?: 'us-east-1'));
        $config = [
            'version' => 'latest',
            'region' => $region,
        ];
        $key = config('lambda.aws_key') ?: (getenv('AWS_ACCESS_KEY_ID') ?: env('AWS_ACCESS_KEY_ID'));
        $secret = config('lambda.aws_secret') ?: (getenv('AWS_SECRET_ACCESS_KEY') ?: env('AWS_SECRET_ACCESS_KEY'));
        $token = config('lambda.aws_token') ?: (getenv('AWS_SESSION_TOKEN') ?: env('AWS_SESSION_TOKEN'));
        if ($key && $secret) {
            $config['credentials'] = new Credentials($key, $secret, $token ?: null);
            \Log::warning('LambdaNotificationService using explicit AWS credentials from env', [
                'region' => $region,
                'access_key_prefix' => substr($key, 0, 4),
                'has_session_token' => !empty($token),
            ]);
        } else {
            \Log::warning('LambdaNotificationService no static AWS keys found; relying on instance profile/default provider chain', [
                'region' => $region,
            ]);
        }
        $this->client = new LambdaClient($config);
        $this->functionName = config('lambda.function') ?: (getenv('LAMBDA_PROFILE_LIKED_FUNCTION') ?: 'emailNotificationFinal');
        $this->debug = filter_var((config('lambda.debug') !== null ? config('lambda.debug') : (getenv('LAMBDA_DEBUG') ?: 'false')), FILTER_VALIDATE_BOOLEAN);
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

    public function notifyUserReported(array $reportData): void
    {
        $payload = json_encode([
            'notificationType' => 'user_reported',
            'reportData' => $reportData,
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
                \Log::info('Lambda notifyUserReported debug', [
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
            \Log::warning('Lambda notifyUserReported failed: '.$e->getMessage());
        }
    }

    public function notifyAttributeLiked(int $recipientUserId, int $senderUserId, array $additionalData = []): void
    {
        // Get recipient and sender user details
        $recipient = \App\Models\User::find($recipientUserId);
        $sender = \App\Models\User::find($senderUserId);
        
        if (!$recipient || !$sender) {
            \Log::warning('Lambda notifyAttributeLiked: User not found', [
                'recipientUserId' => $recipientUserId,
                'senderUserId' => $senderUserId,
            ]);
            return;
        }
        
        // Build the payload with the attribute information
        $payload = json_encode([
            'notificationType' => 'attribute_liked',
            'additionalData' => [
                'recipientEmail' => $recipient->email,
                'senderFirstName' => $sender->firstName,
                'senderLastName' => $sender->lastName,
                'category' => $additionalData['attributeCategory'] ?? '',
                'attribute' => $additionalData['attributeName'] ?? '',
            ],
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
                \Log::info('Lambda notifyAttributeLiked debug', [
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
            \Log::warning('Lambda notifyAttributeLiked failed: '.$e->getMessage());
        }
    }
}


