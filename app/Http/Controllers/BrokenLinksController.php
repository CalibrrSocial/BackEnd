<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\LambdaNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BrokenLinksController extends Controller
{
    protected $lambdaNotificationService;

    public function __construct(LambdaNotificationService $lambdaNotificationService)
    {
        $this->lambdaNotificationService = $lambdaNotificationService;
    }

    /**
     * Report broken social media links
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function reportBrokenLinks(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'notificationType' => 'required|string|in:dead_link_reported',
                'additionalData' => 'required|array',
                'additionalData.recipientEmail' => 'required|email',
                'additionalData.platforms' => 'required|array|min:1',
                'additionalData.platforms.*' => 'string',
                'additionalData.reporterName' => 'required|string'
            ]);

            if ($validator->fails()) {
                Log::warning('Broken links report validation failed', [
                    'errors' => $validator->errors(),
                    'request_data' => $request->all()
                ]);
                
                return response()->json([
                    'error' => 'Validation failed',
                    'details' => $validator->errors()
                ], 400);
            }

            $notificationType = $request->input('notificationType');
            $additionalData = $request->input('additionalData');

            Log::info('Processing broken links report', [
                'notification_type' => $notificationType,
                'recipient_email' => $additionalData['recipientEmail'],
                'platforms' => $additionalData['platforms'],
                'reporter_name' => $additionalData['reporterName']
            ]);

            // Call the Lambda notification service
            $result = $this->lambdaNotificationService->sendNotification(
                $notificationType,
                $additionalData
            );

            if ($result['success']) {
                Log::info('Broken links report email sent successfully', [
                    'recipient_email' => $additionalData['recipientEmail'],
                    'platforms' => $additionalData['platforms'],
                    'lambda_result' => $result
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Broken link report submitted successfully',
                    'platforms' => $additionalData['platforms'],
                    'recipientEmail' => $additionalData['recipientEmail']
                ], 200);
            } else {
                Log::error('Failed to send broken links report email', [
                    'recipient_email' => $additionalData['recipientEmail'],
                    'platforms' => $additionalData['platforms'],
                    'lambda_result' => $result
                ]);

                return response()->json([
                    'error' => 'Failed to send broken links report',
                    'details' => $result['error'] ?? 'Unknown error'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Exception in broken links report', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => 'Failed to process broken links report'
            ], 500);
        }
    }
}
